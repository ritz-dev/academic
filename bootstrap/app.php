<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'gateway',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
       //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    // api: [
    //     'file' => __DIR__.'/../routes/api.php',
    //     'prefix' => 'api-gateway', // Change the prefix here
    // ],

    // api: __DIR__.'/../routes/api.php',
