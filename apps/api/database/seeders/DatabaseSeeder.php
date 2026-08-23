<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use Illuminate\Database\Seeder;

/**
 * Reference data every environment needs, including production.
 *
 * Order matters and is not arbitrary: permissions exist before roles can hold them, and an
 * institution exists before roles (which are institution-scoped) can be created for it.
 *
 * Everything called here is idempotent — this runs on every deploy, not just on a fresh
 * database. Demo and test fixtures do NOT belong here; those live in factories.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InstitutionSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
