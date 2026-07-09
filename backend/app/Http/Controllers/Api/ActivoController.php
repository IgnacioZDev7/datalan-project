<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activo\StoreActivoRequest;
use App\Http\Requests\Activo\UpdateActivoRequest;
use App\Http\Resources\ActivoResource;
use App\Models\Activo;
use App\Models\ActivoHistorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivoController extends Controller
{
    private const RELACIONES = ['producto.modelo.marca', 'producto.unidad', 'ubicacion', 'tecnico'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Activo::query()->with(self::RELACIONES);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_interno', 'like', "%{$buscar}%")
                  ->orWhere('nro_serie', 'like', "%{$buscar}%")
                  ->orWhere('mac', 'like', "%{$buscar}%");
            });
        }

        foreach (['estado', 'situacion', 'producto_id', 'tecnico_id', 'ubicacion_id'] as $filtro) {
            if ($valor = $request->query($filtro)) {
                $query->where($filtro, $valor);
            }
        }

        $orden = $request->query('orden', 'codigo_interno');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['codigo_interno', 'estado', 'situacion', 'created_at']) ? $orden : 'codigo_interno', $dir);

        return ActivoResource::collection($query->paginate((int) $request->query('per_page', 15)));
    }

    public function store(StoreActivoRequest $request): JsonResponse
    {
        $activo = Activo::create($request->validated());

        // Historial inicial (nacimiento del activo en el sistema).
        $this->registrarHistorial($activo, null, $activo->estado, null, $activo->situacion,
            'Alta del activo', $request->user()?->id);

        return (new ActivoResource($activo->load(self::RELACIONES)))->response()->setStatusCode(201);
    }

    public function show(Activo $activo): ActivoResource
    {
        return new ActivoResource($activo->load(self::RELACIONES));
    }

    public function update(UpdateActivoRequest $request, Activo $activo): ActivoResource
    {
        $estadoAnterior = $activo->estado;
        $situacionAnterior = $activo->situacion;

        $activo->update($request->validated());

        // Si cambió estado o situacion, se registra en el historial (trazabilidad).
        if ($activo->estado !== $estadoAnterior || $activo->situacion !== $situacionAnterior) {
            $this->registrarHistorial(
                $activo,
                $estadoAnterior, $activo->estado,
                $situacionAnterior, $activo->situacion,
                $request->input('motivo'),
                $request->user()?->id
            );
        }

        return new ActivoResource($activo->load(self::RELACIONES));
    }

    public function destroy(Activo $activo): JsonResponse
    {
        $activo->delete();

        return response()->json(['message' => 'Activo eliminado correctamente.']);
    }

    private function registrarHistorial(Activo $activo, ?string $estadoAnt, ?string $estadoNuevo,
        ?string $situacionAnt, ?string $situacionNueva, ?string $motivo, ?int $usuarioId): void
    {
        ActivoHistorial::create([
            'activo_id' => $activo->id,
            'estado_anterior' => $estadoAnt,
            'estado_nuevo' => $estadoNuevo,
            'situacion_anterior' => $situacionAnt,
            'situacion_nueva' => $situacionNueva,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId,
        ]);
    }
}
