<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marca\StoreMarcaRequest;
use App\Http\Requests\Marca\UpdateMarcaRequest;
use App\Http\Resources\MarcaResource;
use App\Models\Marca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarcaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Marca::query();

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return MarcaResource::collection($query->paginate($perPage));
    }

    public function store(StoreMarcaRequest $request): JsonResponse
    {
        $marca = Marca::create($request->validated());

        return (new MarcaResource($marca))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Marca $marca): MarcaResource
    {
        return new MarcaResource($marca);
    }

    public function update(UpdateMarcaRequest $request, Marca $marca): MarcaResource
    {
        $marca->update($request->validated());

        return new MarcaResource($marca);
    }

    public function destroy(Marca $marca): JsonResponse
    {
        $marca->delete();

        return response()->json(['message' => 'Marca eliminada correctamente.']);
    }
}
