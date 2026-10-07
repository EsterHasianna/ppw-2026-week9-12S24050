<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        DB::listen(function ($query) {
            // Lewati kueri internal Laravel (session, cache, migrasi) agar log fokus
            if (preg_match('/\b(sessions|cache|cache_locks|migrations|jobs)\b/', $query->sql)) {
                return;
            }

            Log::info(sprintf('[SQL AUDIT] (%.2f ms) %s', $query->time, $query->sql));
        });
    }
}