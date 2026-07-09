<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tecnico\StoreTecnicoRequest;
use App\Http\Requests\Tecnico\UpdateTecnicoRequest;
use App\Http\Resources\TecnicoResource;
use App\Models\Tecnico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TecnicoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tecnico::query()->with('usuario');

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%")
                  ->orWhere('cargo', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'ci', 'cargo', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return TecnicoResource::collection($query->paginate($perPage));
    }

    public function store(StoreTecnicoRequest $request): JsonResponse
    {
        $tecnico = Tecnico::create($request->validated());

        return (new TecnicoResource($tecnico->load('usuario')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Tecnico $tecnico): TecnicoResource
    {
        return new TecnicoResource($tecnico->load('usuario'));
    }

    public function update(UpdateTecnicoRequest $request, Tecnico $tecnico): TecnicoResource
    {
        $tecnico->update($request->validated());

        return new TecnicoResource($tecnico->load('usuario'));
    }

    public function destroy(Tecnico $tecnico): JsonResponse
    {
        $tecnico->delete();

        return response()->json(['message' => 'Técnico eliminado correctamente.']);
    }
}
