<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Autenticacion (publica) ---
    Route::post('login', [AuthController::class, 'login']);

    // --- Rutas protegidas ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('perfil/contrasena', [AuthController::class, 'cambiarContrasena']);

        /*
         | Carga automatica de modulos: cada archivo routes/modules/*.php se registra
         | aqui (hereda prefijo v1 + auth:sanctum). Cada modulo vive en su propio
         | archivo -> desarrollo en paralelo SIN conflictos en un archivo compartido.
         */
        foreach (glob(base_path('routes/modules/*.php')) ?: [] as $moduloRutas) {
            require $moduloRutas;
        }
    });
});
