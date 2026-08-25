<?php

declare(strict_types=1);

namespace App\Modules\Platform\Rbac;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deny-by-default authorisation.
 *
 * Three independent questions are answered separately and must all pass: does the user hold the
 * permission, through a role that is currently valid, in a scope that covers this resource. Nothing
 * here grants anything implicitly — there is no wildcard, no owner override and no administrator
 * bypass. Ownership is a separate check performed by the self-service controllers.
 */
final class AccessControl
{
    /** @var array<int, list<array{permission: string, role: string, scope_type: string, scope_id: string|null}>> */
    private array $grantCache = [];

    public function allows(?User $user, string $permission, ?Scope $scope = null): bool
    {
        if ($user === null || ! $user->is_active) {
            return false;
        }

        $scope ??= Scope::none();

        foreach ($this->grantsFor($user) as $grant) {
            if ($grant['permission'] === $permission && $scope->matches($grant['scope_type'], $grant['scope_id'])) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $permissions */
    public function allowsAny(?User $user, array $permissions, ?Scope $scope = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($user, $permission, $scope)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> every permission code the user holds, in any scope */
    public function permissionCodes(User $user): array
    {
        return array_values(array_unique(array_column($this->grantsFor($user), 'permission')));
    }

    /** @return list<string> */
    public function roleCodes(User $user): array
    {
        return array_values(array_unique(array_column($this->grantsFor($user), 'role')));
    }

    public function forget(User $user): void
    {
        unset($this->grantCache[(int) $user->id]);
    }

    /**
     * @return list<array{permission: string, role: string, scope_type: string, scope_id: string|null}>
     */
    private function grantsFor(User $user): array
    {
        $id = (int) $user->id;

        if (isset($this->grantCache[$id])) {
            return $this->grantCache[$id];
        }

        $rows = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->join('role_permissions', 'role_permissions.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('user_roles.user_id', $id)
            ->where(function ($query): void {
                $query->whereNull('user_roles.expires_at')->orWhere('user_roles.expires_at', '>', now());
            })
            ->get(['permissions.code as permission', 'roles.code as role', 'user_roles.scope_type', 'user_roles.scope_id']);

        return $this->grantCache[$id] = $rows
            ->map(static fn ($row): array => [
                'permission' => $row->permission,
                'role' => $row->role,
                'scope_type' => $row->scope_type,
                'scope_id' => $row->scope_id === null ? null : (string) $row->scope_id,
            ])
            ->values()
            ->all();
    }
}
