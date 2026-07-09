<?php

use App\Http\Controllers\Api\ProveedorController;
use Illuminate\Support\Facades\Route;

Route::get   ('proveedores',               [ProveedorController::class, 'index'])  ->middleware('can:proveedores.ver');
Route::post  ('proveedores',               [ProveedorController::class, 'store'])  ->middleware('can:proveedores.crear');
Route::get   ('proveedores/{proveedor}',   [ProveedorController::class, 'show'])   ->middleware('can:proveedores.ver');
Route::put   ('proveedores/{proveedor}',   [ProveedorController::class, 'update']) ->middleware('can:proveedores.editar');
Route::delete('proveedores/{proveedor}',   [ProveedorController::class, 'destroy'])->middleware('can:proveedores.eliminar');
