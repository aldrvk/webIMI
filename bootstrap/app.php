<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\DynamicDBConnection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // RBAC: Dynamic database connection based on user role
    $middleware->web(append: [
        DynamicDBConnection::class,
    ]);
        $middleware->alias([
            'role' => CheckRole::class,
            'kis.active' => \App\Http\Middleware\CheckKisStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
