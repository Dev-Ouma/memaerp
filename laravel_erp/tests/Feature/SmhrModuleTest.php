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
        ]);
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
        $response->assertSee('Prof. Allan Wabwire');

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
    }

    public function test_leave_management_and_approval_workflow(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.leave-management'));
        $response->assertOk();
        $response->assertSee('Leave Management &amp; Approvals', false);

        // Submit leave
        $submitResponse = $this->actingAs($this->admin)->post(route('smhr.leave-management.store'), [
            'staff_name' => 'Dr. Emmanuel Mutua',
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-15',
            'reason' => 'Annual rest break.',
            'reliever' => 'Dr. Mercy Chebet',
        ]);
        $submitResponse->assertRedirect(route('smhr.leave-management'));
        $submitResponse->assertSessionHas('success');

        // Approve leave
        $approveResponse = $this->actingAs($this->admin)->post(route('smhr.leave-management.approve', 'LV-2026-101'));
        $approveResponse->assertRedirect(route('smhr.leave-management'));
        $approveResponse->assertSessionHas('success');
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
        $response->assertSee('Prof. Allan Wabwire');
        $response->assertSee('TOTAL GROSS EARNINGS');
        $response->assertSee('NET PAYABLE SALARY');

        // Test dynamic staff payslip switching
        $drChebetResponse = $this->actingAs($this->admin)->get(route('smhr.payslip', ['staff_id' => 'EMP-2026-014', 'month' => 'July', 'year' => '2026']));
        $drChebetResponse->assertOk();
        $drChebetResponse->assertSee('Dr. Mercy Chebet');
        $drChebetResponse->assertSee('JULY 2026');
    }

    public function test_kra_p9_form_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.p9-form', ['staffId' => 'EMP-2026-001', 'year' => '2025']));
        $response->assertOk();
        $response->assertSee('KRA Form P9A — Tax Deduction Card', false);
        $response->assertSee('TAX DEDUCTION CARD (FORM P9A) — YEAR 2025');
        $response->assertSee('MEMA UNIVERSITY COLLEGE');
        $response->assertSee('TOTALS (KES)');
    }

    public function test_smhr_reports_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.reports'));
        $response->assertOk();
        $response->assertSee('SMHR Reports &amp; Statutory Returns', false);
        $response->assertSee('Monthly Payroll Variance Ledger');
        $response->assertSee('Statutory Remittances');
    }

    public function test_staff_onboarding_pipeline_and_initiation(): void
    {
        $response = $this->actingAs($this->admin)->get(route('smhr.onboarding'));
        $response->assertOk();
        $response->assertSee('Staff Onboarding &amp; Induction Pipeline', false);
        $response->assertSee('Dr. Mercy Chebet');

        // Initiate new onboarding
        $storeResponse = $this->actingAs($this->admin)->post(route('smhr.onboarding.store'), [
            'name' => 'Dr. Jane Mwangi',
            'email' => 'j.mwangi@mema.ac.ke',
            'phone' => '+254 711 333 444',
            'designation' => 'Senior Lecturer',
            'department' => 'Computer Science',
            'joining_date' => '2026-10-01',
        ]);

        $storeResponse->assertRedirect(route('smhr.onboarding'));
        $storeResponse->assertSessionHas('success');
    }
}
