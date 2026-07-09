<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventarioFisico\StoreInventarioFisicoRequest;
use App\Http\Resources\InventarioFisicoResource;
use App\Models\InventarioFisico;
use App\Services\InventarioFisicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class InventarioFisicoController extends Controller
{
    public function __construct(private readonly InventarioFisicoService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = InventarioFisico::query()->withCount('detalles');

        foreach (['almacen_id', 'estado'] as $filtro) {
            if ($valor = $request->query($filtro)) {
                $query->where($filtro, $valor);
            }
        }

        return InventarioFisicoResource::collection(
            $query->orderByDesc('fecha')->paginate((int) $request->query('per_page', 15))
        );
    }

    public function store(StoreInventarioFisicoRequest $request): JsonResponse
    {
        $conteo = $this->service->registrar($request->validated(), $request->user()->id);

        return (new InventarioFisicoResource($conteo->load('detalles.producto')))
            ->response()->setStatusCode(201);
    }

    public function show(InventarioFisico $inventario): InventarioFisicoResource
    {
        return new InventarioFisicoResource($inventario->load('detalles.producto'));
    }

    /**
     * Cierra el conteo y reconcilia: genera un movimiento de ajuste con las diferencias.
     */
    public function cerrar(Request $request, InventarioFisico $inventario): JsonResponse
    {
        $movimiento = $this->service->cerrar($inventario, $request->user()->id);

        return response()->json([
            'message' => $movimiento
                ? 'Conteo cerrado. Se generó un ajuste con las diferencias.'
                : 'Conteo cerrado. No hubo diferencias que ajustar.',
            'ajuste_codigo' => $movimiento?->codigo,
        ]);
    }

    /**
     * Anula el conteo (solo si está en proceso).
     */
    public function destroy(InventarioFisico $inventario): JsonResponse
    {
        if ($inventario->estado === 'cerrado') {
            throw ValidationException::withMessages(['estado' => ['No se puede anular un conteo ya cerrado.']]);
        }

        $inventario->update(['estado' => 'anulado']);

        return response()->json(['message' => 'Conteo anulado.']);
    }
}
