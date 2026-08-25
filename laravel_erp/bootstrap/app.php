<?php

use App\Modules\Platform\Api\ApiExceptionRenderer;
use App\Modules\Platform\Http\Middleware\ApplySecurityHeaders;
use App\Modules\Platform\Http\Middleware\AssignCorrelationId;
use App\Modules\Platform\Http\Middleware\AuthenticateApiToken;
use App\Modules\Platform\Http\Middleware\CachePublicly;
use App\Modules\Platform\Http\Middleware\EnforceIdempotency;
use App\Modules\Platform\Http\Middleware\RequirePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Every API request carries a correlation id and defensive headers, authenticated or not.
        $middleware->api(prepend: [
            AssignCorrelationId::class,
            ApplySecurityHeaders::class,
        ]);

        $middleware->alias([
            'api.token' => AuthenticateApiToken::class,
            'permission' => RequirePermission::class,
            'idempotent' => EnforceIdempotency::class,
            'cache.public' => CachePublicly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Single rendering path for the API: RFC 7807 problem documents, never a stack trace.
        $exceptions->render(
            fn (\Throwable $e, Request $request) => app(ApiExceptionRenderer::class)->render($e, $request),
        );

        // Correlation ids make a logged error findable from the response the caller received.
        $exceptions->context(fn (): array => ['correlation_id' => correlation_id()]);
    })->create();
