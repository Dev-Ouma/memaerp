<?php

declare(strict_types=1);

namespace App\Modules\Iam\Database\Seeders;

use App\Modules\Iam\Models\Permission;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Support\RoleCatalogue;
use App\Modules\Institution\Models\Institution;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates the system roles for an institution and syncs their permission sets.
 *
 * Re-running this RESETS a system role's permissions to the catalogue. That is intentional: a
 * system role is defined by the software, and drift between what the code assumes a Dean can do
 * and what the database says is exactly the kind of gap that produces an authorization bug
 * nobody can reproduce. Institutions that need something different create a custom role.
 */
final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Institution::query()->each(function (Institution $institution): void {
            foreach (RoleCatalogue::all() as $definition) {
                $family = self::normalizedFamily($definition['code']);
                $role = Role::query()->updateOrCreate(
                    [
                        'institution_id' => $institution->getKey(),
                        'code' => $definition['code'],
                    ],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'family' => $family,
                        'is_system' => true,
                        'hierarchy_level' => self::hierarchyLevel($definition['code'], $family),
                        'is_mfa_mandatory' => self::requiresMfa($definition['code'], $family),
                        'default_scope_type' => $definition['default_scope'],
                    ],
                );

                $permissionIds = Permission::query()
                    ->whereIn('name', $definition['permissions'])
                    ->pluck('id', 'name');

                // A role referencing a permission that does not exist means the two catalogues
                // have drifted. Failing here beats seeding a role that quietly lacks access.
                $missing = array_diff($definition['permissions'], $permissionIds->keys()->all());

                if ($missing !== []) {
                    throw new RuntimeException(sprintf(
                        'Role [%s] references unknown permissions: %s. Add them to PermissionCatalogue.',
                        $definition['code'],
                        implode(', ', $missing),
                    ));
                }

                $role->permissions()->sync($permissionIds->values()->all());
            }

            $this->command->info(
                count(RoleCatalogue::all()).' system roles synced for '.$institution->name.'.'
            );
        });
    }

    private static function hierarchyLevel(string $code, string $family): int
    {
        return match ($code) {
            'vice-chancellor', 'vc-designee', 'system-admin' => 1,
            'dvc-academic', 'dvc-finance', 'dvc-research', 'registrar-academic', 'bursar', 'ict-security' => 2,
            'dean', 'campus-director', 'exam-officer', 'dean-of-students', 'librarian', 'election-commissioner', 'senate-member' => 3,
            'head-of-department', 'deputy-dean', 'deputy-registrar', 'graduate-school-admin' => 4,
            default => $family === Role::FAMILY_STUDENT_LIFECYCLE ? 10 : 6,
        };
    }

    private static function requiresMfa(string $code, string $family): bool
    {
        if ($family === Role::FAMILY_EXECUTIVE) {
            return true;
        }

        return in_array($code, [
            'system-admin', 'ict-security', 'registrar-academic', 'deputy-registrar',
            'admissions-officer', 'dean', 'deputy-dean', 'head-of-department',
            'exam-officer', 'exam-admin', 'exam-coordinator', 'exam-examiner',
            'marks-processor', 'results-officer', 'exam-board-secretary', 'bursar',
            'finance-officer', 'student-finance-accountant', 'payments-accountant',
            'budget-accountant', 'budget-officer', 'finance-examiner', 'cashier',
            'procurement-manager', 'tender-committee', 'librarian', 'election-commissioner',
            'returning-officer', 'pdc-coordinator',
        ], true);
    }

    private static function normalizedFamily(string $code): string
    {
        return match ($code) {
            'vice-chancellor', 'vc-designee', 'dvc-academic', 'dvc-finance', 'dvc-research',
            'dean', 'deputy-dean', 'campus-director' => Role::FAMILY_EXECUTIVE,
            'exam-officer', 'exam-admin', 'exam-coordinator', 'exam-examiner', 'marks-processor',
            'results-officer', 'exam-board-secretary' => Role::FAMILY_EXAMINATION,
            'bursar', 'finance-officer', 'student-finance-accountant', 'payments-accountant',
            'budget-accountant', 'budget-officer', 'finance-examiner', 'cashier' => Role::FAMILY_FINANCE,
            'procurement-manager', 'procurement-officer', 'tender-committee' => Role::FAMILY_PROCUREMENT,
            'student', 'applicant', 'graduate', 'alumni' => Role::FAMILY_STUDENT_LIFECYCLE,
            'dean-of-students', 'student-affairs-officer', 'accommodation-officer',
            'counselling-officer' => Role::FAMILY_STUDENT_AFFAIRS,
            'librarian', 'assistant-librarian' => Role::FAMILY_LIBRARY,
            'election-commissioner', 'returning-officer', 'senate-member', 'auditor',
            'hr-officer', 'content-editor' => Role::FAMILY_GOVERNANCE,
            'pdc-coordinator', 'trainer' => Role::FAMILY_CONTINUING_ED,
            'system-admin', 'ict-security', 'user-support' => Role::FAMILY_SYSTEM_ADMIN,
            default => Role::FAMILY_ACADEMIC_ADMIN,
        };
    }
}
