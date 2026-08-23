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
                $role = Role::query()->updateOrCreate(
                    [
                        'institution_id' => $institution->getKey(),
                        'code' => $definition['code'],
                    ],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'family' => $definition['family'],
                        'is_system' => true,
                        'hierarchy_level' => self::hierarchyLevel($definition['code'], $definition['family']),
                        'is_mfa_mandatory' => self::requiresMfa($definition['code'], $definition['family']),
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
            default => $family === Role::FAMILY_STUDENT ? 10 : 6,
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
}
