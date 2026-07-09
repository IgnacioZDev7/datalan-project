<?php

use App\Http\Controllers\Api\UbicacionController;
use Illuminate\Support\Facades\Route;

Route::get   ('ubicaciones',               [UbicacionController::class, 'index'])  ->middleware('can:ubicaciones.ver');
Route::post  ('ubicaciones',               [UbicacionController::class, 'store'])  ->middleware('can:ubicaciones.crear');
Route::get   ('ubicaciones/{ubicacion}',   [UbicacionController::class, 'show'])   ->middleware('can:ubicaciones.ver');
Route::put   ('ubicaciones/{ubicacion}',   [UbicacionController::class, 'update']) ->middleware('can:ubicaciones.editar');
Route::delete('ubicaciones/{ubicacion}',   [UbicacionController::class, 'destroy'])->middleware('can:ubicaciones.eliminar');
