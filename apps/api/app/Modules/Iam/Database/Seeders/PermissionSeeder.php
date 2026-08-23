<?php

declare(strict_types=1);

namespace App\Modules\Iam\Database\Seeders;

use App\Modules\Iam\Models\Permission;
use App\Modules\Iam\Support\PermissionCatalogue;
use App\Platform\Support\Uuid7;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Syncs the permission catalogue into the database. Safe to re-run on every deploy — that is the
 * point. New capabilities appear; descriptions update in place; nothing is orphaned.
 *
 * Permissions REMOVED from the catalogue are reported but not deleted. Deleting one cascades
 * through `permission_role` and would silently strip access from live roles during a deploy; a
 * human should decide that.
 */
final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = PermissionCatalogue::all();
        $now = now();

        $rows = array_map(fn (array $permission): array => [
            'id' => Uuid7::generate(),
            ...$permission,
            'created_at' => $now,
            'updated_at' => $now,
        ], $catalogue);

        DB::table('iam.permissions')->upsert(
            $rows,
            uniqueBy: ['name'],
            update: ['module', 'resource', 'action', 'description', 'is_sensitive', 'updated_at'],
        );

        $catalogueNames = array_column($catalogue, 'name');
        $orphans = Permission::query()->whereNotIn('name', $catalogueNames)->pluck('name');

        if ($orphans->isNotEmpty()) {
            $this->command->warn(
                'Permissions in the database but not in the catalogue (left in place; remove deliberately): '
                .$orphans->implode(', '),
            );
        }

        $this->command->info(count($rows).' permissions synced.');
    }
}
