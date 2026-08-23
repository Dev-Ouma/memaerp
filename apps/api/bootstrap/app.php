<?php

use App\Platform\Http\Middleware\EstablishRequestContext;
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
        // SPA cookie authentication for the seven first-party Next.js apps (Sanctum stateful).
        $middleware->statefulApi();

        // Prepended, so the correlation id exists before authentication runs — a failed sign-in
        // and the audit record describing it must share one.
        $middleware->prepend(EstablishRequestContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
