<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Almacen\StoreAlmacenRequest;
use App\Http\Requests\Almacen\UpdateAlmacenRequest;
use App\Http\Resources\AlmacenResource;
use App\Models\Almacen;
use App\Models\Direccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                $data['direccion_id'] = Direccion::create($dir)->id;
            }

            $almacen = Almacen::create($data);

            return (new AlmacenResource($almacen->load(['responsable', 'direccion'])))
                ->response()
                ->setStatusCode(201);
        });
    }

    public function show(Almacen $almacen): AlmacenResource
    {
        return new AlmacenResource($almacen->load(['responsable', 'direccion']));
    }

    public function update(UpdateAlmacenRequest $request, Almacen $almacen): AlmacenResource
    {
        return DB::transaction(function () use ($request, $almacen) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                if ($almacen->direccion_id) {
                    $almacen->direccion->update($dir);
                } else {
                    $data['direccion_id'] = Direccion::create($dir)->id;
                }
            }

            $almacen->update($data);

            return new AlmacenResource($almacen->load(['responsable', 'direccion']));
        });
    }

    public function destroy(Almacen $almacen): JsonResponse
    {
        $almacen->delete();

        return response()->json(['message' => 'Almacén eliminado correctamente.']);
    }
}
