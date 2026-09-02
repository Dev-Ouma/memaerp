<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\User;
use App\Modules\Platform\Audit\AuditRecorder;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class RoleAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $users = User::query()->with(['stakeholderTypes'])
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where(fn ($search) => $search->where('name', 'ilike', "%{$value}%")->orWhere('email', 'ilike', "%{$value}%")))
            ->withCount('rbacAssignments')->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $assignments = UserRole::query()->with(['user', 'role'])->latest('granted_at')->paginate(25, ['*'], 'assignments_page');

        return view('admin.access.index', compact('users', 'roles', 'assignments'));
    }

    public function store(Request $request, AuditRecorder $audit, AccessControl $access): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'scope_type' => ['required', Rule::in(PermissionCatalogue::SCOPE_TYPES)],
            'scope_id' => ['nullable', 'string', 'max:64', 'required_unless:scope_type,institution'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'grant_reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);
        $target = User::query()->findOrFail($data['user_id']);
        abort_if(! $target->is_active, 422, 'Roles cannot be granted to an inactive account.');
        $role = Role::query()->with('permissions')->findOrFail($data['role_id']);
        $grantsSegregatedAuthority = $role->permissions->contains('is_segregated', true);
        $targetIsSystemAdministrator = UserRole::query()->where('user_id', $target->id)
            ->whereHas('role', fn ($query) => $query->where('code', 'system_administrator'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
        $targetHasSegregatedAuthority = UserRole::query()->where('user_id', $target->id)
            ->whereHas('role.permissions', fn ($query) => $query->where('is_segregated', true))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
        abort_if(
            ($grantsSegregatedAuthority && $targetIsSystemAdministrator)
            || ($role->code === 'system_administrator' && $targetHasSegregatedAuthority),
            422,
            'System administration and segregated operational authority cannot be assigned to the same user.',
        );

        $assignment = DB::transaction(function () use ($request, $data, $target, $audit): UserRole {
            $duplicate = UserRole::query()->where('user_id', $target->id)->where('role_id', $data['role_id'])
                ->where('scope_type', $data['scope_type'])->where('scope_id', $data['scope_id'] ?? null)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
            abort_if($duplicate, 422, 'This user already has an active equivalent role assignment.');

            $assignment = UserRole::create([
                ...$data, 'scope_id' => $data['scope_id'] ?? null,
                'granted_by' => $request->user()->id, 'granted_at' => now(),
            ]);
            $audit->record('role.assignment.granted', [
                'actor_role' => $request->user()->activeRole(), 'subject_type' => User::class,
                'subject_id' => $target->id, 'after' => $assignment->load('role')->toArray(), 'classification' => 'confidential',
            ]);

            return $assignment;
        });
        $access->forget($target);

        return back()->with('success', "Role assignment {$assignment->id} granted.");
    }

    public function destroy(Request $request, UserRole $assignment, AuditRecorder $audit, AccessControl $access): RedirectResponse
    {
        $data = $request->validate(['revocation_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $assignment->load(['user', 'role']);
        abort_if(
            (int) $assignment->user_id === (int) $request->user()->id && $assignment->role->code === 'system_administrator',
            422,
            'You cannot revoke your own System Administrator assignment.',
        );
        $before = $assignment->toArray();
        $target = $assignment->user;
        DB::transaction(function () use ($request, $assignment, $data, $before, $audit, $target): void {
            $assignment->delete();
            $audit->record('role.assignment.revoked', [
                'actor_role' => $request->user()->activeRole(), 'subject_type' => User::class,
                'subject_id' => $target->id, 'before' => $before,
                'after' => ['revocation_reason' => $data['revocation_reason']], 'classification' => 'confidential',
            ]);
        });
        $access->forget($target);

        return back()->with('success', 'Role assignment revoked and audited.');
    }
}
