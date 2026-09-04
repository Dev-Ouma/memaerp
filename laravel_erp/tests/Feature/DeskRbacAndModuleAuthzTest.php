<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Platform\Rbac\AccessControl;
use Database\Seeders\StakeholderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeskRbacAndModuleAuthzTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_desk_accounts_receive_catalogue_roles(): void
    {
        $this->seed(StakeholderSeeder::class);
        $this->seedRbac();

        $access = app(AccessControl::class);

        $this->assertTrue($access->allows(
            User::where('email', 'registrar@mema.ac.ke')->firstOrFail(),
            'admission.decision.final',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'admissions.officer@mema.ac.ke')->firstOrFail(),
            'admission.document.verify',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'finance.officer@mema.ac.ke')->firstOrFail(),
            'admission.payment.waive',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'curriculum.manager@mema.ac.ke')->firstOrFail(),
            'curriculum.manage',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'hr.officer@mema.ac.ke')->firstOrFail(),
            'smhr.leave.approve',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'finance.officer@mema.ac.ke')->firstOrFail(),
            'fees.manage',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'registration.officer@mema.ac.ke')->firstOrFail(),
            'registration.manage',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'transfers.officer@mema.ac.ke')->firstOrFail(),
            'transfers.manage',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'lms.manager@mema.ac.ke')->firstOrFail(),
            'lms.manage',
        ));
        $this->assertTrue($access->allows(
            User::where('email', 'graduation.officer@mema.ac.ke')->firstOrFail(),
            'graduation.manage',
        ));

        $admin = User::where('email', 'admin@mema.ac.ke')->firstOrFail();
        $this->assertTrue($access->allows($admin, 'curriculum.manage'));
        $this->assertTrue($access->allows($admin, 'fees.manage'));
        $this->assertFalse($access->allows($admin, 'admission.decision.final'));
    }

    public function test_staff_without_curriculum_grant_cannot_create_school(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('curriculum.school.store'), [
            'code' => 'SCH-X',
            'name' => 'Unauthorized School',
            'status' => 'Active',
        ])->assertForbidden();
    }

    public function test_staff_without_hr_grant_cannot_create_staff_member(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('smhr.staff-directory.store'), [
            'name' => 'Unauthorized Hire',
            'email' => 'unauthorized.hire@mema.ac.ke',
            'phone' => '+254 700 000 001',
            'designation' => 'Lecturer',
            'department' => 'Computing',
            'employment_type' => 'Permanent / Tenured',
            'rank' => 'Lecturer',
            'qualification' => 'MSc',
        ])->assertForbidden();
    }

    public function test_staff_without_fees_grant_cannot_write_operational_fees(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('fees.accounts.store'), [
            'code' => 'ACC-X',
            'name' => 'Unauthorized Account',
            'status' => 'ACTIVE',
        ])->assertForbidden();
    }

    public function test_fees_officer_can_write_operational_fees(): void
    {
        $officer = $this->admissionOfficer('finance_officer');

        $this->actingAs($officer)->post(route('fees.accounts.store'), [
            'code' => 'ACC-FIN-1',
            'name' => 'Finance Desk Account',
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $this->assertDatabaseHas('fee_payment_accounts', ['code' => 'ACC-FIN-1']);
    }

    public function test_curriculum_manager_can_create_school(): void
    {
        $manager = $this->admissionOfficer('curriculum_manager');

        $this->actingAs($manager)->post(route('curriculum.school.store'), [
            'code' => 'SCH-CUR',
            'name' => 'Curriculum Desk School',
            'status' => 'Active',
        ])->assertRedirect(route('curriculum.school'));

        $this->assertDatabaseHas('schools', ['code' => 'SCH-CUR']);
    }
}
