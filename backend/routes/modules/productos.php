<?php

use App\Http\Controllers\Api\ProductoController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Productos (referencia canonica).
 | Este archivo se carga automaticamente dentro del grupo v1 + auth:sanctum
 | (ver routes/api.php). NO hace falta tocar ningun archivo compartido.
 */

Route::get   ('productos',            [ProductoController::class, 'index'])  ->middleware('can:productos.ver');
Route::post  ('productos',            [ProductoController::class, 'store'])  ->middleware('can:productos.crear');
Route::get   ('productos/{producto}', [ProductoController::class, 'show'])   ->middleware('can:productos.ver');
Route::put   ('productos/{producto}', [ProductoController::class, 'update']) ->middleware('can:productos.editar');
Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->middleware('can:productos.eliminar');
