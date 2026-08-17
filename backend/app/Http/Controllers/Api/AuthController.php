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

    /**
     * Cambio de la propia contraseña por el usuario autenticado.
     * Exige la contraseña actual y aplica el estandar de seguridad.
     */
    public function cambiarContrasena(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ], [
            'contrasena_actual.required' => 'Debes ingresar tu contraseña actual.',
            'contrasena.required' => 'La nueva contraseña es obligatoria.',
            'contrasena.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
            'contrasena.regex' => 'La contraseña debe incluir letras y números.',
        ]);

        $usuario = $request->user();

        if (! Hash::check($datos['contrasena_actual'], $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'contrasena_actual' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $usuario->update(['contrasena' => $datos['contrasena']]);

        // Seguridad: cierra las demas sesiones abiertas, conserva la actual.
        $tokenActualId = $request->user()->currentAccessToken()->id;
        $usuario->tokens()->where('id', '!=', $tokenActualId)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
