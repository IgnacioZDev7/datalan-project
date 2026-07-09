<?php

use App\Http\Controllers\Api\ExistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('existencias',            [ExistenciaController::class, 'index'])->middleware('can:existencias.ver');
Route::get('existencias/{existencia}', [ExistenciaController::class, 'show'])->middleware('can:existencias.ver');
