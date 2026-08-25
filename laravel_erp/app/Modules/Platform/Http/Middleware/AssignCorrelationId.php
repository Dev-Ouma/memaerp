<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every request an id that appears in the response, the logs, the audit trail and the outbox.
 *
 * A client-supplied `X-Correlation-Id` is honoured so a trace can span the frontend and the API, but
 * only when it is a well-formed UUID. Anything else is replaced rather than sanitised: the id is
 * written to a `uuid` column and interpolated into log lines, and accepting arbitrary text there is
 * both a type error waiting to happen and a log-injection vector.
 */
final class AssignCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $supplied = (string) $request->header('X-Correlation-Id', '');

        $correlationId = Str::isUuid($supplied) ? strtolower($supplied) : (string) Str::uuid();
        set_correlation_id($correlationId);

        Log::shareContext(['correlation_id' => $correlationId]);

        $response = $next($request);
        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}
