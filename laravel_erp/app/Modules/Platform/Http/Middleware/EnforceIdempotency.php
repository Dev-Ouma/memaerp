<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Idempotency\IdempotencyStore;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the idempotency contract to state-changing endpoints.
 *
 * Used as `idempotent` (key optional but honoured) or `idempotent:required` on the endpoints where a
 * duplicate would cost real money or create a duplicate record — payment initiation and application
 * submission. Only successful responses are recorded: a failed attempt must be retryable.
 */
final class EnforceIdempotency
{
    public function __construct(private readonly IdempotencyStore $store) {}

    public function handle(Request $request, Closure $next, string $mode = 'optional'): Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        $key = trim((string) $request->header('Idempotency-Key', ''));

        if ($key === '') {
            if ($mode === 'required') {
                throw ApiException::unprocessable(
                    'IDEMPOTENCY_KEY_REQUIRED',
                    'An Idempotency-Key header is required for this request.',
                    ['Idempotency-Key' => ['Send a unique key (a UUID is fine) so retries are safe.']],
                );
            }

            return $next($request);
        }

        if (strlen($key) > 190) {
            throw ApiException::unprocessable('IDEMPOTENCY_KEY_INVALID', 'The idempotency key is too long.', [
                'Idempotency-Key' => ['Use at most 190 characters.'],
            ]);
        }

        $route = $request->method().' '.($request->route()?->uri() ?? $request->path());
        $principal = (string) ($request->user()?->getAuthIdentifier() ?? 'ip:'.$request->ip());
        $hash = IdempotencyStore::hashRequest($request->all());

        $outcome = $this->store->begin($key, $route, $principal, $hash);

        if ($outcome['status'] === 'replay') {
            return (new JsonResponse($outcome['replay']['body'], $outcome['replay']['status']))
                ->header('Idempotent-Replay', 'true')
                ->header('X-Correlation-Id', correlation_id());
        }

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $this->store->release($key, $route, $principal);

            throw $e;
        }

        if ($response->getStatusCode() >= 400) {
            $this->store->release($key, $route, $principal);

            return $response;
        }

        $body = $response instanceof JsonResponse ? $response->getData(true) : null;
        $this->store->complete($key, $route, $principal, $response->getStatusCode(), $body);

        return $response;
    }
}
