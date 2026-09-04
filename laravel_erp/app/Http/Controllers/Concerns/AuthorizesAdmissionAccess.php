<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Admission\ApprovalStep;
use App\Models\User;
use App\Modules\Admission\Workspaces\ApprovalWorkspace;
use App\Modules\Platform\Rbac\AccessControl;
use Illuminate\Http\Request;

/**
 * Deny-by-default admission authorisation via PermissionCatalogue grants.
 * Legacy `admin|staff` role strings are not sufficient on their own.
 */
trait AuthorizesAdmissionAccess
{
    protected function authorizeAdmission(Request $request, string ...$permissions): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);
        abort_unless(
            app(AccessControl::class)->allowsAny($user, $permissions),
            403,
            'You do not hold the required admissions permission for this action.',
        );
    }

    /**
     * Approval ladder rungs map to catalogue roles / decision permissions.
     * DEAN → decision.approve (HoD / manager / registrar)
     * SENATE_BOARD → decision.final or decision.check (registrar)
     */
    protected function authorizeApprovalStep(Request $request, ApprovalStep $step): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);

        $access = app(AccessControl::class);
        $permissions = match ($step->role_code) {
            ApprovalWorkspace::ROLE_BOARD => ['admission.decision.final', 'admission.decision.check'],
            default => ['admission.decision.approve', 'admission.decision.final'],
        };

        $roleCodes = match ($step->role_code) {
            ApprovalWorkspace::ROLE_DEAN => ['head_of_department', 'admissions_manager', 'registrar'],
            ApprovalWorkspace::ROLE_BOARD => ['registrar'],
            default => ['registrar', 'admissions_manager', 'head_of_department'],
        };

        $allowed = $access->allowsAny($user, $permissions)
            || count(array_intersect($access->roleCodes($user), $roleCodes)) > 0;

        abort_unless($allowed, 403, "You are not authorised to sign the {$step->role_code} rung.");
    }
}
