<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
 | Modulo: Dashboard.
 | Endpoint de solo lectura que agrega KPIs + gráficas en una sola petición.
 | Se carga automáticamente dentro del grupo v1 + auth:sanctum (ver routes/api.php).
 */

Route::get('dashboard/estadisticas', [DashboardController::class, 'estadisticas'])
    ->middleware('can:reportes.ver');
