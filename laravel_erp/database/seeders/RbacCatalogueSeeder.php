<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Platform\Rbac\PermissionCatalogue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RbacCatalogueSeeder extends Seeder
{
    /**
     * Named desk accounts → catalogue roles. Keeps system_administrator free of
     * admission decision authority while giving each environment working desks.
     *
     * @var array<string, string>
     */
    private const DESK_GRANTS = [
        'registrar@mema.ac.ke' => 'registrar',
        'admissions.officer@mema.ac.ke' => 'admissions_officer',
        'finance.officer@mema.ac.ke' => 'finance_officer',
        'bursar@mema.ac.ke' => 'finance_officer',
        'curriculum.manager@mema.ac.ke' => 'curriculum_manager',
        'hr.officer@mema.ac.ke' => 'hr_officer',
        'dpo@mema.ac.ke' => 'data_protection_officer',
        'registration.officer@mema.ac.ke' => 'registration_officer',
        'transfers.officer@mema.ac.ke' => 'transfers_officer',
        'lms.manager@mema.ac.ke' => 'lms_manager',
        'graduation.officer@mema.ac.ke' => 'graduation_officer',
        'student.affairs@mema.ac.ke' => 'student_affairs_officer',
    ];

    public function run(): void
    {
        $violations = PermissionCatalogue::violations();
        if ($violations !== []) {
            throw new \RuntimeException(implode(PHP_EOL, $violations));
        }

        DB::transaction(function (): void {
            foreach (PermissionCatalogue::permissions() as $code => $definition) {
                $existing = DB::table('permissions')->where('code', $code)->first();
                $values = [
                    'module' => $definition['module'], 'resource' => $definition['resource'],
                    'action' => $definition['action'], 'description' => $definition['description'],
                    'classification' => $definition['classification'], 'is_segregated' => $definition['segregated'],
                    'updated_at' => now(),
                ];
                $existing
                    ? DB::table('permissions')->where('id', $existing->id)->update($values)
                    : DB::table('permissions')->insert(['id' => (string) Str::uuid(), 'code' => $code, ...$values, 'created_at' => now()]);
            }

            foreach (PermissionCatalogue::roles() as $code => $definition) {
                $existing = DB::table('roles')->where('code', $code)->first();
                $values = [
                    'name' => $definition['name'], 'description' => $definition['description'],
                    'default_scope_type' => $definition['default_scope_type'], 'is_system' => true, 'updated_at' => now(),
                ];
                $roleId = $existing?->id ?? (string) Str::uuid();
                $existing
                    ? DB::table('roles')->where('id', $roleId)->update($values)
                    : DB::table('roles')->insert(['id' => $roleId, 'code' => $code, ...$values, 'created_at' => now()]);

                $permissionIds = DB::table('permissions')->whereIn('code', $definition['permissions'])->pluck('id');
                DB::table('role_permissions')->where('role_id', $roleId)->whereNotIn('permission_id', $permissionIds)->delete();
                foreach ($permissionIds as $permissionId) {
                    DB::table('role_permissions')->insertOrIgnore([
                        'id' => (string) Str::uuid(), 'role_id' => $roleId,
                        'permission_id' => $permissionId, 'created_at' => now(),
                    ]);
                }
            }

            $systemAdminRole = DB::table('roles')->where('code', 'system_administrator')->value('id');
            DB::table('users')->where('role', 'admin')->where('is_active', true)->orderBy('id')->each(function ($user) use ($systemAdminRole): void {
                if (! DB::table('user_roles')->where('user_id', $user->id)->where('role_id', $systemAdminRole)->exists()) {
                    DB::table('user_roles')->insert([
                        'id' => (string) Str::uuid(), 'user_id' => $user->id, 'role_id' => $systemAdminRole,
                        'scope_type' => 'institution', 'scope_id' => null, 'granted_by' => null,
                        'granted_at' => now(), 'grant_reason' => 'Controlled bootstrap from the legacy administrator role.',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });

            $dpoRole = DB::table('roles')->where('code', 'data_protection_officer')->value('id');
            $teacher = DB::table('users')->where('email', 'teacher@mema.ac.ke')->first();
            if ($teacher && $dpoRole) {
                if (! DB::table('user_roles')->where('user_id', $teacher->id)->where('role_id', $dpoRole)->exists()) {
                    DB::table('user_roles')->insert([
                        'id' => (string) Str::uuid(), 'user_id' => $teacher->id, 'role_id' => $dpoRole,
                        'scope_type' => 'institution', 'scope_id' => null, 'granted_by' => null,
                        'granted_at' => now(), 'grant_reason' => 'Bootstrap data protection officer role for testing.',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            foreach (self::DESK_GRANTS as $email => $roleCode) {
                $user = DB::table('users')->whereRaw('lower(email) = ?', [mb_strtolower($email)])->first();
                $roleId = DB::table('roles')->where('code', $roleCode)->value('id');
                if ($user === null || $roleId === null) {
                    continue;
                }
                // Do not pile segregated desk authority onto a system administrator (SoD).
                if ($systemAdminRole && DB::table('user_roles')->where('user_id', $user->id)->where('role_id', $systemAdminRole)->exists()) {
                    continue;
                }
                if (DB::table('user_roles')->where('user_id', $user->id)->where('role_id', $roleId)->exists()) {
                    continue;
                }
                DB::table('user_roles')->insert([
                    'id' => (string) Str::uuid(), 'user_id' => $user->id, 'role_id' => $roleId,
                    'scope_type' => 'institution', 'scope_id' => null, 'granted_by' => null,
                    'granted_at' => now(), 'grant_reason' => 'Controlled bootstrap for named desk account.',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }
}
