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
}
