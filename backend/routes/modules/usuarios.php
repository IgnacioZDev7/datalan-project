<?php

use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Usuarios (administracion) + Roles. Track principal (seguridad).
 | destroy = desactivar (baja logica con el flag activo, no borra). Asigna roles con Spatie.
 */

Route::get   ('usuarios',            [UsuarioController::class, 'index'])  ->middleware('can:usuarios.ver');
Route::post  ('usuarios',            [UsuarioController::class, 'store'])  ->middleware('can:usuarios.crear');
Route::get   ('usuarios/{usuario}',  [UsuarioController::class, 'show'])   ->middleware('can:usuarios.ver');
Route::put   ('usuarios/{usuario}',  [UsuarioController::class, 'update']) ->middleware('can:usuarios.editar');
Route::delete('usuarios/{usuario}',  [UsuarioController::class, 'destroy'])->middleware('can:usuarios.eliminar');

// Roles y permisos (lectura, para la UI de administracion).
Route::get('roles',    [RolController::class, 'index'])   ->middleware('can:roles.ver');
Route::get('permisos', [RolController::class, 'permisos'])->middleware('can:roles.ver');
