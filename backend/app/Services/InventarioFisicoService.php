<?php

namespace App\Services;

use App\Models\Carrete;
use App\Models\Existencia;
use App\Models\InventarioFisico;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Conteo físico y reconciliación. Al cerrar un conteo, las diferencias
 * (físico vs sistema) de los consumibles se corrigen generando UN movimiento
 * de tipo 'ajuste' a través del MovimientoService (fuente única de verdad).
 */
class InventarioFisicoService
{
    public function __construct(private readonly MovimientoService $movimientos)
    {
    }

    /**
     * Crea el conteo con sus detalles, tomando una foto de la cantidad en sistema.
     */
    public function registrar(array $datos, int $usuarioId): InventarioFisico
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $detalles = $datos['detalles'];
            unset($datos['detalles']);

            $datos['responsable_id'] = $usuarioId;
            $datos['estado'] = 'en_proceso';
            $conteo = InventarioFisico::create($datos);

            foreach ($detalles as $linea) {
                $linea['cantidad_sistema'] = $this->cantidadSistema($linea, $conteo->almacen_id);
                $conteo->detalles()->create($linea);
            }

            return $conteo->load('detalles');
        });
    }

    /**
     * Cierra el conteo: genera un ajuste con las diferencias de consumibles.
     * Devuelve el movimiento de ajuste creado (o null si no hubo diferencias).
     */
    public function cerrar(InventarioFisico $conteo, int $usuarioId): ?Movimiento
    {
        if ($conteo->estado !== 'en_proceso') {
            throw ValidationException::withMessages(['estado' => ['El conteo ya está cerrado o anulado.']]);
        }

        return DB::transaction(function () use ($conteo, $usuarioId) {
            $ajusteDetalles = [];

            foreach ($conteo->detalles as $d) {
                // Solo se auto-ajustan consumibles (sin activo ni carrete).
                if ($d->activo_id || $d->carrete_id) {
                    continue;
                }
                $delta = (float) $d->cantidad_fisica - (float) $d->cantidad_sistema;
                if ($delta != 0.0) {
                    $ajusteDetalles[] = ['producto_id' => $d->producto_id, 'cantidad' => $delta];
                }
            }

            $movimiento = null;
            if (! empty($ajusteDetalles)) {
                $movimiento = $this->movimientos->registrar([
                    'codigo' => 'AJU-'.$conteo->codigo,
                    'tipo' => 'ajuste',
                    'fecha' => now(),
                    'almacen_destino_id' => $conteo->almacen_id,
                    'inventario_fisico_id' => $conteo->id,
                    'observaciones' => "Ajuste por conteo físico {$conteo->codigo}",
                    'detalles' => $ajusteDetalles,
                ], $usuarioId);
            }

            $conteo->update(['estado' => 'cerrado']);

            return $movimiento;
        });
    }

    private function cantidadSistema(array $linea, int $almacenId): float
    {
        if (! empty($linea['carrete_id'])) {
            return (float) (Carrete::find($linea['carrete_id'])?->metraje_disponible ?? 0);
        }
        if (! empty($linea['activo_id'])) {
            return 1.0; // presencia del activo
        }
        $ex = Existencia::where('producto_id', $linea['producto_id'])
            ->where('almacen_id', $almacenId)->first();

        return (float) ($ex?->cantidad_actual ?? 0);
    }
}
