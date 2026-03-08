<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | ROUTING
    |--------------------------------------------------------------------------
    */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | MIDDLEWARE
    |--------------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | GLOBAL MIDDLEWARE (future usage)
        |--------------------------------------------------------------------------
        */
        // $middleware->append(SomeGlobalMiddleware::class);

        /*
        |--------------------------------------------------------------------------
        | ALIAS MIDDLEWARE
        |--------------------------------------------------------------------------
        */
        $middleware->alias([

            // Spatie Permission
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            //  AUTO PERMISSION (NOTRE MIDDLEWARE INTELLIGENT)
            'auto.permission' => \App\Http\Middleware\AutoPermissionMiddleware::class,
        ]);

    })

    /*
    |--------------------------------------------------------------------------
    | EXCEPTIONS
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Custom error handling (optional)
        |--------------------------------------------------------------------------
        */
        // $exceptions->render(function (Throwable $e) {
        //     return response()->view('errors.custom', [], 500);
        // });

    })

    ->create();