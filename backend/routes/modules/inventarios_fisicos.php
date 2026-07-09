<?php

use App\Http\Controllers\Api\InventarioFisicoController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Conteo fisico (inventarios_fisicos). Track principal.
 | Al cerrar, la reconciliacion genera un movimiento de tipo 'ajuste' con las
 | diferencias (via MovimientoService). Los movimientos son inmutables; el conteo
 | se corrige anulando (mientras este en_proceso) y creando uno nuevo.
 */

Route::get   ('inventarios-fisicos',                    [InventarioFisicoController::class, 'index'])  ->middleware('can:inventarios_fisicos.ver');
Route::post  ('inventarios-fisicos',                    [InventarioFisicoController::class, 'store'])  ->middleware('can:inventarios_fisicos.crear');
Route::get   ('inventarios-fisicos/{inventario}',       [InventarioFisicoController::class, 'show'])   ->middleware('can:inventarios_fisicos.ver');
Route::post  ('inventarios-fisicos/{inventario}/cerrar', [InventarioFisicoController::class, 'cerrar']) ->middleware('can:inventarios_fisicos.editar');
Route::delete('inventarios-fisicos/{inventario}',       [InventarioFisicoController::class, 'destroy'])->middleware('can:inventarios_fisicos.eliminar');
