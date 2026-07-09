<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ubicacion\StoreUbicacionRequest;
use App\Http\Requests\Ubicacion\UpdateUbicacionRequest;
use App\Http\Resources\UbicacionResource;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UbicacionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Ubicacion::query()->with('almacen');

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($almacenId = $request->query('almacen_id')) {
            $query->where('almacen_id', $almacenId);
        }

        $orden = $request->query('orden', 'codigo');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['codigo', 'descripcion', 'created_at']) ? $orden : 'codigo', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return UbicacionResource::collection($query->paginate($perPage));
    }

    public function store(StoreUbicacionRequest $request): JsonResponse
    {
        $ubicacion = Ubicacion::create($request->validated());

        return (new UbicacionResource($ubicacion->load('almacen')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ubicacion $ubicacion): UbicacionResource
    {
        return new UbicacionResource($ubicacion->load('almacen'));
    }

    public function update(UpdateUbicacionRequest $request, Ubicacion $ubicacion): UbicacionResource
    {
        $ubicacion->update($request->validated());

        return new UbicacionResource($ubicacion->load('almacen'));
    }

    public function destroy(Ubicacion $ubicacion): JsonResponse
    {
        $ubicacion->delete();

        return response()->json(['message' => 'Ubicación eliminada correctamente.']);
    }
}
