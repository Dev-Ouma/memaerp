<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SmhrAppraisal;
use App\Models\SmhrDisciplinaryRecord;
use App\Models\SmhrOnboardingCandidate;
use App\Models\SmhrPayrollItem;
use App\Models\SmhrPayrollVarianceReport;
use App\Models\SmhrWorkload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SmhrDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_smhr_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'hr_officer');
        $this->actingAs($officer);

        $this->post(route('smhr.workload-allocation.store'), [
            'name' => 'Dr Workload',
            'staff_id' => 'EMP24001',
            'dept' => 'CS',
            'units' => 'CSC101, CSC102',
            'teaching_hours' => 12,
            'supervision_hours' => 4,
            'admin_hours' => 2,
            'status' => 'OPTIMAL',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('smhr.performance-appraisals.store'), [
            'name' => 'Dr Appraisal',
            'staff_id' => 'EMP24001',
            'dept' => 'CS',
            'teaching_eval' => '4.5',
            'overall_score' => '88',
            'grade' => 'A',
            'status' => 'Completed',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('smhr.payroll-register.store'), [
            'name' => 'Dr Payroll',
            'staff_id' => 'EMP24001',
            'dept' => 'CS',
            'month' => 'September 2026',
            'basic_pay' => 120000,
            'allowances' => 20000,
            'gross' => 140000,
            'paye' => 25000,
            'statutory' => 5000,
            'net_pay' => 110000,
            'status' => 'Paid',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('smhr.reports.store'), [
            'month' => 'September 2026',
            'staff_count' => 1,
            'gross' => '140000',
            'paye' => '25000',
            'variance' => '0%',
            'reason' => 'Baseline',
            'amount' => 140000,
            'status' => 'Compliant',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('smhr.disciplinary-records.store'), [
            'staff_name' => 'Dr Disciplinary',
            'staff_id' => 'EMP24002',
            'dept' => 'CS',
            'category' => 'Conduct',
            'type' => 'Warning',
            'description' => 'Late marking',
            'action_taken' => 'Verbal warning',
            'date' => '2026-09-01',
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('smhr.onboarding.store'), [
            'name' => 'Dr Onboard',
            'email' => 'onboard@mema.ac.ke',
            'phone' => '+254711000001',
            'designation' => 'Lecturer',
            'department' => 'CS',
            'joining_date' => '2026-10-15',
        ])->assertRedirect(route('smhr.onboarding'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('smhr_workloads', ['name' => 'Dr Workload', 'total_hours' => 18]);
        $this->assertDatabaseHas('smhr_appraisals', ['name' => 'Dr Appraisal', 'status' => 'Completed']);
        $this->assertDatabaseHas('smhr_payroll_items', ['name' => 'Dr Payroll', 'net_pay' => 110000]);
        $this->assertDatabaseHas('smhr_payroll_variance_reports', ['month' => 'September 2026']);
        $this->assertDatabaseHas('smhr_disciplinary_records', ['staff_name' => 'Dr Disciplinary']);
        $this->assertDatabaseHas('smhr_onboarding_candidates', ['name' => 'Dr Onboard']);

        $this->get(route('smhr.workload-allocation'))->assertOk()->assertSee('Dr Workload');
        $this->get(route('smhr.performance-appraisals'))->assertOk()->assertSee('Dr Appraisal');
        $this->get(route('smhr.payroll-register'))->assertOk()->assertSee('Dr Payroll');
        $this->get(route('smhr.onboarding'))->assertOk()->assertSee('Dr Onboard');
    }

    public function test_smhr_screens_render_empty(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'hr_officer');

        foreach ([
            'smhr.workload-allocation',
            'smhr.performance-appraisals',
            'smhr.payroll-register',
            'smhr.reports',
            'smhr.disciplinary-records',
            'smhr.onboarding',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk();
        }

        $this->assertSame(0, SmhrWorkload::query()->count());
        $this->assertSame(0, SmhrAppraisal::query()->count());
        $this->assertSame(0, SmhrPayrollItem::query()->count());
        $this->assertSame(0, SmhrPayrollVarianceReport::query()->count());
        $this->assertSame(0, SmhrDisciplinaryRecord::query()->count());
        $this->assertSame(0, SmhrOnboardingCandidate::query()->count());
    }

    public function test_cohort_creation_persists_and_other_cohort_screens_are_empty(): void
    {
        $this->seedRbac();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        $this->post(route('cohort.cohort-creation.store'), [
            'cohort_code' => 'COH-E2E-2026',
            'cohort_name' => 'E2E Cohort',
            'academic_year' => '2026/2027',
            'intake_session' => 'September',
            'study_mode' => 'ODeL',
            'capacity' => 100,
            'enrolled' => 10,
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('institution_cohorts', ['cohort_code' => 'COH-E2E-2026']);
        $this->get(route('cohort.cohort-creation'))->assertOk()->assertSee('COH-E2E-2026');

        foreach ([
            'cohort.programme-cohort-mapping',
            'cohort.publish-finance',
            'cohort.cohort-transfer',
        ] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertDontSee('14850')
                ->assertDontSee('Victor Kipkorir')
                ->assertDontSee('Safaricom');
        }
    }

    public function test_staff_without_smhr_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('smhr.workload-allocation.store'), [
            'name' => 'Denied',
        ])->assertForbidden();

        $this->assertDatabaseMissing('smhr_workloads', ['name' => 'Denied']);
    }
}
