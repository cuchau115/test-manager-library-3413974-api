<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class LibrarySchemaProvider extends ServiceProvider
{
    public function boot(): void
    {
        try {
            DB::statement('CREATE SCHEMA IF NOT EXISTS library');
        } catch (\Throwable) {
            // Base de datos aún no disponible (p.ej., health check sin DB)
        }
    }
}