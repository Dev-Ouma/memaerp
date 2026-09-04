<?php

namespace Tests;

use App\Models\User;
use App\Modules\Platform\Rbac\AccessControl;
use Database\Seeders\RbacCatalogueSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function seedRbac(): void
    {
        $this->seed(RbacCatalogueSeeder::class);
    }

    protected function grantRole(User $user, string $roleCode, ?User $grantedBy = null): string
    {
        $id = (string) Str::uuid();
        DB::table('user_roles')->insert([
            'id' => $id, 'user_id' => $user->id,
            'role_id' => DB::table('roles')->where('code', $roleCode)->value('id'),
            'scope_type' => 'institution', 'scope_id' => null, 'granted_by' => $grantedBy?->id,
            'granted_at' => now(), 'grant_reason' => 'Test authorization assignment.',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        app(AccessControl::class)->forget($user);

        return $id;
    }

    /** Staff actor with a catalogue role for admission mutations (not legacy admin alone). */
    protected function admissionOfficer(string $rbacRole = 'registrar'): User
    {
        $this->seedRbac();
        $user = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($user, $rbacRole);

        return $user;
    }
}
