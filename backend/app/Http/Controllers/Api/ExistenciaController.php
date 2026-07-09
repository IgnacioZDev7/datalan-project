<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExistenciaResource;
use App\Models\Existencia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExistenciaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Existencia::query()->with(['producto', 'almacen']);

        if ($productoId = $request->query('producto_id')) {
            $query->where('producto_id', $productoId);
        }

        if ($almacenId = $request->query('almacen_id')) {
            $query->where('almacen_id', $almacenId);
        }

        if ($request->boolean('bajo_minimo')) {
            $query->whereColumn('cantidad_actual', '<=', 'cantidad_minima');
        }

        $orden = $request->query('orden', 'producto_id');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($orden, ['producto_id', 'almacen_id', 'cantidad_actual', 'created_at']) ? $orden : 'producto_id', $dir);

        $perPage = (int) $request->query('per_page', 15);

        return ExistenciaResource::collection($query->paginate($perPage));
    }

    public function show(Existencia $existencia): ExistenciaResource
    {
        return new ExistenciaResource($existencia->load(['producto', 'almacen']));
    }
}
