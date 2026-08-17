<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Alerta;
use App\Models\Carrete;
use App\Models\Existencia;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function estadisticas(): JsonResponse
    {
        // --- KPIs ---
        $kpis = [
            'productos'               => Producto::count(),
            'activos'                 => Activo::count(),
            'existencias_bajo_minimo' => Existencia::whereColumn('cantidad_actual', '<=', 'cantidad_minima')
                ->where('cantidad_minima', '>', 0)
                ->count(),
            'alertas'                 => Alerta::where('leida', false)->count(),
            'movimientos_mes'         => Movimiento::whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count(),
            'carretes'                => Carrete::count(),
        ];

        // --- Movimientos por mes (últimos 6 meses) — portable, agrupado en PHP ---
        $desde = now()->subMonths(5)->startOfMonth();
        $movs = Movimiento::where('fecha', '>=', $desde)->get(['fecha', 'tipo']);
        $movimientos_por_mes = $movs
            ->groupBy(fn ($m) => substr((string) $m->fecha, 0, 7))
            ->map(fn ($grupo, $mes) => [
                'mes'      => $mes,
                'entradas' => $grupo->whereIn('tipo', ['entrada', 'devolucion'])->count(),
                'salidas'  => $grupo->whereIn('tipo', ['salida', 'baja'])->count(),
            ])
            ->sortKeys()
            ->values();

        // --- Stock por categoría ---
        // SUM() vuelve como string en MySQL y PostgreSQL: casteamos a número.
        $stock_por_categoria = Existencia::join('productos', 'existencias.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select('categorias.nombre as categoria', DB::raw('SUM(existencias.cantidad_actual) as total'))
            ->groupBy('categorias.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'categoria' => $row->categoria,
                'total'     => (float) $row->total,
            ])
            ->values();

        // --- Activos por situación ---
        $activos_por_situacion = Activo::select('situacion', DB::raw('COUNT(*) as total'))
            ->groupBy('situacion')
            ->get()
            ->map(fn ($row) => [
                'situacion' => $row->situacion,
                'total'     => (int) $row->total,
            ])
            ->values();

        return response()->json([
            'kpis'                    => $kpis,
            'movimientos_por_mes'     => $movimientos_por_mes,
            'stock_por_categoria'     => $stock_por_categoria,
            'activos_por_situacion'   => $activos_por_situacion,
        ]);
    }
}
