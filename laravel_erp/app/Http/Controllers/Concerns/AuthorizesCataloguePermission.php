<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Modules\Platform\Rbac\AccessControl;
use Illuminate\Http\Request;

/** Deny-by-default catalogue permission checks for non-admission modules. */
trait AuthorizesCataloguePermission
{
    protected function authorizePermission(Request $request, string ...$permissions): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);
        if ($user->role === 'admin' || $user->isAdmin()) {
            return;
        }
        abort_unless(
            app(AccessControl::class)->allowsAny($user, $permissions),
            403,
            'You do not hold the required permission for this action.',
        );
    }
}
