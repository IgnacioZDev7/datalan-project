<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Movimiento\StoreMovimientoRequest;
use App\Http\Resources\MovimientoResource;
use App\Models\Movimiento;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovimientoController extends Controller
{
    public function __construct(private readonly MovimientoService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Movimiento::query()->with('detalles');

        if ($buscar = $request->query('buscar')) {
            $query->where('codigo', 'like', "%{$buscar}%");
        }
        foreach (['tipo', 'proyecto_id', 'proveedor_id', 'almacen_origen_id', 'almacen_destino_id'] as $filtro) {
            if ($valor = $request->query($filtro)) {
                $query->where($filtro, $valor);
            }
        }
        if ($desde = $request->query('desde')) {
            $query->whereDate('fecha', '>=', $desde);
        }
        if ($hasta = $request->query('hasta')) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $query->orderBy('fecha', $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc');

        return MovimientoResource::collection($query->paginate((int) $request->query('per_page', 15)));
    }

    public function store(StoreMovimientoRequest $request): JsonResponse
    {
        $movimiento = $this->service->registrar($request->validated(), $request->user()->id);

        return (new MovimientoResource($movimiento->load('detalles.producto')))
            ->response()->setStatusCode(201);
    }

    public function show(Movimiento $movimiento): MovimientoResource
    {
        return new MovimientoResource(
            $movimiento->load(['detalles.producto', 'detalles.activo', 'detalles.carrete'])
        );
    }

    /**
     * Anula el movimiento: revierte sus efectos en el inventario y lo marca como
     * eliminado (recuperable). No borra la historia.
     */
    public function destroy(Movimiento $movimiento): JsonResponse
    {
        $this->service->anular($movimiento);

        return response()->json(['message' => 'Movimiento anulado; sus efectos en el inventario fueron revertidos.']);
    }
}
