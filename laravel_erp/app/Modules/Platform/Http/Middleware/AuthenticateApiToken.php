<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Auth\ApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-token authentication for `/api/v1`.
 *
 * Failures are deliberately uniform — an unknown token, an expired token and a revoked token all return
 * the same 401 with the same message, so the response cannot be used to classify tokens.
 */
final class AuthenticateApiToken
{
    public function __construct(private readonly ApiTokenService $tokens) {}

    public function handle(Request $request, Closure $next, string $ability = '*'): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '') {
            throw ApiException::make(401, 'UNAUTHENTICATED', 'Authentication is required.', 'Present a bearer token in the Authorization header.');
        }

        $token = $this->tokens->resolve($bearer);

        if ($token === null) {
            throw ApiException::make(401, 'UNAUTHENTICATED', 'Authentication is required.', 'The credentials presented are not valid.');
        }

        $user = $token->user()->first();

        if ($user === null || ! $user->is_active) {
            throw ApiException::make(401, 'UNAUTHENTICATED', 'Authentication is required.', 'The credentials presented are not valid.');
        }

        $abilities = (array) $token->abilities;

        if ($ability !== '*' && ! in_array('*', $abilities, true) && ! in_array($ability, $abilities, true)) {
            throw ApiException::forbidden('This token is not permitted to perform that action.');
        }

        $this->tokens->touch($token);

        auth()->setUser($user);
        $request->setUserResolver(static fn () => $user);
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
