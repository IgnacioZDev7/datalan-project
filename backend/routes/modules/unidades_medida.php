<?php

use App\Http\Controllers\Api\UnidadMedidaController;
use Illuminate\Support\Facades\Route;

Route::get   ('unidades-medida',               [UnidadMedidaController::class, 'index'])  ->middleware('can:unidades.ver');
Route::post  ('unidades-medida',               [UnidadMedidaController::class, 'store'])  ->middleware('can:unidades.crear');
Route::get   ('unidades-medida/{unidad_medidum}', [UnidadMedidaController::class, 'show'])   ->middleware('can:unidades.ver');
Route::put   ('unidades-medida/{unidad_medidum}', [UnidadMedidaController::class, 'update']) ->middleware('can:unidades.editar');
Route::delete('unidades-medida/{unidad_medidum}', [UnidadMedidaController::class, 'destroy'])->middleware('can:unidades.eliminar');
