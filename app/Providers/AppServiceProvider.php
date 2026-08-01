<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('FIREBASE_CREDENTIALS_JSON')) {
            $path = storage_path('app/firebase_credentials.json');
            if (!file_exists($path)) {
                @mkdir(dirname($path), 0755, true);
                @file_put_contents($path, env('FIREBASE_CREDENTIALS_JSON'));
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Schema::defaultStringLength(191);
        Response::macro('error404', function () {
            return response()->view('auth.404', [], 404);
        });
    }
}
