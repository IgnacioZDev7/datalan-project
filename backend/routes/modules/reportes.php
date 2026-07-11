<?php

use App\Http\Controllers\Api\ReporteController;
use Illuminate\Support\Facades\Route;

/*
 | Reportes (PDF/Excel). Solo lectura. Requieren permiso 'reportes.exportar'.
 */

Route::get('reportes/existencias/excel', [ReporteController::class, 'existenciasExcel'])->middleware('can:reportes.exportar');
Route::get('reportes/existencias/pdf',   [ReporteController::class, 'existenciasPdf'])->middleware('can:reportes.exportar');
Route::get('reportes/movimientos/excel', [ReporteController::class, 'movimientosExcel'])->middleware('can:reportes.exportar');
Route::get('reportes/movimientos/pdf',   [ReporteController::class, 'movimientosPdf'])->middleware('can:reportes.exportar');
Route::get('reportes/kardex/{producto}/pdf', [ReporteController::class, 'kardexPdf'])->middleware('can:reportes.exportar');
