<?php

use App\Http\Controllers\Api\MarcaController;
use Illuminate\Support\Facades\Route;

Route::get   ('marcas',            [MarcaController::class, 'index'])  ->middleware('can:marcas.ver');
Route::post  ('marcas',            [MarcaController::class, 'store'])  ->middleware('can:marcas.crear');
Route::get   ('marcas/{marca}',    [MarcaController::class, 'show'])   ->middleware('can:marcas.ver');
Route::put   ('marcas/{marca}',    [MarcaController::class, 'update']) ->middleware('can:marcas.editar');
Route::delete('marcas/{marca}',    [MarcaController::class, 'destroy'])->middleware('can:marcas.eliminar');
