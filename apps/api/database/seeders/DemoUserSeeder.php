<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\PersonIdentity;
use App\Platform\Support\Scope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * DEMONSTRATION ACCOUNTS — not production data.
 *
 * Every account here is granted at a DIFFERENT scope on purpose, because that is the only way to
 * see the authorization model actually working. A Dean of Science and a Head of Computer Science
 * both hold `examination.marks.view`; the Dean sees four departments and the HOD sees one, and
 * neither of them sees Nursing. If every demo account were institution-scoped, the entire scope
 * dimension would look like dead code.
 *
 * Passwords are weak and identical by design — this seeder must never run in production, which
 * is why it is not called from {@see DatabaseSeeder}.
 */
final class DemoUserSeeder extends Seeder
{
    private const string PASSWORD = 'password123';

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoUserSeeder must never run in production.');
        }

        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();

        $science = Faculty::query()->where('institution_id', $institution->id)
            ->where('code', 'FSCI')->firstOrFail();
        $computerScience = Department::query()->where('institution_id', $institution->id)
            ->where('code', 'CS')->firstOrFail();

        $accounts = [
            [
                'email' => 'admin@mema.ac.ke',
                'username' => 'admin',
                'given_name' => 'System',
                'family_name' => 'Administrator',
                'staff_no' => 'EMP-000001',
                'role' => 'system-admin',
                'scope' => Scope::institution(),
            ],
            [
                'email' => 'auditor@mema.ac.ke',
                'username' => 'auditor',
                'given_name' => 'Grace',
                'family_name' => 'Wanjiru',
                'staff_no' => 'EMP-000002',
                'role' => 'auditor',
                'scope' => Scope::institution(),
            ],
            [
                'email' => 'registrar@mema.ac.ke',
                'username' => 'registrar',
                'given_name' => 'Mike',
                'family_name' => 'Wabwire',
                'staff_no' => 'EMP-000003',
                'role' => 'registrar-academic',
                'scope' => Scope::institution(),
            ],
            [
                'email' => 'dean.science@mema.ac.ke',
                'username' => 'dean.science',
                'given_name' => 'Achieng',
                'family_name' => 'Odhiambo',
                'staff_no' => 'EMP-000004',
                'role' => 'dean',
                // Faculty scope: reaches every department under Science, and nothing outside it.
                'scope' => Scope::faculty($science->id),
            ],
            [
                'email' => 'hod.cs@mema.ac.ke',
                'username' => 'hod.cs',
                'given_name' => 'Peter',
                'family_name' => 'Kariuki',
                'staff_no' => 'EMP-000005',
                'role' => 'head-of-department',
                // Department scope: Computer Science only — not the rest of the faculty.
                'scope' => Scope::department($computerScience->id),
            ],
            [
                'email' => 'lecturer@mema.ac.ke',
                'username' => 'lecturer',
                'given_name' => 'Miriam',
                'family_name' => 'Chebet',
                'staff_no' => 'EMP-000006',
                'role' => 'lecturer',
                // Self scope: only their own assigned offerings.
                'scope' => Scope::self(),
            ],
            [
                'email' => 'bursar@mema.ac.ke',
                'username' => 'bursar',
                'given_name' => 'Joseph',
                'family_name' => 'Otieno',
                'staff_no' => 'EMP-000007',
                'role' => 'bursar',
                'scope' => Scope::institution(),
            ],
            [
                'email' => 'finance@mema.ac.ke',
                'username' => 'finance',
                'given_name' => 'Faith',
                'family_name' => 'Nduta',
                'staff_no' => 'EMP-000008',
                'role' => 'finance-officer',
                'scope' => Scope::institution(),
            ],
            [
                'email' => 'senate@mema.ac.ke',
                'username' => 'senate',
                'given_name' => 'Senate',
                'family_name' => 'Secretariat',
                'staff_no' => 'EMP-000009',
                'role' => 'senate-member',
                'scope' => Scope::institution(),
            ],
        ];

        foreach ($accounts as $account) {
            $this->createAccount($institution, $account);
        }

        $this->command->info(count($accounts).' demo accounts seeded (password: '.self::PASSWORD.').');
    }

    /** @param array{email: string, username: string, given_name: string, family_name: string, staff_no: string, role: string, scope: Scope} $account */
    private function createAccount(Institution $institution, array $account): void
    {
        $person = Person::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'primary_email' => $account['email'],
            ],
            [
                'given_name' => $account['given_name'],
                'family_name' => $account['family_name'],
                'nationality' => 'KE',
            ],
        );

        PersonIdentity::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'identity_type' => PersonIdentity::TYPE_EMPLOYEE,
                'identifier' => $account['staff_no'],
            ],
            [
                'person_id' => $person->id,
                'status' => PersonIdentity::STATUS_ACTIVE,
                'started_on' => CarbonImmutable::parse('2024-01-01'),
            ],
        );

        $user = User::query()->updateOrCreate(
            [
                'institution_id' => $institution->id,
                'email' => $account['email'],
            ],
            [
                'person_id' => $person->id,
                'username' => $account['username'],
                'password' => Hash::make(self::PASSWORD),
                'is_active' => true,
                'must_change_password' => false,
                'email_verified_at' => CarbonImmutable::now(),
            ],
        );

        $role = Role::query()
            ->where('institution_id', $institution->id)
            ->where('code', $account['role'])
            ->firstOrFail();

        $scope = $account['scope'];

        RoleAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'scope_type' => $scope->type,
                'scope_id' => $scope->id,
            ],
            [
                'institution_id' => $institution->id,
                'grant_reason' => 'Demonstration account seeded for local development',
                'starts_at' => CarbonImmutable::now(),
            ],
        );
    }
}
