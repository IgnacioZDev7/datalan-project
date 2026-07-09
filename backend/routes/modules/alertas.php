<?php

use App\Http\Controllers\Api\AlertaController;
use Illuminate\Support\Facades\Route;

Route::get   ('alertas',            [AlertaController::class, 'index'])  ->middleware('can:alertas.ver');
Route::post  ('alertas',            [AlertaController::class, 'store'])  ->middleware('can:alertas.crear');
Route::get   ('alertas/{alertum}',  [AlertaController::class, 'show'])   ->middleware('can:alertas.ver');
Route::put   ('alertas/{alertum}',  [AlertaController::class, 'update']) ->middleware('can:alertas.editar');
Route::delete('alertas/{alertum}',  [AlertaController::class, 'destroy'])->middleware('can:alertas.eliminar');
