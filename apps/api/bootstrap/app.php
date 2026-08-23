<?php

use App\Platform\Http\ApiErrorResponse;
use App\Platform\Http\Middleware\EstablishRequestContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make('UNAUTHENTICATED', 'Authentication is required.', 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make('FORBIDDEN', 'You are not allowed to perform this action.', 403);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                'VALIDATION_FAILED',
                'The request contains invalid fields.',
                422,
                $exception->errors(),
            );
        });

        $notFound = function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
        };
        $exceptions->render($notFound);

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $retryAfter = (string) ($exception->getHeaders()['Retry-After'] ?? 60);

            return ApiErrorResponse::make(
                'RATE_LIMITED',
                'Too many requests. Retry later.',
                429,
                headers: ['Retry-After' => $retryAfter],
            );
        });
    })->create();
