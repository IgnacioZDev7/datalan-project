<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Kardex: historial de movimientos de un producto con saldo corrido.
 * Es una CONSULTA sobre los movimientos (no una tabla). Solo lectura.
 */
class KardexController extends Controller
{
    public function producto(Request $request, Producto $producto): JsonResponse
    {
        $almacenId = $request->query('almacen_id');

        $detalles = MovimientoDetalle::with('movimiento')
            ->where('producto_id', $producto->id)
            ->whereHas('movimiento', function ($q) use ($request) {
                $q->whereNull('deleted_at'); // excluye movimientos anulados
                if ($desde = $request->query('desde')) {
                    $q->whereDate('fecha', '>=', $desde);
                }
                if ($hasta = $request->query('hasta')) {
                    $q->whereDate('fecha', '<=', $hasta);
                }
            })
            ->get()
            ->sortBy(fn ($d) => [optional($d->movimiento)->fecha, $d->id])
            ->values();

        $saldo = 0.0;
        $lineas = [];

        foreach ($detalles as $d) {
            $mov = $d->movimiento;
            $efecto = $this->efecto($mov, (float) $d->cantidad, $almacenId);
            if (is_null($efecto)) {
                continue; // no afecta al almacén filtrado
            }

            $saldo += $efecto;
            $lineas[] = [
                'fecha' => $mov->fecha,
                'movimiento' => $mov->codigo,
                'tipo' => $mov->tipo,
                'documento' => $mov->documento_referencia,
                'entrada' => $efecto > 0 ? $efecto : null,
                'salida' => $efecto < 0 ? abs($efecto) : null,
                'saldo' => round($saldo, 2),
            ];
        }

        return response()->json([
            'producto' => ['id' => $producto->id, 'codigo' => $producto->codigo, 'nombre' => $producto->nombre],
            'almacen_id' => $almacenId ? (int) $almacenId : null,
            'saldo_final' => round($saldo, 2),
            'movimientos' => $lineas,
        ]);
    }

    /**
     * Efecto (con signo) de una línea sobre el saldo. Null si no aplica al almacén.
     */
    private function efecto($mov, float $cantidad, $almacenId): ?float
    {
        return match ($mov->tipo) {
            'entrada', 'devolucion' => $this->aplica($mov->almacen_destino_id, $almacenId) ? +$cantidad : null,
            'salida', 'baja' => $this->aplica($mov->almacen_origen_id, $almacenId) ? -$cantidad : null,
            'ajuste' => $this->aplica($mov->almacen_destino_id, $almacenId) ? +$cantidad : null, // cantidad = delta con signo
            'traslado' => match (true) {
                (int) $mov->almacen_destino_id === (int) $almacenId => +$cantidad,
                (int) $mov->almacen_origen_id === (int) $almacenId => -$cantidad,
                is_null($almacenId) => 0.0, // global: el traslado no cambia el total
                default => null,
            },
            default => null,
        };
    }

    private function aplica(?int $almacenMovimiento, $almacenFiltro): bool
    {
        return is_null($almacenFiltro) || (int) $almacenMovimiento === (int) $almacenFiltro;
    }
}
