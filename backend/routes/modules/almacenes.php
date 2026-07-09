<?php

use App\Http\Controllers\Api\AlmacenController;
use Illuminate\Support\Facades\Route;

Route::get   ('almacenes',            [AlmacenController::class, 'index'])  ->middleware('can:almacenes.ver');
Route::post  ('almacenes',            [AlmacenController::class, 'store'])  ->middleware('can:almacenes.crear');
Route::get   ('almacenes/{almacen}',  [AlmacenController::class, 'show'])   ->middleware('can:almacenes.ver');
Route::put   ('almacenes/{almacen}',  [AlmacenController::class, 'update']) ->middleware('can:almacenes.editar');
Route::delete('almacenes/{almacen}',  [AlmacenController::class, 'destroy'])->middleware('can:almacenes.eliminar');
