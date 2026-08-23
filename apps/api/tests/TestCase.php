<?php

declare(strict_types=1);

namespace Tests;

use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Platform\Support\Scope;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected Institution $institution;

    /**
     * Seeds the reference data every test needs: the institution and its structure, the
     * permission catalogue, and the system roles.
     *
     * Called explicitly by tests that need it rather than in setUp(), so that tests which do not
     * touch authorization stay fast.
     */
    protected function seedReferenceData(): void
    {
        $this->seed(InstitutionSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->institution = Institution::query()->where('code', 'MEMA')->firstOrFail();
    }

    /**
     * A user holding one role at one scope — the shape every authorization test needs.
     */
    protected function userWithRole(string $roleCode, Scope $scope, ?string $email = null): User
    {
        $email ??= 'user-'.uniqid().'@mema.ac.ke';

        $person = Person::query()->create([
            'institution_id' => $this->institution->id,
            'given_name' => 'Test',
            'family_name' => 'User',
            'primary_email' => $email,
        ]);

        $user = User::query()->create([
            'institution_id' => $this->institution->id,
            'person_id' => $person->id,
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $role = Role::query()
            ->where('institution_id', $this->institution->id)
            ->where('code', $roleCode)
            ->firstOrFail();

        RoleAssignment::query()->create([
            'institution_id' => $this->institution->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => $scope->type,
            'scope_id' => $scope->id,
            'grant_reason' => 'Test fixture',
        ]);

        return $user->fresh();
    }

    /** A user with a login but no roles at all — the baseline every deny test needs. */
    protected function userWithNoRoles(): User
    {
        $email = 'norole-'.uniqid().'@mema.ac.ke';

        $person = Person::query()->create([
            'institution_id' => $this->institution->id,
            'given_name' => 'No',
            'family_name' => 'Roles',
            'primary_email' => $email,
        ]);

        return User::query()->create([
            'institution_id' => $this->institution->id,
            'person_id' => $person->id,
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }
}
