<?php

namespace App\Services;

use App\Models\Activo;
use App\Models\Carrete;
use App\Models\Existencia;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Motor del inventario. Es la ÚNICA vía por la que cambian existencias / metraje /
 * situación de activos. Regla de oro: stock = entradas - salidas + devoluciones.
 * Todo ocurre dentro de una transacción; si algo falla, no se aplica nada.
 */
class MovimientoService
{
    /**
     * Registra un movimiento con sus detalles y aplica sus efectos en el inventario.
     */
    public function registrar(array $datos, int $usuarioId): Movimiento
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $detalles = $datos['detalles'];
            unset($datos['detalles']);

            $datos['registrado_por'] = $usuarioId;
            $movimiento = Movimiento::create($datos);

            foreach ($detalles as $linea) {
                $detalle = $movimiento->detalles()->create($linea);
                $this->aplicarEfecto($movimiento, $detalle, 1);
            }

            return $movimiento->load('detalles');
        });
    }

    /**
     * Anula un movimiento: revierte sus efectos y lo marca como eliminado (recuperable).
     */
    public function anular(Movimiento $movimiento): void
    {
        DB::transaction(function () use ($movimiento) {
            foreach ($movimiento->detalles as $detalle) {
                $this->aplicarEfecto($movimiento, $detalle, -1); // signo invertido = revertir
            }
            $movimiento->delete(); // soft delete
        });
    }

    /**
     * Aplica (o revierte, si $factor = -1) el efecto de una línea según el tipo de
     * producto y el tipo de movimiento.
     */
    private function aplicarEfecto(Movimiento $mov, MovimientoDetalle $detalle, int $factor): void
    {
        $producto = Producto::with('categoria')->findOrFail($detalle->producto_id);

        match ($producto->categoria->tipo_inventario) {
            'consumible' => $this->efectoConsumible($mov, $detalle, $factor),
            'cable' => $this->efectoCarrete($mov, $detalle, $factor),
            'activo', 'herramienta' => $this->efectoActivo($mov, $detalle, $factor),
            default => null,
        };
    }

    // ---- CONSUMIBLES: existencias por (producto, almacen) ----
    private function efectoConsumible(Movimiento $mov, MovimientoDetalle $d, int $factor): void
    {
        $cantidad = (float) $d->cantidad * $factor;

        match ($mov->tipo) {
            'entrada', 'devolucion' => $this->ajustarExistencia($d->producto_id, $mov->almacen_destino_id, +$cantidad),
            'salida', 'baja' => $this->ajustarExistencia($d->producto_id, $mov->almacen_origen_id, -$cantidad, true),
            'traslado' => tap(null, function () use ($d, $mov, $cantidad) {
                $this->ajustarExistencia($d->producto_id, $mov->almacen_origen_id, -$cantidad, true);
                $this->ajustarExistencia($d->producto_id, $mov->almacen_destino_id, +$cantidad);
            }),
            // ajuste: la cantidad es un delta con signo (viene de una reconciliación).
            'ajuste' => $this->ajustarExistencia($d->producto_id, $mov->almacen_destino_id ?? $mov->almacen_origen_id, +$cantidad),
            default => null,
        };
    }

    private function ajustarExistencia(int $productoId, ?int $almacenId, float $delta, bool $validarStock = false): void
    {
        if (! $almacenId) {
            throw ValidationException::withMessages(['almacen' => ['El movimiento requiere un almacén para el consumible.']]);
        }

        $existencia = Existencia::firstOrCreate(
            ['producto_id' => $productoId, 'almacen_id' => $almacenId],
            ['cantidad_actual' => 0]
        );

        $nuevo = (float) $existencia->cantidad_actual + $delta;

        if ($validarStock && $nuevo < 0) {
            throw ValidationException::withMessages([
                'detalles' => ["Stock insuficiente del producto #{$productoId} en el almacén #{$almacenId} (disponible: {$existencia->cantidad_actual})."],
            ]);
        }

        $existencia->update(['cantidad_actual' => $nuevo]);
    }

    // ---- CABLE: metraje del carrete ----
    private function efectoCarrete(Movimiento $mov, MovimientoDetalle $d, int $factor): void
    {
        if (! $d->carrete_id) {
            throw ValidationException::withMessages(['detalles' => ['El detalle de cable requiere carrete_id.']]);
        }

        $carrete = Carrete::findOrFail($d->carrete_id);
        $metros = (float) $d->metraje * $factor;

        $consume = in_array($mov->tipo, ['salida', 'baja']);
        $nuevo = (float) $carrete->metraje_disponible + ($consume ? -$metros : +$metros);

        if ($nuevo < 0) {
            throw ValidationException::withMessages([
                'detalles' => ["Metraje insuficiente en el carrete {$carrete->codigo} (disponible: {$carrete->metraje_disponible} m)."],
            ]);
        }

        $carrete->update(['metraje_disponible' => $nuevo]);
    }

    // ---- ACTIVOS / HERRAMIENTAS: situación de la unidad ----
    private function efectoActivo(Movimiento $mov, MovimientoDetalle $d, int $factor): void
    {
        if (! $d->activo_id) {
            throw ValidationException::withMessages(['detalles' => ['El detalle de activo requiere activo_id.']]);
        }

        $activo = Activo::findOrFail($d->activo_id);

        // Al revertir (factor -1) se devuelve al almacén.
        if ($factor < 0) {
            $activo->update(['situacion' => 'en_almacen']);

            return;
        }

        match ($mov->tipo) {
            'salida' => $activo->update(['situacion' => $mov->proyecto_id ? 'instalado' : 'asignado']),
            'entrada', 'devolucion' => $activo->update(['situacion' => 'en_almacen']),
            'baja' => $activo->update(['situacion' => 'de_baja', 'estado' => 'baja']),
            'traslado' => null, // solo cambia de almacén vía ubicacion, no la situación
            default => null,
        };
    }
}
