<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defensive response headers for a JSON API.
 *
 * `no-store` matters more than usual here: responses routinely contain applicant personal data, and an
 * intermediary or browser cache holding an admission decision would be a disclosure. HSTS is only
 * emitted over HTTPS so local development is unaffected.
 */
final class ApplySecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none'");

        // Laravel always sets a Cache-Control header, so "not set" is never the signal. Anything that
        // has not deliberately marked itself public (the catalogue, via the cache.public middleware)
        // carries applicant data and must not be stored by a browser or an intermediary.
        if (! str_contains((string) $response->headers->get('Cache-Control'), 'public')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
