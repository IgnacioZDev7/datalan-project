<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Usuario::query()->with('roles');

        if ($buscar = $request->query('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('correo_electronico', 'like', "%{$buscar}%");
            });
        }
        if (! is_null($request->query('activo'))) {
            $query->where('activo', $request->boolean('activo'));
        }

        return UsuarioResource::collection($query->orderBy('nombres')->paginate((int) $request->query('per_page', 15)));
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $usuario = Usuario::create($data);
        $usuario->syncRoles($roles);

        return (new UsuarioResource($usuario->load('roles')))->response()->setStatusCode(201);
    }

    public function show(Usuario $usuario): UsuarioResource
    {
        return new UsuarioResource($usuario->load('roles'));
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): UsuarioResource
    {
        $data = $request->validated();

        // La contraseña solo se cambia si viene (el cast 'hashed' la hashea sola).
        if (empty($data['contrasena'])) {
            unset($data['contrasena']);
        }

        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $usuario->update($data);

        if (! is_null($roles)) {
            $usuario->syncRoles($roles);
        }

        return new UsuarioResource($usuario->load('roles'));
    }

    /**
     * Desactiva el usuario (no se borra; baja lógica con el flag activo).
     */
    public function destroy(Request $request, Usuario $usuario): JsonResponse
    {
        if ($usuario->id === $request->user()->id) {
            throw ValidationException::withMessages(['usuario' => ['No puedes desactivarte a ti mismo.']]);
        }

        $usuario->update(['activo' => false]);
        $usuario->tokens()->delete(); // cierra sus sesiones

        return response()->json(['message' => 'Usuario desactivado.']);
    }
}
