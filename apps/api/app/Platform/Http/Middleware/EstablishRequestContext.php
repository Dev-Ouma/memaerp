<?php

declare(strict_types=1);

namespace App\Platform\Http\Middleware;

use App\Platform\Support\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Populates the {@see RequestContext} at the very start of every request and echoes the
 * correlation id back on the response.
 *
 * Runs before authentication, so the correlation id exists even for requests that never
 * authenticate — a failed sign-in and the audit record describing it must share an id, or the
 * two cannot be tied together during an investigation.
 */
final class EstablishRequestContext
{
    public function __construct(private readonly RequestContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->hydrateFromRequest($request);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Correlation-Id', (string) $this->context->correlationId());
        $response->headers->set('X-Request-Id', (string) $this->context->requestId());

        return $response;
    }
}
