<?php

use App\Http\Controllers\Api\BitacoraController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Bitácora / Auditoría (solo lectura).
 | Lista paginada del activity log de Spatie con filtros: usuario_id, desde, hasta, buscar.
 */

Route::get('bitacora', [BitacoraController::class, 'index'])
    ->middleware('can:bitacora.ver');
