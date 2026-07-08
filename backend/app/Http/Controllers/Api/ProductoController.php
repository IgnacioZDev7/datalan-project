<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Listado paginado con filtros estándar.
     * Filtros: buscar, activo, categoria_id, per_page, orden, dir.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Producto::query()->with(['categoria', 'modelo.marca', 'unidad']);

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($categoriaId = $request->query('categoria_id')) {
            $query->where('categoria_id', $categoriaId);
        }

        $orden = $request->query('orden', 'nombre');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['nombre', 'codigo', 'created_at']) ? $orden : 'nombre', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return ProductoResource::collection($query->paginate($perPage));
    }

    public function store(StoreProductoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create($data);

        return (new ProductoResource($producto->load(['categoria', 'modelo.marca', 'unidad'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Producto $producto): ProductoResource
    {
        return new ProductoResource($producto->load(['categoria', 'modelo.marca', 'unidad']));
    }

    public function update(UpdateProductoRequest $request, Producto $producto): ProductoResource
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            // Reemplaza la imagen anterior si existía.
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return new ProductoResource($producto->load(['categoria', 'modelo.marca', 'unidad']));
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete(); // soft delete

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }
}
