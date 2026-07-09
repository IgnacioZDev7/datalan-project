<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carrete\StoreCarreteRequest;
use App\Http\Requests\Carrete\UpdateCarreteRequest;
use App\Http\Resources\CarreteResource;
use App\Models\Carrete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarreteController extends Controller
{
    private const RELACIONES = ['producto.modelo.marca', 'producto.unidad', 'ubicacion'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Carrete::query()->with(self::RELACIONES);

        if ($buscar = $request->query('buscar')) {
            $query->where('codigo', 'like', "%{$buscar}%");
        }

        foreach (['estado', 'tipo_fibra', 'producto_id', 'ubicacion_id'] as $filtro) {
            if ($valor = $request->query($filtro)) {
                $query->where($filtro, $valor);
            }
        }

        $orden = $request->query('orden', 'codigo');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['codigo', 'estado', 'metraje_disponible', 'created_at']) ? $orden : 'codigo', $dir);

        return CarreteResource::collection($query->paginate((int) $request->query('per_page', 15)));
    }

    public function store(StoreCarreteRequest $request): JsonResponse
    {
        $carrete = Carrete::create($request->validated());

        return (new CarreteResource($carrete->load(self::RELACIONES)))->response()->setStatusCode(201);
    }

    public function show(Carrete $carrete): CarreteResource
    {
        return new CarreteResource($carrete->load(self::RELACIONES));
    }

    public function update(UpdateCarreteRequest $request, Carrete $carrete): CarreteResource
    {
        $carrete->update($request->validated());

        return new CarreteResource($carrete->load(self::RELACIONES));
    }

    public function destroy(Carrete $carrete): JsonResponse
    {
        $carrete->delete();

        return response()->json(['message' => 'Carrete eliminado correctamente.']);
    }
}
