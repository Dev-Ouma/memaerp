<?php

declare(strict_types=1);

namespace App\Modules\Platform\Rbac;

use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\User;
use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;

final class RoleAssignmentService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly AccessControl $access,
    ) {}

    /**
     * @param  array{role_id: string, scope_type: string, scope_id?: string|null, expires_at?: string|null, grant_reason: string}  $data
     */
    public function grant(User $actor, User $target, array $data): UserRole
    {
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

        $assignment = DB::transaction(function () use ($actor, $data, $target): UserRole {
            $duplicate = UserRole::query()->where('user_id', $target->id)->where('role_id', $data['role_id'])
                ->where('scope_type', $data['scope_type'])->where('scope_id', $data['scope_id'] ?? null)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
            abort_if($duplicate, 422, 'This user already has an active equivalent role assignment.');

            $assignment = UserRole::create([
                'user_id' => $target->id,
                'role_id' => $data['role_id'],
                'scope_type' => $data['scope_type'],
                'scope_id' => $data['scope_id'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'grant_reason' => $data['grant_reason'],
                'granted_by' => $actor->id,
                'granted_at' => now(),
            ]);
            $this->audit->record('role.assignment.granted', [
                'actor_role' => $actor->activeRole(), 'subject_type' => User::class,
                'subject_id' => $target->id, 'after' => $assignment->load('role')->toArray(), 'classification' => 'confidential',
            ]);

            return $assignment;
        });
        $this->access->forget($target);

        return $assignment;
    }

    public function revoke(User $actor, UserRole $assignment, string $revocationReason): void
    {
        $assignment->load(['user', 'role']);
        abort_if(
            (int) $assignment->user_id === (int) $actor->id && $assignment->role->code === 'system_administrator',
            422,
            'You cannot revoke your own System Administrator assignment.',
        );
        $before = $assignment->toArray();
        $target = $assignment->user;
        DB::transaction(function () use ($actor, $assignment, $revocationReason, $before, $target): void {
            $assignment->delete();
            $this->audit->record('role.assignment.revoked', [
                'actor_role' => $actor->activeRole(), 'subject_type' => User::class,
                'subject_id' => $target->id, 'before' => $before,
                'after' => ['revocation_reason' => $revocationReason], 'classification' => 'confidential',
            ]);
        });
        $this->access->forget($target);
    }
}
