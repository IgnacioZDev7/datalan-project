<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\StoreProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;
use App\Http\Resources\ProveedorResource;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProveedorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Proveedor::query()->with('direccion');

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('nit', 'like', "%{$buscar}%")
                  ->orWhere('contacto', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'nit', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return ProveedorResource::collection($query->paginate($perPage));
    }

    public function store(StoreProveedorRequest $request): JsonResponse
    {
        $proveedor = Proveedor::create($request->validated());

        return (new ProveedorResource($proveedor->load('direccion')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Proveedor $proveedor): ProveedorResource
    {
        return new ProveedorResource($proveedor->load('direccion'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): ProveedorResource
    {
        $proveedor->update($request->validated());

        return new ProveedorResource($proveedor->load('direccion'));
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->delete();

        return response()->json(['message' => 'Proveedor eliminado correctamente.']);
    }
}
