<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->web(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $middleware->web(\Illuminate\Session\Middleware\StartSession::class);
        $middleware->web(\App\Http\Middleware\SetLocale::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'subscription' => \App\Http\Middleware\SubscriptionMiddleware::class,
        ]);
        $middleware->api(\App\Http\Middleware\CustomCorsMiddleware::class);
        $middleware->api(\Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
