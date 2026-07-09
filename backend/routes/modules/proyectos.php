<?php

use App\Http\Controllers\Api\ProyectoController;
use Illuminate\Support\Facades\Route;

Route::get   ('proyectos',             [ProyectoController::class, 'index'])  ->middleware('can:proyectos.ver');
Route::post  ('proyectos',             [ProyectoController::class, 'store'])  ->middleware('can:proyectos.crear');
Route::get   ('proyectos/{proyecto}',  [ProyectoController::class, 'show'])   ->middleware('can:proyectos.ver');
Route::put   ('proyectos/{proyecto}',  [ProyectoController::class, 'update']) ->middleware('can:proyectos.editar');
Route::delete('proyectos/{proyecto}',  [ProyectoController::class, 'destroy'])->middleware('can:proyectos.eliminar');
