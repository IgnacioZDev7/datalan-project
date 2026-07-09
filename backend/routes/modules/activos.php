<?php

use App\Http\Controllers\Api\ActivoController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Activos (equipos + herramientas). Track principal.
 | Reglas: situacion=asignado exige tecnico_id; se registra activo_historial al
 | cambiar estado/situacion; las credenciales van cifradas y nunca en la respuesta.
 */

Route::get   ('activos',          [ActivoController::class, 'index'])  ->middleware('can:activos.ver');
Route::post  ('activos',          [ActivoController::class, 'store'])  ->middleware('can:activos.crear');
Route::get   ('activos/{activo}', [ActivoController::class, 'show'])   ->middleware('can:activos.ver');
Route::put   ('activos/{activo}', [ActivoController::class, 'update']) ->middleware('can:activos.editar');
Route::delete('activos/{activo}', [ActivoController::class, 'destroy'])->middleware('can:activos.eliminar');
