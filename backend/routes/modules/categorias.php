<?php

use App\Http\Controllers\Api\CategoriaController;
use Illuminate\Support\Facades\Route;

Route::get   ('categorias',            [CategoriaController::class, 'index'])  ->middleware('can:categorias.ver');
Route::post  ('categorias',            [CategoriaController::class, 'store'])  ->middleware('can:categorias.crear');
Route::get   ('categorias/{categoria}', [CategoriaController::class, 'show'])   ->middleware('can:categorias.ver');
Route::put   ('categorias/{categoria}', [CategoriaController::class, 'update']) ->middleware('can:categorias.editar');
Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->middleware('can:categorias.eliminar');
