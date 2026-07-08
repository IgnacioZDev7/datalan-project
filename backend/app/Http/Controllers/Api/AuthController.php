<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login: valida credenciales, verifica que el usuario este activo,
     * registra el ultimo acceso y emite un token Sanctum (Bearer).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::where('correo_electronico', $request->correo_electronico)->first();

        if (! $usuario || ! Hash::check($request->contrasena, $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (! $usuario->activo) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['El usuario esta desactivado. Contacte al administrador.'],
            ]);
        }

        $usuario->forceFill(['ultimo_acceso' => now()])->save();

        $token = $usuario->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Datos del usuario autenticado (con roles y permisos).
     */
    public function me(Request $request): UsuarioResource
    {
        return new UsuarioResource($request->user());
    }

    /**
     * Logout: revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesion cerrada correctamente.']);
    }
}
