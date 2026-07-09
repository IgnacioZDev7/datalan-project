<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnidadMedida\StoreUnidadMedidaRequest;
use App\Http\Requests\UnidadMedida\UpdateUnidadMedidaRequest;
use App\Http\Resources\UnidadMedidaResource;
use App\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnidadMedidaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = UnidadMedida::query();

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('abreviatura', 'like', "%{$buscar}%");
            });
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'abreviatura', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return UnidadMedidaResource::collection($query->paginate($perPage));
    }

    public function store(StoreUnidadMedidaRequest $request): JsonResponse
    {
        $unidad = UnidadMedida::create($request->validated());

        return (new UnidadMedidaResource($unidad))
            ->response()
            ->setStatusCode(201);
    }

    public function show(UnidadMedida $unidadMedidum): UnidadMedidaResource
    {
        return new UnidadMedidaResource($unidadMedidum);
    }

    public function update(UpdateUnidadMedidaRequest $request, UnidadMedida $unidadMedidum): UnidadMedidaResource
    {
        $unidadMedidum->update($request->validated());

        return new UnidadMedidaResource($unidadMedidum);
    }

    public function destroy(UnidadMedida $unidadMedidum): JsonResponse
    {
        $unidadMedidum->delete();

        return response()->json(['message' => 'Unidad de medida eliminada correctamente.']);
    }
}
