<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Alerta\StoreAlertaRequest;
use App\Http\Requests\Alerta\UpdateAlertaRequest;
use App\Http\Resources\AlertaResource;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Alerta::query()->with(['producto', 'activo']);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('mensaje', 'like', "%{$buscar}%")
                  ->orWhere('tipo', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('leida'))) {
            $query->where('leida', $request->boolean('leida'));
        }

        if ($nivel = $request->query('nivel')) {
            $query->where('nivel', $nivel);
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        $orden = $request->query('orden', 'created_at');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['tipo', 'nivel', 'leida', 'created_at']) ? $orden : 'created_at', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return AlertaResource::collection($query->paginate($perPage));
    }

    public function store(StoreAlertaRequest $request): JsonResponse
    {
        $alerta = Alerta::create($request->validated());

        return (new AlertaResource($alerta->load(['producto', 'activo'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Alerta $alertum): AlertaResource
    {
        return new AlertaResource($alertum->load(['producto', 'activo']));
    }

    public function update(UpdateAlertaRequest $request, Alerta $alertum): AlertaResource
    {
        $alertum->update($request->validated());

        return new AlertaResource($alertum->load(['producto', 'activo']));
    }

    public function destroy(Alerta $alertum): JsonResponse
    {
        $alertum->delete();

        return response()->json(['message' => 'Alerta eliminada correctamente.']);
    }
}
