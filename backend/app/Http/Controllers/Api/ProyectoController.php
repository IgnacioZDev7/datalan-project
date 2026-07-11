<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proyecto\StoreProyectoRequest;
use App\Http\Requests\Proyecto\UpdateProyectoRequest;
use App\Http\Resources\ProyectoResource;
use App\Models\Direccion;
use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Proyecto::query()->with(['empresa', 'tecnico', 'direccion']);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        if ($empresaId = $request->query('empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if ($tecnicoId = $request->query('tecnico_id')) {
            $query->where('tecnico_id', $tecnicoId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'codigo', 'estado', 'fecha_inicio', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return ProyectoResource::collection($query->paginate($perPage));
    }

    public function store(StoreProyectoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                $data['direccion_id'] = Direccion::create($dir)->id;
            }

            $proyecto = Proyecto::create($data);

            return (new ProyectoResource($proyecto->load(['empresa', 'tecnico', 'direccion'])))
                ->response()
                ->setStatusCode(201);
        });
    }

    public function show(Proyecto $proyecto): ProyectoResource
    {
        return new ProyectoResource($proyecto->load(['empresa', 'tecnico', 'direccion']));
    }

    public function update(UpdateProyectoRequest $request, Proyecto $proyecto): ProyectoResource
    {
        return DB::transaction(function () use ($request, $proyecto) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                if ($proyecto->direccion_id) {
                    $proyecto->direccion->update($dir);
                } else {
                    $data['direccion_id'] = Direccion::create($dir)->id;
                }
            }

            $proyecto->update($data);

            return new ProyectoResource($proyecto->load(['empresa', 'tecnico', 'direccion']));
        });
    }

    public function destroy(Proyecto $proyecto): JsonResponse
    {
        $proyecto->delete();

        return response()->json(['message' => 'Proyecto eliminado correctamente.']);
    }
}
