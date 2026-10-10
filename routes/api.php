<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Todas las rutas de este archivo se montan bajo el prefijo /api.
// Por eso aquí la ruta es /v1/health y se sirve como /api/v1/health.
Route::get('/v1/health', HealthController::class);
