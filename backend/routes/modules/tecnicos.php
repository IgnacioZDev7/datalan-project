<?php

use App\Http\Controllers\Api\TecnicoController;
use Illuminate\Support\Facades\Route;

Route::get   ('tecnicos',            [TecnicoController::class, 'index'])  ->middleware('can:tecnicos.ver');
Route::post  ('tecnicos',            [TecnicoController::class, 'store'])  ->middleware('can:tecnicos.crear');
Route::get   ('tecnicos/{tecnico}',  [TecnicoController::class, 'show'])   ->middleware('can:tecnicos.ver');
Route::put   ('tecnicos/{tecnico}',  [TecnicoController::class, 'update']) ->middleware('can:tecnicos.editar');
Route::delete('tecnicos/{tecnico}',  [TecnicoController::class, 'destroy'])->middleware('can:tecnicos.eliminar');
