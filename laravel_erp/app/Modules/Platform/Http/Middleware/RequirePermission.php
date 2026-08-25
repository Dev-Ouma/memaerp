<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Rbac\Scope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level capability gate: `permission:admission.decision.final`.
 *
 * This is the coarse check — it proves the user holds the permission somewhere. Controllers still
 * re-check with the resource's Scope before acting on a specific application, because holding
 * `admission.review.perform` in one department must not authorise a review in another.
 */
final class RequirePermission
{
    public function __construct(private readonly AccessControl $access) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            throw ApiException::make(401, 'UNAUTHENTICATED', 'Authentication is required.');
        }

        if (! $this->access->allowsAny($user, $permissions, Scope::none())) {
            throw ApiException::forbidden(
                'This action requires one of: '.implode(', ', $permissions).'.',
            );
        }

        return $next($request);
    }
}
