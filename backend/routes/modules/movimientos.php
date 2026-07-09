<?php

use App\Http\Controllers\Api\MovimientoController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Movimientos (motor del inventario). Track principal.
 | Los movimientos son inmutables: no hay update. Se corrigen anulando (delete, que
 | revierte el stock) y creando uno nuevo. El efecto en existencias/carretes/activos
 | lo aplica MovimientoService dentro de una transaccion.
 */

Route::get   ('movimientos',              [MovimientoController::class, 'index'])  ->middleware('can:movimientos.ver');
Route::post  ('movimientos',              [MovimientoController::class, 'store'])  ->middleware('can:movimientos.crear');
Route::get   ('movimientos/{movimiento}', [MovimientoController::class, 'show'])   ->middleware('can:movimientos.ver');
Route::delete('movimientos/{movimiento}', [MovimientoController::class, 'destroy'])->middleware('can:movimientos.anular');
