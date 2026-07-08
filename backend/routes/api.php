<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Autenticacion ---
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // === MODULO: Productos (referencia canonica - ver docs/guia-desarrollo-modulos.md) ===
        Route::get   ('productos',            [ProductoController::class, 'index'])  ->middleware('can:productos.ver');
        Route::post  ('productos',            [ProductoController::class, 'store'])  ->middleware('can:productos.crear');
        Route::get   ('productos/{producto}', [ProductoController::class, 'show'])   ->middleware('can:productos.ver');
        Route::put   ('productos/{producto}', [ProductoController::class, 'update']) ->middleware('can:productos.editar');
        Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->middleware('can:productos.eliminar');

        // Aqui se montaran los demas modulos (Fase 5).
    });
});
