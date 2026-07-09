<?php

use App\Http\Controllers\Api\KardexController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Kardex (reporte). Solo lectura. Es una consulta sobre los movimientos
 | con saldo corrido; no hay tabla kardex. Filtros: almacen_id, desde, hasta.
 */

Route::get('kardex/producto/{producto}', [KardexController::class, 'producto'])->middleware('can:reportes.ver');
