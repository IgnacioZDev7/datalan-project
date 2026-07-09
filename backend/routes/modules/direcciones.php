<?php

use App\Http\Controllers\Api\DireccionController;
use Illuminate\Support\Facades\Route;

Route::get   ('direcciones',               [DireccionController::class, 'index'])  ->middleware('can:ubicaciones.ver');
Route::post  ('direcciones',               [DireccionController::class, 'store'])  ->middleware('can:ubicaciones.crear');
Route::get   ('direcciones/{direccion}',   [DireccionController::class, 'show'])   ->middleware('can:ubicaciones.ver');
Route::put   ('direcciones/{direccion}',   [DireccionController::class, 'update']) ->middleware('can:ubicaciones.editar');
Route::delete('direcciones/{direccion}',   [DireccionController::class, 'destroy'])->middleware('can:ubicaciones.eliminar');
