<?php

use App\Http\Controllers\Api\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::get   ('empresas',            [EmpresaController::class, 'index'])  ->middleware('can:empresas.ver');
Route::post  ('empresas',            [EmpresaController::class, 'store'])  ->middleware('can:empresas.crear');
Route::get   ('empresas/{empresa}',  [EmpresaController::class, 'show'])   ->middleware('can:empresas.ver');
Route::put   ('empresas/{empresa}',  [EmpresaController::class, 'update']) ->middleware('can:empresas.editar');
Route::delete('empresas/{empresa}',  [EmpresaController::class, 'destroy'])->middleware('can:empresas.eliminar');
