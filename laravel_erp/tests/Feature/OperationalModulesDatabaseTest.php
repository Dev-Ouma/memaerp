<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OperationalModulesDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->seedRbac();
    }

    public function test_former_mock_modules_render_from_empty_database(): void
    {
        foreach ([
            'fees.payment-accounts',
            'graduation.criteria',
            'lms.course-shells',
            'imprest.permissions',
            'work-study.period-setup',
            'transfers.exemptions',
            'transfers.dates-setup',
            'service-providers.providers',
            'reports.advanced-analytics',
            'smhr.workload-allocation',
            'smhr.onboarding',
            'cohort.cohort-creation',
            'cohort.programme-cohort-mapping',
        ] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }

        $this->actingAs($this->admin)->get(route('transfers.exemptions'))
            ->assertOk()
            ->assertDontSee('DANIEL KIBET')
            ->assertDontSee('1,683');
        $this->actingAs($this->admin)->get(route('service-providers.providers'))
            ->assertOk()
            ->assertDontSee('Safaricom PLC');
        $this->actingAs($this->admin)->get(route('reports.advanced-analytics'))
            ->assertOk()
            ->assertDontSee('14,850');
        $this->actingAs($this->admin)->get(route('cohort.cohort-creation'))
            ->assertOk()
            ->assertDontSee('COH-2026-SEP-MAIN');
        $this->actingAs($this->admin)->get(route('smhr.onboarding'))
            ->assertOk()
            ->assertDontSee('Dr. Mercy Chebet');
    }

    public function test_operational_record_can_be_created_and_listed(): void
    {
        $this->actingAs($this->admin)->post(route('fees.accounts.store'), [
            'code' => 'ACC-DB-001',
            'name' => 'Equity Collection Account',
            'bank_name' => 'Equity Bank',
            'account_number' => '0123456789',
            'integration_type' => 'Bank IPN',
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $this->assertDatabaseHas('fee_payment_accounts', [
            'code' => 'ACC-DB-001',
            'name' => 'Equity Collection Account',
        ]);

        $this->actingAs($this->admin)->get(route('fees.payment-accounts'))
            ->assertOk()
            ->assertSee('Equity Collection Account')
            ->assertSee('ACC-DB-001');
    }

    public function test_smhr_staff_and_leave_persist_to_database(): void
    {

        $this->actingAs($this->admin)->post(route('smhr.staff-directory.store'), [
            'name' => 'Dr Jane Database',
            'email' => 'jane.database@mema.ac.ke',
            'phone' => '+254711000111',
            'designation' => 'Senior Lecturer',
            'department' => 'Computer Science',
            'employment_type' => 'Permanent',
            'rank' => 'Senior Lecturer',
            'qualification' => 'PhD Computing',
        ])->assertRedirect(route('smhr.staff-directory'));

        $this->assertDatabaseHas('users', ['email' => 'jane.database@mema.ac.ke', 'role' => 'staff']);
        $this->assertTrue(Staff::query()->whereHas('user', fn ($q) => $q->where('email', 'jane.database@mema.ac.ke'))->exists());

        $this->actingAs($this->admin)->get(route('smhr.staff-directory'))
            ->assertOk()
            ->assertSee('Dr Jane Database')
            ->assertDontSee('Prof. Allan Wabwire');

        $this->actingAs($this->admin)->post(route('smhr.leave-management.store'), [
            'staff_name' => 'Dr Jane Database',
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'reason' => 'Annual rest break recorded in database.',
            'reliever' => 'Acting HOD',
        ])->assertRedirect(route('smhr.leave-management'));

        $leave = StaffLeaveRequest::query()->latest('id')->first();
        $this->assertNotNull($leave);
        $this->assertSame('pending', $leave->status);

        $this->actingAs($this->admin)->post(route('smhr.leave-management.approve', $leave->id))
            ->assertRedirect(route('smhr.leave-management'));
        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_graduation_criteria_persist_to_domain_table(): void
    {

        $this->actingAs($this->admin)->post(route('graduation.criteria.store'), [
            'programme' => 'BSc Computer Science',
            'min_credits' => '120',
            'min_cgpa' => '2.00',
            'thesis_required' => 'No',
            'clearance_nodes' => 'Finance, Library',
            'status' => 'Active',
        ])->assertRedirect();

        $this->assertDatabaseHas('graduation_criteria', [
            'programme' => 'BSc Computer Science',
            'min_credits' => '120',
        ]);

        $this->actingAs($this->admin)->get(route('graduation.criteria'))
            ->assertOk()
            ->assertSee('BSc Computer Science');
    }
}
