<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Direccion\StoreDireccionRequest;
use App\Http\Requests\Direccion\UpdateDireccionRequest;
use App\Http\Resources\DireccionResource;
use App\Models\Direccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DireccionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Direccion::query();

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('ciudad', 'like', "%{$buscar}%")
                  ->orWhere('zona', 'like', "%{$buscar}%")
                  ->orWhere('calle', 'like', "%{$buscar}%");
            });
        }

        $orden = $request->query('orden', 'ciudad');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['ciudad', 'zona', 'calle', 'created_at']) ? $orden : 'ciudad', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return DireccionResource::collection($query->paginate($perPage));
    }

    public function store(StoreDireccionRequest $request): JsonResponse
    {
        $direccion = Direccion::create($request->validated());

        return (new DireccionResource($direccion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Direccion $direccion): DireccionResource
    {
        return new DireccionResource($direccion);
    }

    public function update(UpdateDireccionRequest $request, Direccion $direccion): DireccionResource
    {
        $direccion->update($request->validated());

        return new DireccionResource($direccion);
    }

    public function destroy(Direccion $direccion): JsonResponse
    {
        $direccion->delete();

        return response()->json(['message' => 'Dirección eliminada correctamente.']);
    }
}
