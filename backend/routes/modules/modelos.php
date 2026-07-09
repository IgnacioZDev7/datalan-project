<?php

use App\Http\Controllers\Api\ModeloController;
use Illuminate\Support\Facades\Route;

Route::get   ('modelos',            [ModeloController::class, 'index'])  ->middleware('can:modelos.ver');
Route::post  ('modelos',            [ModeloController::class, 'store'])  ->middleware('can:modelos.crear');
Route::get   ('modelos/{modelo}',   [ModeloController::class, 'show'])   ->middleware('can:modelos.ver');
Route::put   ('modelos/{modelo}',   [ModeloController::class, 'update']) ->middleware('can:modelos.editar');
Route::delete('modelos/{modelo}',   [ModeloController::class, 'destroy'])->middleware('can:modelos.eliminar');
