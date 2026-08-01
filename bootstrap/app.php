<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

// Write Firebase credentials JSON to file dynamically on bootstrap
if (isset($_ENV['FIREBASE_CREDENTIALS_JSON']) || isset($_SERVER['FIREBASE_CREDENTIALS_JSON']) || env('FIREBASE_CREDENTIALS_JSON')) {
    $_ENV['GOOGLE_CLOUD_PROJECT'] = 'easyfind-realestate';
    $_SERVER['GOOGLE_CLOUD_PROJECT'] = 'easyfind-realestate';
    putenv('GOOGLE_CLOUD_PROJECT=easyfind-realestate');

    $path = dirname(__DIR__) . '/storage/app/firebase_credentials.json';
    if (!file_exists($path)) {
        @mkdir(dirname($path), 0755, true);
        @file_put_contents($path, $_ENV['FIREBASE_CREDENTIALS_JSON'] ?? $_SERVER['FIREBASE_CREDENTIALS_JSON'] ?? env('FIREBASE_CREDENTIALS_JSON'));
    }
}



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRoleMiddleware::class,
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'password.notset' => \App\Http\Middleware\RedirectIfPasswordIsSet::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withProviders([
        \App\Providers\BroadcastServiceProvider::class,
    ])->create();
    
