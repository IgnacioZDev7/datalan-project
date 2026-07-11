<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Http\Resources\EmpresaResource;
use App\Models\Direccion;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Empresa::query()->with('direccion');

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

        return EmpresaResource::collection($query->paginate($perPage));
    }

    public function store(StoreEmpresaRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                $data['direccion_id'] = Direccion::create($dir)->id;
            }

            $empresa = Empresa::create($data);

            return (new EmpresaResource($empresa->load('direccion')))
                ->response()
                ->setStatusCode(201);
        });
    }

    public function show(Empresa $empresa): EmpresaResource
    {
        return new EmpresaResource($empresa->load('direccion'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa): EmpresaResource
    {
        return DB::transaction(function () use ($request, $empresa) {
            $data = $request->validated();
            $dir = $data['direccion'] ?? null;
            unset($data['direccion']);

            if ($dir && array_filter($dir)) {
                if ($empresa->direccion_id) {
                    $empresa->direccion->update($dir);
                } else {
                    $data['direccion_id'] = Direccion::create($dir)->id;
                }
            }

            $empresa->update($data);

            return new EmpresaResource($empresa->load('direccion'));
        });
    }

    public function destroy(Empresa $empresa): JsonResponse
    {
        $empresa->delete();

        return response()->json(['message' => 'Empresa eliminada correctamente.']);
    }
}
