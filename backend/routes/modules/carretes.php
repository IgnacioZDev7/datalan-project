<?php

use App\Http\Controllers\Api\CarreteController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Carretes (cable de fibra por metraje). Track principal.
 | Regla: metraje_disponible <= metraje_inicial. El consumo de metros se hace via
 | movimientos (no editando el saldo directo).
 */

Route::get   ('carretes',           [CarreteController::class, 'index'])  ->middleware('can:carretes.ver');
Route::post  ('carretes',           [CarreteController::class, 'store'])  ->middleware('can:carretes.crear');
Route::get   ('carretes/{carrete}', [CarreteController::class, 'show'])   ->middleware('can:carretes.ver');
Route::put   ('carretes/{carrete}', [CarreteController::class, 'update']) ->middleware('can:carretes.editar');
Route::delete('carretes/{carrete}', [CarreteController::class, 'destroy'])->middleware('can:carretes.eliminar');
