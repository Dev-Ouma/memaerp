<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SmhrModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->seedRbac();
    }

    public function test_authenticated_user_can_access_smhr_dashboard(): void
    {
        $staffUser = User::factory()->create([
            'name' => 'Database Staff Member',
            'role' => 'staff',
            'department' => 'Database Services',
            'is_active' => true,
        ]);
        $staffId = DB::table('staff')->insertGetId([
            'user_id' => $staffUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('staff_leave_requests')->insert([
            'staff_id' => $staffId,
            'leave_date' => now()->addDay()->toDateString(),
            'message' => 'Database-backed leave request',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('smhr.dashboard'));
        $response->assertOk();
        $response->assertSee('SMHR — Staff &amp; Human Resources', false);
        $response->assertSee('STAFF DIRECTORY &amp; PROFILES', false);
        $response->assertSee('Database Services');
        $response->assertSee('Database Staff Member');
        $response->assertDontSee('148 Total Staff');
    }

    public function test_staff_directory_view_and_registration(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.staff-directory'));
        $response->assertOk();
        $response->assertSee('Staff Directory &amp; Profiles', false);
        $response->assertDontSee('Prof. Allan Wabwire');

        // Test staff creation
        $createResponse = $this->actingAs($this->admin)->post(route('smhr.staff-directory.store'), [
            'name' => 'Dr. Jane Mwangi',
            'email' => 'j.mwangi@mema.ac.ke',
            'phone' => '+254 711 222 333',
            'designation' => 'Senior Lecturer in Software Eng.',
            'department' => 'Computer Science',
            'employment_type' => 'Permanent / Tenured',
            'rank' => 'Senior Lecturer',
            'qualification' => 'PhD in Computer Systems (UoN)',
        ]);

        $createResponse->assertRedirect(route('smhr.staff-directory'));
        $createResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'j.mwangi@mema.ac.ke']);
        $this->actingAs($this->admin)->get(route('smhr.staff-directory'))->assertSee('Dr. Jane Mwangi');
    }

    public function test_leave_management_and_approval_workflow(): void
    {
        $staffUser = User::factory()->create([
            'name' => 'Dr Emmanuel Leave',
            'role' => 'staff',
            'is_active' => true,
        ]);
        $staffId = DB::table('staff')->insertGetId([
            'user_id' => $staffUser->id,
            'staff_no' => 'EMP-LEAVE-1',
            'department' => 'Information Technology',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('smhr.leave-management'));
        $response->assertOk();
        $response->assertSee('Leave Management &amp; Approvals', false);

        $submitResponse = $this->actingAs($this->admin)->post(route('smhr.leave-management.store'), [
            'staff_name' => 'Dr Emmanuel Leave',
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-15',
            'reason' => 'Annual rest break.',
            'reliever' => 'Dr Mercy Cover',
        ]);
        $submitResponse->assertRedirect(route('smhr.leave-management'));
        $submitResponse->assertSessionHas('success');
        $this->assertDatabaseHas('staff_leave_requests', [
            'staff_id' => $staffId,
            'status' => 'pending',
        ]);

        $leaveId = (string) DB::table('staff_leave_requests')->where('staff_id', $staffId)->value('id');
        $approveResponse = $this->actingAs($this->admin)->post(route('smhr.leave-management.approve', $leaveId));
        $approveResponse->assertRedirect(route('smhr.leave-management'));
        $approveResponse->assertSessionHas('success');
        $this->assertDatabaseHas('staff_leave_requests', ['id' => $leaveId, 'status' => 'approved']);
    }

    public function test_workload_allocation_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.workload-allocation'));
        $response->assertOk();
        $response->assertSee('Teaching Workload &amp; Faculty Allocation', false);
    }

    public function test_performance_appraisals_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.performance-appraisals'));
        $response->assertOk();
        $response->assertSee('Staff Performance Appraisals &amp; KPIs', false);
    }

    public function test_payroll_register_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.payroll-register'));
        $response->assertOk();
        $response->assertSee('Payroll &amp; Statutory Compensation Register', false);
        $response->assertSee('Disbursement batch reference');
    }

    public function test_disciplinary_records_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.disciplinary-records'));
        $response->assertOk();
        $response->assertSee('Disciplinary &amp; HR Governance Ledger', false);
    }

    public function test_payslip_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.payslip'));
        $response->assertOk();
        $response->assertSee('Employee Pay Advice / Payslip', false);
        $response->assertSee('TOTAL GROSS EARNINGS');
        $response->assertSee('NET PAYABLE SALARY');
        $response->assertDontSee('Prof. Allan Wabwire');
        $response->assertDontSee('Dr. Mercy Chebet');
    }

    public function test_kra_p9_form_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.p9-form'));
        $response->assertOk();
        $response->assertDontSee('Prof. Allan Wabwire');
    }

    public function test_smhr_reports_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.reports'));
        $response->assertOk();
        $response->assertSee('SMHR Reports &amp; Statutory Returns', false);
    }

    public function test_staff_onboarding_pipeline_and_initiation(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.onboarding'));
        $response->assertOk();
        $response->assertSee('Staff Onboarding &amp; Induction Pipeline', false);
        $response->assertDontSee('Dr. Mercy Chebet');

        $storeResponse = $this->actingAs($this->admin)->post(route('smhr.onboarding.store'), [
            'name' => 'Dr Jane Mwangi',
            'email' => 'j.mwangi@mema.ac.ke',
            'phone' => '+254700000001',
            'designation' => 'Senior Lecturer',
            'department' => 'Computer Science',
            'joining_date' => '2026-10-01',
        ]);

        $storeResponse->assertRedirect(route('smhr.onboarding'));
        $storeResponse->assertSessionHas('success');
        $this->assertDatabaseHas('smhr_onboarding_candidates', [
            'name' => 'Dr Jane Mwangi',
            'department' => 'Computer Science',
            'status' => 'In Progress',
        ]);
    }
}
