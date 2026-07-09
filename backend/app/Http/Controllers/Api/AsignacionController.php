<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asignacion\StoreAsignacionRequest;
use App\Http\Requests\Asignacion\UpdateAsignacionRequest;
use App\Http\Resources\AsignacionResource;
use App\Models\Asignacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AsignacionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Asignacion::query()->with(['activo', 'tecnico', 'proyecto']);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('observaciones', 'like', "%{$buscar}%");
            });
        }

        if ($activoId = $request->query('activo_id')) {
            $query->where('activo_id', $activoId);
        }

        if ($tecnicoId = $request->query('tecnico_id')) {
            $query->where('tecnico_id', $tecnicoId);
        }

        if ($proyectoId = $request->query('proyecto_id')) {
            $query->where('proyecto_id', $proyectoId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        $orden = $request->query('orden', 'fecha_asignacion');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['fecha_asignacion', 'fecha_devolucion', 'estado', 'created_at']) ? $orden : 'fecha_asignacion', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return AsignacionResource::collection($query->paginate($perPage));
    }

    public function store(StoreAsignacionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['registrado_por'] = auth()->id();

        $asignacion = Asignacion::create($data);

        return (new AsignacionResource($asignacion->load(['activo', 'tecnico', 'proyecto'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Asignacion $asignacione): AsignacionResource
    {
        return new AsignacionResource($asignacione->load(['activo', 'tecnico', 'proyecto']));
    }

    public function update(UpdateAsignacionRequest $request, Asignacion $asignacione): AsignacionResource
    {
        $asignacione->update($request->validated());

        return new AsignacionResource($asignacione->load(['activo', 'tecnico', 'proyecto']));
    }

    public function destroy(Asignacion $asignacione): JsonResponse
    {
        $asignacione->delete();

        return response()->json(['message' => 'Asignación eliminada correctamente.']);
    }
}
