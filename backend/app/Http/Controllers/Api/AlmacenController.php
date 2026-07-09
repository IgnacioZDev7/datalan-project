<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Almacen\StoreAlmacenRequest;
use App\Http\Requests\Almacen\UpdateAlmacenRequest;
use App\Http\Resources\AlmacenResource;
use App\Models\Almacen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlmacenController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Almacen::query()->with(['responsable', 'direccion']);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($responsableId = $request->query('responsable_id')) {
            $query->where('responsable_id', $responsableId);
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return AlmacenResource::collection($query->paginate($perPage));
    }

    public function store(StoreAlmacenRequest $request): JsonResponse
    {
        $almacen = Almacen::create($request->validated());

        return (new AlmacenResource($almacen->load(['responsable', 'direccion'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Almacen $almacen): AlmacenResource
    {
        return new AlmacenResource($almacen->load(['responsable', 'direccion']));
    }

    public function update(UpdateAlmacenRequest $request, Almacen $almacen): AlmacenResource
    {
        $almacen->update($request->validated());

        return new AlmacenResource($almacen->load(['responsable', 'direccion']));
    }

    public function destroy(Almacen $almacen): JsonResponse
    {
        $almacen->delete();

        return response()->json(['message' => 'Almacén eliminado correctamente.']);
    }
}
