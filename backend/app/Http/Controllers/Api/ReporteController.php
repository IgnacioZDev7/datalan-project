<?php

namespace App\Http\Controllers\Api;

use App\Exports\ExistenciasExport;
use App\Exports\MovimientosExport;
use App\Http\Controllers\Controller;
use App\Models\Existencia;
use App\Models\Movimiento;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    // ---- EXISTENCIAS ----

    public function existenciasExcel(Request $request)
    {
        return Excel::download(
            new ExistenciasExport($request->query('almacen_id'), $request->boolean('bajo_minimo')),
            'existencias.xlsx'
        );
    }

    public function existenciasPdf(Request $request)
    {
        $existencias = Existencia::with(['producto', 'almacen'])
            ->when($request->query('almacen_id'), fn($q, $v) => $q->where('almacen_id', $v))
            ->when($request->boolean('bajo_minimo'), fn($q) => $q->whereColumn('cantidad_actual', '<=', 'cantidad_minima'))
            ->orderBy('almacen_id')->orderBy('producto_id')
            ->get();
        $pdf = Pdf::loadView('reportes.existencias-pdf', compact('existencias'));
        return $pdf->download('existencias.pdf');
    }

    // ---- MOVIMIENTOS ----

    public function movimientosExcel(Request $request)
    {
        return Excel::download(
            new MovimientosExport($request->query('desde'), $request->query('hasta'), $request->query('tipo')),
            'movimientos.xlsx'
        );
    }

    public function movimientosPdf(Request $request)
    {
        $movimientos = Movimiento::query()
            ->when($request->query('tipo'), fn($q, $v) => $q->where('tipo', $v))
            ->when($request->query('desde'), fn($q, $v) => $q->whereDate('fecha', '>=', $v))
            ->when($request->query('hasta'), fn($q, $v) => $q->whereDate('fecha', '<=', $v))
            ->orderByDesc('fecha')
            ->get();
        $pdf = Pdf::loadView('reportes.movimientos-pdf', compact('movimientos'));
        return $pdf->download('movimientos.pdf');
    }

    // ---- KARDEX ----

    public function kardexPdf(Request $request, Producto $producto)
    {
        $almacenId = $request->query('almacen_id');
        $detalles = \App\Models\MovimientoDetalle::with('movimiento')
            ->where('producto_id', $producto->id)
            ->whereHas('movimiento', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->sortBy(fn($d) => [optional($d->movimiento)->fecha, $d->id])
            ->values();

        $saldo = 0.0;
        $lineas = [];
        foreach ($detalles as $d) {
            $mov = $d->movimiento;
            $efecto = $this->efecto($mov, (float) $d->cantidad, $almacenId);
            if (is_null($efecto)) continue;
            $saldo += $efecto;
            $lineas[] = [
                'fecha' => $mov->fecha,
                'movimiento' => $mov->codigo,
                'tipo' => $mov->tipo,
                'entrada' => $efecto > 0 ? $efecto : null,
                'salida' => $efecto < 0 ? abs($efecto) : null,
                'saldo' => round($saldo, 2),
            ];
        }

        $movimientos = $lineas;
        $saldoFinal = round($saldo, 2);
        $productoArr = ['id' => $producto->id, 'codigo' => $producto->codigo, 'nombre' => $producto->nombre];

        $pdf = Pdf::loadView('reportes.kardex-pdf', [
            'producto' => $productoArr,
            'movimientos' => $movimientos,
            'saldoFinal' => $saldoFinal,
        ]);
        return $pdf->download("kardex-{$producto->codigo}.pdf");
    }

    private function efecto($mov, float $cantidad, $almacenId): ?float
    {
        return match ($mov->tipo) {
            'entrada', 'devolucion' => $this->aplica($mov->almacen_destino_id, $almacenId) ? +$cantidad : null,
            'salida', 'baja' => $this->aplica($mov->almacen_origen_id, $almacenId) ? -$cantidad : null,
            'ajuste' => $this->aplica($mov->almacen_destino_id, $almacenId) ? +$cantidad : null,
            'traslado' => match (true) {
                (int) $mov->almacen_destino_id === (int) $almacenId => +$cantidad,
                (int) $mov->almacen_origen_id === (int) $almacenId => -$cantidad,
                is_null($almacenId) => 0.0,
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
