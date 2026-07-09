<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modelo\StoreModeloRequest;
use App\Http\Requests\Modelo\UpdateModeloRequest;
use App\Http\Resources\ModeloResource;
use App\Models\Modelo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ModeloController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Modelo::query()->with('marca');

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($marcaId = $request->query('marca_id')) {
            $query->where('marca_id', $marcaId);
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return ModeloResource::collection($query->paginate($perPage));
    }

    public function store(StoreModeloRequest $request): JsonResponse
    {
        $modelo = Modelo::create($request->validated());

        return (new ModeloResource($modelo->load('marca')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Modelo $modelo): ModeloResource
    {
        return new ModeloResource($modelo->load('marca'));
    }

    public function update(UpdateModeloRequest $request, Modelo $modelo): ModeloResource
    {
        $modelo->update($request->validated());

        return new ModeloResource($modelo->load('marca'));
    }

    public function destroy(Modelo $modelo): JsonResponse
    {
        $modelo->delete();

        return response()->json(['message' => 'Modelo eliminado correctamente.']);
    }
}
