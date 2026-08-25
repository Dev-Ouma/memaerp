<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks a response as safely cacheable — used only by the public programme catalogue, which contains
 * no personal data and is read far more often than it changes. Everything else stays `no-store`.
 */
final class CachePublicly
{
    public function handle(Request $request, Closure $next, string $seconds = '60'): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 200 && $request->isMethod('GET')) {
            $response->headers->set('Cache-Control', 'public, max-age='.(int) $seconds.', s-maxage='.(int) $seconds);
        }

        return $response;
    }
}
