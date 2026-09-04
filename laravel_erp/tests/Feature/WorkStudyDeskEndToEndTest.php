<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkStudyAllocation;
use App\Models\WorkStudyApplication;
use App\Models\WorkStudyClaim;
use App\Models\WorkStudyPeriod;
use App\Models\WorkStudyPosition;
use App\Models\WorkStudyTimesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkStudyDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_work_study_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'student_affairs_officer');
        $this->actingAs($officer);

        $this->post(route('work-study.period-setup.store'), [
            'trimester' => 'Sep-Dec 2026',
            'academic_year' => '2026/2027',
            'total_budget' => 'KES 2,000,000',
            'committed_budget' => 'KES 500,000',
            'hourly_rate' => 'KES 200',
            'max_weekly_hours' => '20',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('work-study.positions.store'), [
            'job_code' => 'WS-LIB-01',
            'title' => 'Library Assistant',
            'department' => 'Library',
            'slots_available' => 5,
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('work-study.applications.store'), [
            'app_no' => 'WS-APP-01',
            'student_name' => 'E2E Scholar',
            'reg_no' => 'BCS/WS/2026',
            'preferred_role' => 'Library Assistant',
            'vetting_status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('work-study.allocations.store'), [
            'allocation_code' => 'WS-AL-01',
            'student_name' => 'E2E Scholar',
            'reg_no' => 'BCS/WS/2026',
            'assigned_position' => 'Library Assistant',
            'contract_status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('work-study.timesheets.store'), [
            'timesheet_no' => 'TS-01',
            'student_name' => 'E2E Scholar',
            'hours_claimed' => '16',
            'supervisor_status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('work-study.claims.store'), [
            'voucher_no' => 'VCH-01',
            'student_name' => 'E2E Scholar',
            'reg_no' => 'BCS/WS/2026',
            'gross_amount' => 'KES 3,200',
            'disbursement_mode' => 'M-Pesa',
            'disbursement_status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('work_study_periods', ['trimester' => 'Sep-Dec 2026']);
        $this->assertDatabaseHas('work_study_positions', ['job_code' => 'WS-LIB-01']);
        $this->assertDatabaseHas('work_study_claims', ['voucher_no' => 'VCH-01']);

        $this->get(route('work-study.period-setup'))->assertOk()->assertSee('Sep-Dec 2026');
        $this->get(route('work-study.positions'))->assertOk()->assertSee('Library Assistant');
        $this->get(route('work-study.claims'))->assertOk()->assertSee('VCH-01')->assertSee('M-Pesa');
    }

    public function test_work_study_screens_render_empty(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'student_affairs_officer');

        foreach ([
            'work-study.period-setup',
            'work-study.positions',
            'work-study.applications',
            'work-study.allocations',
            'work-study.timesheets',
            'work-study.claims',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk();
        }

        $this->assertSame(0, WorkStudyPeriod::query()->count());
        $this->assertSame(0, WorkStudyPosition::query()->count());
        $this->assertSame(0, WorkStudyApplication::query()->count());
        $this->assertSame(0, WorkStudyAllocation::query()->count());
        $this->assertSame(0, WorkStudyTimesheet::query()->count());
        $this->assertSame(0, WorkStudyClaim::query()->count());
    }

    public function test_staff_without_student_affairs_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('work-study.period-setup.store'), [
            'trimester' => 'Denied',
            'academic_year' => '2026/2027',
        ])->assertForbidden();
    }
}
