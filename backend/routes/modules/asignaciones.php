<?php

use App\Http\Controllers\Api\AsignacionController;
use Illuminate\Support\Facades\Route;

Route::get   ('asignaciones',                [AsignacionController::class, 'index'])  ->middleware('can:asignaciones.ver');
Route::post  ('asignaciones',                [AsignacionController::class, 'store'])  ->middleware('can:asignaciones.crear');
Route::get   ('asignaciones/{asignacione}',  [AsignacionController::class, 'show'])   ->middleware('can:asignaciones.ver');
Route::put   ('asignaciones/{asignacione}',  [AsignacionController::class, 'update']) ->middleware('can:asignaciones.editar');
Route::delete('asignaciones/{asignacione}',  [AsignacionController::class, 'destroy'])->middleware('can:asignaciones.eliminar');
