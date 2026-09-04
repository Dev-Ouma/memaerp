<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\WorkStudyAllocation;
use App\Models\WorkStudyApplication;
use App\Models\WorkStudyClaim;
use App\Models\WorkStudyPeriod;
use App\Models\WorkStudyPosition;
use App\Models\WorkStudyTimesheet;
use App\Support\SoftStatsBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkStudyController extends Controller
{
    use AuthorizesCataloguePermission;

    public function periodSetup(Request $request): View
    {
        $records = WorkStudyPeriod::query()->latest()->get();
        $periods = $records->map(fn (WorkStudyPeriod $row): array => [
            'trimester' => $row->trimester,
            'academic_year' => $row->academic_year,
            'application_start' => $row->application_start ?? '—',
            'application_deadline' => $row->application_deadline ?? '—',
            'total_budget' => $row->total_budget ?? '—',
            'committed_budget' => $row->committed_budget ?? '—',
            'hourly_rate' => $row->hourly_rate ?? '—',
            'max_weekly_hours' => $row->max_weekly_hours ?? '—',
            'target_beneficiaries' => $row->target_beneficiaries ?? '—',
            'status' => $row->status,
        ])->all();
        $active = $records->first(fn (WorkStudyPeriod $r): bool => str_contains(strtolower($r->status), 'active'));
        $stats = new SoftStatsBag([
            'activeSession' => $active?->trimester ?? '—',
            'allocatedBudget' => $active?->committed_budget ?? ($records->pluck('committed_budget')->filter()->first() ?? '—'),
            'hourlyRate' => $active?->hourly_rate ?? ($records->pluck('hourly_rate')->filter()->first() ?? '—'),
            'maxHoursPerWeek' => $active?->max_weekly_hours ?? ($records->pluck('max_weekly_hours')->filter()->first() ?? '—'),
        ]);

        return view('work-study.period-setup', compact('periods', 'stats'))->with(
            'operationalCreate',
            $this->form('Add work-study period', 'Persists to work_study_periods.', 'work-study.period-setup.store', [
                ['name' => 'trimester', 'label' => 'Trimester', 'required' => true],
                ['name' => 'academic_year', 'label' => 'Academic year', 'required' => true],
                ['name' => 'application_start', 'label' => 'Application start'],
                ['name' => 'application_deadline', 'label' => 'Application deadline'],
                ['name' => 'total_budget', 'label' => 'Total budget'],
                ['name' => 'committed_budget', 'label' => 'Committed budget'],
                ['name' => 'hourly_rate', 'label' => 'Hourly rate'],
                ['name' => 'max_weekly_hours', 'label' => 'Max weekly hours'],
                ['name' => 'target_beneficiaries', 'label' => 'Target beneficiaries'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePeriodSetup(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyPeriod::class, [
            'trimester' => ['required', 'string', 'max:80'],
            'academic_year' => ['required', 'string', 'max:20'],
            'application_start' => ['nullable', 'string', 'max:40'],
            'application_deadline' => ['nullable', 'string', 'max:40'],
            'total_budget' => ['nullable', 'string', 'max:80'],
            'committed_budget' => ['nullable', 'string', 'max:80'],
            'hourly_rate' => ['nullable', 'string', 'max:40'],
            'max_weekly_hours' => ['nullable', 'string', 'max:40'],
            'target_beneficiaries' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Work-study period saved.');
    }

    public function positions(Request $request): View
    {
        $records = WorkStudyPosition::query()->latest()->get();
        $positions = $records->map(fn (WorkStudyPosition $row): array => [
            'job_code' => $row->job_code,
            'title' => $row->title,
            'department' => $row->department ?? '—',
            'supervisor' => $row->supervisor ?? '—',
            'hours_per_week' => $row->hours_per_week ?? '—',
            'skills_required' => $row->skills_required ?? '—',
            'slots_available' => (string) $row->slots_available,
            'slots_filled' => (string) $row->slots_filled,
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalOpenSlots' => (int) $records->sum(fn (WorkStudyPosition $r): int => max(0, (int) $r->slots_available - (int) $r->slots_filled)),
            'approvedRequisitions' => $records->filter(fn (WorkStudyPosition $r): bool => str_contains(strtolower($r->status), 'open') || str_contains(strtolower($r->status), 'approv'))->count(),
            'applicationsReceived' => WorkStudyApplication::query()->count(),
            'participatingDepts' => $records->pluck('department')->filter()->unique()->count(),
        ]);

        return view('work-study.positions', compact('positions', 'stats'))->with(
            'operationalCreate',
            $this->form('Add position', 'Persists to work_study_positions.', 'work-study.positions.store', [
                ['name' => 'job_code', 'label' => 'Job code', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'supervisor', 'label' => 'Supervisor'],
                ['name' => 'hours_per_week', 'label' => 'Hours / week'],
                ['name' => 'skills_required', 'label' => 'Skills required'],
                ['name' => 'slots_available', 'label' => 'Slots available', 'type' => 'number'],
                ['name' => 'slots_filled', 'label' => 'Slots filled', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePositions(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyPosition::class, [
            'job_code' => ['required', 'string', 'max:80', 'unique:work_study_positions,job_code'],
            'title' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'supervisor' => ['nullable', 'string', 'max:190'],
            'hours_per_week' => ['nullable', 'string', 'max:40'],
            'skills_required' => ['nullable', 'string', 'max:255'],
            'slots_available' => ['nullable', 'integer', 'min:0'],
            'slots_filled' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Open', 'slots_available' => 0, 'slots_filled' => 0], 'Position saved.');
    }

    public function applications(Request $request): View
    {
        $records = WorkStudyApplication::query()->latest()->get();
        $applications = $records->map(fn (WorkStudyApplication $row): array => [
            'app_no' => $row->app_no,
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'programme' => $row->programme ?? '—',
            'preferred_role' => $row->preferred_role ?? '—',
            'current_gpa' => $row->current_gpa ?? '—',
            'need_category' => $row->need_category ?? '—',
            'fee_arrears' => $row->fee_arrears ?? '—',
            'socio_economic_score' => $row->socio_economic_score ?? '—',
            'vetting_status' => $row->vetting_status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalApplicants' => $records->count(),
            'pendingVetting' => $records->filter(fn (WorkStudyApplication $r): bool => str_contains(strtolower($r->vetting_status), 'pending'))->count(),
            'vettedEligible' => $records->filter(fn (WorkStudyApplication $r): bool => str_contains(strtolower($r->vetting_status), 'eligib') || str_contains(strtolower($r->vetting_status), 'approv') || str_contains(strtolower($r->vetting_status), 'short'))->count(),
            'rejectedCriteria' => $records->filter(fn (WorkStudyApplication $r): bool => str_contains(strtolower($r->vetting_status), 'reject'))->count(),
        ]);

        return view('work-study.applications', compact('applications', 'stats'))->with(
            'operationalCreate',
            $this->form('Add application', 'Persists to work_study_applications.', 'work-study.applications.store', [
                ['name' => 'app_no', 'label' => 'Application number', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'programme', 'label' => 'Programme'],
                ['name' => 'preferred_role', 'label' => 'Preferred role'],
                ['name' => 'current_gpa', 'label' => 'GPA'],
                ['name' => 'need_category', 'label' => 'Need category'],
                ['name' => 'fee_arrears', 'label' => 'Fee arrears'],
                ['name' => 'socio_economic_score', 'label' => 'Socio-economic score'],
                ['name' => 'vetting_status', 'label' => 'Vetting status'],
            ]),
        );
    }

    public function storeApplications(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyApplication::class, [
            'app_no' => ['required', 'string', 'max:80', 'unique:work_study_applications,app_no'],
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'programme' => ['nullable', 'string', 'max:190'],
            'preferred_role' => ['nullable', 'string', 'max:190'],
            'current_gpa' => ['nullable', 'string', 'max:40'],
            'need_category' => ['nullable', 'string', 'max:80'],
            'fee_arrears' => ['nullable', 'string', 'max:80'],
            'socio_economic_score' => ['nullable', 'string', 'max:40'],
            'vetting_status' => ['nullable', 'string', 'max:40'],
        ], ['vetting_status' => 'Pending'], 'Application saved.');
    }

    public function allocations(Request $request): View
    {
        $records = WorkStudyAllocation::query()->latest()->get();
        $allocations = $records->map(fn (WorkStudyAllocation $row): array => [
            'allocation_code' => $row->allocation_code,
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'assigned_position' => $row->assigned_position ?? '—',
            'department' => $row->department ?? '—',
            'supervisor' => $row->supervisor ?? '—',
            'approved_weekly_hours' => $row->approved_weekly_hours ?? '—',
            'start_date' => $row->start_date ?? '—',
            'end_date' => $row->end_date ?? '—',
            'contract_status' => $row->contract_status,
        ])->all();
        $stats = new SoftStatsBag([
            'activePlacements' => $records->filter(fn (WorkStudyAllocation $r): bool => str_contains(strtolower($r->contract_status), 'active'))->count(),
            'averageHoursPerWeek' => $records->count() ? 'Tracked' : '—',
            'deptsHosting' => $records->pluck('department')->filter()->unique()->count(),
            'monthlyStipendVolume' => $records->count().' placements',
        ]);

        return view('work-study.allocations', compact('allocations', 'stats'))->with(
            'operationalCreate',
            $this->form('Add allocation', 'Persists to work_study_allocations.', 'work-study.allocations.store', [
                ['name' => 'allocation_code', 'label' => 'Allocation code', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'assigned_position', 'label' => 'Position'],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'supervisor', 'label' => 'Supervisor'],
                ['name' => 'approved_weekly_hours', 'label' => 'Weekly hours'],
                ['name' => 'start_date', 'label' => 'Start date'],
                ['name' => 'end_date', 'label' => 'End date'],
                ['name' => 'contract_status', 'label' => 'Contract status'],
            ]),
        );
    }

    public function storeAllocations(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyAllocation::class, [
            'allocation_code' => ['required', 'string', 'max:80', 'unique:work_study_allocations,allocation_code'],
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'assigned_position' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'supervisor' => ['nullable', 'string', 'max:190'],
            'approved_weekly_hours' => ['nullable', 'string', 'max:40'],
            'start_date' => ['nullable', 'string', 'max:40'],
            'end_date' => ['nullable', 'string', 'max:40'],
            'contract_status' => ['nullable', 'string', 'max:40'],
        ], ['contract_status' => 'Active'], 'Allocation saved.');
    }

    public function timesheets(Request $request): View
    {
        $records = WorkStudyTimesheet::query()->latest()->get();
        $timesheets = $records->map(fn (WorkStudyTimesheet $row): array => [
            'timesheet_no' => $row->timesheet_no,
            'student_name' => $row->student_name,
            'department' => $row->department ?? '—',
            'month_cycle' => $row->month_cycle ?? '—',
            'hours_claimed' => $row->hours_claimed ?? '—',
            'hourly_rate' => $row->hourly_rate ?? '—',
            'total_amount' => $row->total_amount ?? '—',
            'supervisor_rating' => $row->supervisor_rating ?? '—',
            'supervisor_status' => $row->supervisor_status,
        ])->all();
        $stats = new SoftStatsBag([
            'submittedTimesheets' => $records->count(),
            'pendingSupervisorApproval' => $records->filter(fn (WorkStudyTimesheet $r): bool => str_contains(strtolower($r->supervisor_status), 'pending'))->count(),
            'approvedBySupervisor' => $records->filter(fn (WorkStudyTimesheet $r): bool => str_contains(strtolower($r->supervisor_status), 'approv'))->count(),
            'loggedHoursThisMonth' => $records->count(),
        ]);

        return view('work-study.timesheets', compact('timesheets', 'stats'))->with(
            'operationalCreate',
            $this->form('Add timesheet', 'Persists to work_study_timesheets.', 'work-study.timesheets.store', [
                ['name' => 'timesheet_no', 'label' => 'Timesheet number', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'month_cycle', 'label' => 'Month cycle'],
                ['name' => 'hours_claimed', 'label' => 'Hours claimed'],
                ['name' => 'hourly_rate', 'label' => 'Hourly rate'],
                ['name' => 'total_amount', 'label' => 'Total amount'],
                ['name' => 'supervisor_rating', 'label' => 'Supervisor rating'],
                ['name' => 'supervisor_status', 'label' => 'Supervisor status'],
            ]),
        );
    }

    public function storeTimesheets(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyTimesheet::class, [
            'timesheet_no' => ['required', 'string', 'max:80', 'unique:work_study_timesheets,timesheet_no'],
            'student_name' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'month_cycle' => ['nullable', 'string', 'max:40'],
            'hours_claimed' => ['nullable', 'string', 'max:40'],
            'hourly_rate' => ['nullable', 'string', 'max:40'],
            'total_amount' => ['nullable', 'string', 'max:80'],
            'supervisor_rating' => ['nullable', 'string', 'max:40'],
            'supervisor_status' => ['nullable', 'string', 'max:40'],
        ], ['supervisor_status' => 'Pending'], 'Timesheet saved.');
    }

    public function claims(Request $request): View
    {
        $records = WorkStudyClaim::query()->latest()->get();
        $claims = $records->map(fn (WorkStudyClaim $row): array => [
            'voucher_no' => $row->voucher_no,
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'timesheet_ref' => $row->timesheet_ref ?? '—',
            'gross_amount' => $row->gross_amount ?? '—',
            'fee_account_credit' => $row->fee_account_credit ?? '—',
            'cash_stipend' => $row->cash_stipend ?? '—',
            'disbursement_mode' => $row->disbursement_mode ?? '—',
            'audit_approval' => $row->audit_approval ?? '—',
            'disbursement_status' => $row->disbursement_status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalPaidToDate' => $records->filter(fn (WorkStudyClaim $r): bool => str_contains(strtolower($r->disbursement_status), 'paid'))->count(),
            'pendingFinanceApproval' => $records->filter(fn (WorkStudyClaim $r): bool => str_contains(strtolower($r->disbursement_status), 'pending'))->count(),
            'mpesaDisbursements' => $records->filter(fn (WorkStudyClaim $r): bool => str_contains(strtolower((string) $r->disbursement_mode), 'pesa'))->count(),
            'tuitionCredits' => $records->filter(fn (WorkStudyClaim $r): bool => filled($r->fee_account_credit))->count(),
        ]);

        return view('work-study.claims', compact('claims', 'stats'))->with(
            'operationalCreate',
            $this->form('Add claim', 'Persists to work_study_claims.', 'work-study.claims.store', [
                ['name' => 'voucher_no', 'label' => 'Voucher number', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'timesheet_ref', 'label' => 'Timesheet ref'],
                ['name' => 'gross_amount', 'label' => 'Gross amount'],
                ['name' => 'fee_account_credit', 'label' => 'Fee account credit'],
                ['name' => 'cash_stipend', 'label' => 'Cash stipend'],
                ['name' => 'disbursement_mode', 'label' => 'Disbursement mode'],
                ['name' => 'audit_approval', 'label' => 'Audit approval'],
                ['name' => 'disbursement_status', 'label' => 'Disbursement status'],
            ]),
        );
    }

    public function storeClaims(Request $request): RedirectResponse
    {
        return $this->store($request, WorkStudyClaim::class, [
            'voucher_no' => ['required', 'string', 'max:80', 'unique:work_study_claims,voucher_no'],
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'timesheet_ref' => ['nullable', 'string', 'max:80'],
            'gross_amount' => ['nullable', 'string', 'max:80'],
            'fee_account_credit' => ['nullable', 'string', 'max:80'],
            'cash_stipend' => ['nullable', 'string', 'max:80'],
            'disbursement_mode' => ['nullable', 'string', 'max:80'],
            'audit_approval' => ['nullable', 'string', 'max:80'],
            'disbursement_status' => ['nullable', 'string', 'max:40'],
        ], ['disbursement_status' => 'Pending'], 'Claim saved.');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $defaults
     */
    private function store(Request $request, string $model, array $rules, array $defaults, string $message): RedirectResponse
    {
        $this->authorizePermission($request, 'student_affairs.manage');
        $data = $request->validate($rules);
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                $data[$key] = $value;
            }
        }
        $model::query()->create($data);

        return back()->with('success', $message);
    }

    /**
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @return array{title: string, hint: string, action: string, fields: list<array{name: string, label: string, type?: string, required?: bool}>}
     */
    private function form(string $title, string $hint, string $route, array $fields): array
    {
        return [
            'title' => $title,
            'hint' => $hint,
            'action' => route($route),
            'fields' => $fields,
        ];
    }
}
