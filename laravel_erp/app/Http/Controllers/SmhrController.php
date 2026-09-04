<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\AcademicDepartment;
use App\Models\AuditLog;
use App\Models\ModuleRecord;
use App\Models\School;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\User;
use App\Services\OperationalRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class SmhrController extends Controller
{
    use AuthorizesCataloguePermission;

    public function __construct(private readonly OperationalRecordService $records) {}

    private function authorizeHr(Request $request, string ...$permissions): void
    {
        $this->authorizePermission($request, ...($permissions !== [] ? $permissions : ['smhr.view']));
    }

    public function dashboard(Request $request): View
    {
        $staff = Staff::query()->with(['user', 'course'])->get();
        $activeStaff = $staff->filter(fn (Staff $member): bool => (bool) $member->user?->is_active);
        $teachingFaculty = $activeStaff->whereNotNull('course_id')->count();
        $approvedLeave = StaffLeaveRequest::query()->where('status', 'approved')->whereDate('leave_date', '>=', today())->count();

        $metrics = [
            'totalStaff' => $activeStaff->count(),
            'teachingFaculty' => $teachingFaculty,
            'administrativeStaff' => $activeStaff->count() - $teachingFaculty,
            'onLeave' => $approvedLeave,
            'monthlyPayrollGross' => 0,
            'pendingAppraisals' => ModuleRecord::query()->where('module', 'smhr')->where('kind', 'appraisal')->where('status', 'like', '%Pending%')->count(),
            'retentionRate' => ($staff->count() > 0 ? round(($activeStaff->count() / $staff->count()) * 100, 1) : 0.0).'%',
            'activeVacancies' => ModuleRecord::query()->where('module', 'smhr')->where('kind', 'onboarding')->where('status', 'like', '%Open%')->count(),
        ];

        $departmentStats = $activeStaff
            ->groupBy(fn (Staff $member): string => $member->department ?: $member->user?->department ?: $member->course?->name ?: 'Unassigned')
            ->map(fn ($members, string $department): array => [
                'dept' => $department,
                'teaching' => $members->whereNotNull('course_id')->count(),
                'admin' => $members->whereNull('course_id')->count(),
                'budget' => 'KES 0.00',
            ])->values()->all();

        $recentHires = $activeStaff->sortByDesc('created_at')->take(4)->map(fn (Staff $member): array => [
            'id' => $member->staff_no ?: 'EMP-'.str_pad((string) $member->id, 6, '0', STR_PAD_LEFT),
            'name' => $member->user?->name ?? 'Unknown',
            'role' => $member->designation ?: $member->user?->title ?: 'Staff member',
            'dept' => $member->department ?: $member->user?->department ?: $member->course?->name ?: 'Unassigned',
            'type' => $member->employment_type ?: 'Active',
            'joined' => $member->joined_at?->format('d M Y') ?? $member->created_at?->format('d M Y') ?? 'Not recorded',
        ])->values()->all();

        $pendingLeaves = StaffLeaveRequest::query()
            ->with(['staff.user'])
            ->where('status', 'pending')
            ->orderBy('leave_date')
            ->limit(5)
            ->get()
            ->map(fn (StaffLeaveRequest $leave): array => [
                'id' => $leave->id,
                'name' => $leave->staff?->user?->name ?? 'Unknown',
                'type' => $leave->leave_type ?: 'Leave request',
                'days' => $leave->days ?? 1,
                'from' => $leave->leave_date?->format('d M Y') ?? '—',
                'to' => ($leave->end_date ?? $leave->leave_date)?->format('d M Y') ?? '—',
                'status' => strtoupper($leave->status),
            ])->all();

        return view('smhr.dashboard', compact('metrics', 'departmentStats', 'recentHires', 'pendingLeaves'));
    }

    public function staffDirectory(Request $request): View
    {
        $schools = School::all();
        $departments = AcademicDepartment::all();

        $staffMembers = Staff::query()->with(['user', 'course'])->latest()->get()->map(function (Staff $member): array {
            return [
                'id' => $member->staff_no ?: 'EMP-'.str_pad((string) $member->id, 6, '0', STR_PAD_LEFT),
                'name' => $member->user?->name ?? 'Unknown',
                'email' => $member->user?->email ?? '—',
                'phone' => $member->phone ?: ($member->user?->phone_number ?: '—'),
                'designation' => $member->designation ?: ($member->user?->title ?: 'Staff'),
                'school' => $member->course?->name ?: 'Unassigned',
                'department' => $member->department ?: ($member->user?->department ?: 'Unassigned'),
                'type' => $member->employment_type ?: 'Permanent',
                'rank' => $member->rank ?: '—',
                'status' => strtoupper($member->employment_status ?: 'ACTIVE'),
                'qualification' => $member->qualification ?: '—',
                'joined' => $member->joined_at?->format('d M Y') ?? $member->created_at?->format('d M Y') ?? '—',
            ];
        })->all();

        return view('smhr.staff-directory', compact('staffMembers', 'schools', 'departments'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string'],
            'rank' => ['required', 'string'],
            'qualification' => ['required', 'string', 'max:255'],
        ]);

        $staff = DB::transaction(function () use ($validated): Staff {
            $parts = preg_split('/\s+/', trim($validated['name']), 2) ?: [trim($validated['name'])];
            $user = User::create([
                'name' => trim($validated['name']),
                'first_name' => $parts[0] ?? 'Staff',
                'last_name' => $parts[1] ?? 'Member',
                'email' => mb_strtolower(trim($validated['email'])),
                'phone_number' => $validated['phone'],
                'title' => mb_substr($validated['designation'], 0, 30),
                'department' => $validated['department'],
                'role' => 'staff',
                'password' => Str::password(24),
                'is_active' => true,
            ]);

            $staffNo = 'EMP'.now()->format('y').str_pad((string) (Staff::query()->count() + 1), 4, '0', STR_PAD_LEFT);

            return Staff::create([
                'user_id' => $user->id,
                'staff_no' => $staffNo,
                'phone' => $validated['phone'],
                'designation' => $validated['designation'],
                'department' => $validated['department'],
                'employment_type' => $validated['employment_type'],
                'rank' => $validated['rank'],
                'qualification' => $validated['qualification'],
                'employment_status' => 'ACTIVE',
                'joined_at' => now()->toDateString(),
            ]);
        });

        AuditLog::record('smhr.staff_created', $staff, null, $staff->load('user')->toArray());

        return redirect()->route('smhr.staff-directory')->with('success', 'Staff member '.$validated['name'].' created with ID '.($staff->staff_no ?? $staff->id).'.');
    }

    public function leaveManagement(Request $request): View
    {
        $requests = StaffLeaveRequest::query()->with(['staff.user'])->latest()->get();
        $leaveStats = [
            'totalOnLeave' => $requests->where('status', 'approved')->filter(function (StaffLeaveRequest $leave): bool {
                $start = $leave->leave_date;
                $end = $leave->end_date ?? $leave->leave_date;

                return $start && $end && $start->lte(today()) && $end->gte(today());
            })->count(),
            'pendingApproval' => $requests->where('status', 'pending')->count(),
            'approvedThisMonth' => $requests->where('status', 'approved')->filter(fn (StaffLeaveRequest $leave): bool => $leave->updated_at?->isCurrentMonth())->count(),
            'averageLeaveDays' => number_format((float) ($requests->avg('days') ?? 0), 1).' Days',
        ];

        $leaveRequests = $requests->map(function (StaffLeaveRequest $leave): array {
            return [
                'id' => (string) $leave->id,
                'staff_id' => $leave->staff?->staff_no ?: 'EMP-'.str_pad((string) ($leave->staff_id ?? 0), 6, '0', STR_PAD_LEFT),
                'name' => $leave->staff?->user?->name ?? 'Unknown',
                'dept' => $leave->staff?->department ?: ($leave->staff?->user?->department ?: 'Unassigned'),
                'type' => $leave->leave_type ?: 'Leave',
                'days' => $leave->days ?? 1,
                'start_date' => $leave->leave_date?->format('d M Y') ?? '—',
                'end_date' => ($leave->end_date ?? $leave->leave_date)?->format('d M Y') ?? '—',
                'reason' => $leave->message ?: '—',
                'reliever' => $leave->reliever ?: '—',
                'status' => strtoupper($leave->status),
                'balance_remaining' => 0,
            ];
        })->all();

        return view('smhr.leave-management', compact('leaveStats', 'leaveRequests'));
    }

    public function submitLeave(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.leave.submit');
        $validated = $request->validate([
            'staff_name' => ['required', 'string'],
            'leave_type' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'reliever' => ['required', 'string'],
        ]);

        $staff = Staff::query()->whereHas('user', fn ($q) => $q->where('name', $validated['staff_name']))->first();
        abort_unless($staff, 422, 'No staff member found for this leave request.');

        $days = max(1, (int) now()->parse($validated['start_date'])->diffInDays(now()->parse($validated['end_date'])) + 1);
        $leave = StaffLeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'message' => $validated['reason'],
            'status' => 'pending',
            'leave_type' => $validated['leave_type'],
            'days' => $days,
            'reliever' => $validated['reliever'],
        ]);
        AuditLog::record('smhr.leave_submitted', $leave, null, $leave->toArray());

        return redirect()->route('smhr.leave-management')->with('success', 'Leave application for '.$validated['staff_name'].' submitted for approval.');
    }

    public function approveLeave(Request $request, string $id): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.leave.approve');
        $leave = StaffLeaveRequest::query()->findOrFail($id);
        $before = $leave->toArray();
        $leave->update(['status' => 'approved']);
        AuditLog::record('smhr.leave_approved', $leave, $before, $leave->fresh()?->toArray());

        return redirect()->route('smhr.leave-management')->with('success', 'Leave request #'.$id.' approved.');
    }

    public function rejectLeave(Request $request, string $id): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.leave.approve');
        $leave = StaffLeaveRequest::query()->findOrFail($id);
        $before = $leave->toArray();
        $leave->update(['status' => 'rejected']);
        AuditLog::record('smhr.leave_rejected', $leave, $before, $leave->fresh()?->toArray());

        return redirect()->route('smhr.leave-management')->with('error', 'Leave request #'.$id.' rejected.');
    }

    public function workloadAllocation(Request $request): View
    {
        return $this->records->screen($request, 'smhr.workload-allocation', 'smhr', 'workload', 'allocations', [
            ['key' => 'averageHours', 'op' => 'avg_days', 'field' => 'hours'],
            ['key' => 'maxAllowedHours', 'op' => 'count'],
            ['key' => 'fullyAllocatedFaculty', 'op' => 'percent_match', 'field' => 'status', 'needle' => 'Allocated'],
            ['key' => 'overloadCount', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Overload'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'hours', 'label' => 'Weekly hours'],
            ['name' => 'status', 'label' => 'Status'],
        ], [], 'workloadStats');
    }

    public function performanceAppraisals(Request $request): View
    {
        return $this->records->screen($request, 'smhr.performance-appraisals', 'smhr', 'appraisal', 'appraisals', [
            ['key' => 'completed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Completed'],
            ['key' => 'pendingReview', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'averageScore', 'op' => 'avg_days', 'field' => 'score'],
            ['key' => 'topPerformers', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Excellent'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'cycle', 'label' => 'Appraisal cycle'],
            ['name' => 'score', 'label' => 'Score'],
            ['name' => 'status', 'label' => 'Status'],
        ], [], 'appraisalStats');
    }

    public function payrollRegister(Request $request): View
    {
        $rows = $this->records->rows('smhr', 'payroll', $request);
        $gross = collect($rows)->sum(fn (array $row): float => $this->records->parseMoney($row['gross'] ?? 0));
        $net = collect($rows)->sum(fn (array $row): float => $this->records->parseMoney($row['net'] ?? 0));

        return $this->records->screen($request, 'smhr.payroll-register', 'smhr', 'payroll', 'payrollItems', [
            ['key' => 'gross', 'op' => 'sum_money', 'field' => 'gross'],
            ['key' => 'net', 'op' => 'sum_money', 'field' => 'net'],
            ['key' => 'staffPaid', 'op' => 'count'],
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'period', 'label' => 'Payroll period'],
            ['name' => 'gross', 'label' => 'Gross'],
            ['name' => 'net', 'label' => 'Net'],
            ['name' => 'status', 'label' => 'Status'],
        ], [
            'payrollSummary' => [
                'month' => now()->format('F Y'),
                'disbursedStatus' => count($rows) ? 'Database register loaded' : 'No payroll rows yet',
                'grossSalary' => $gross,
                'totalAllowances' => 0,
                'statutoryPAYE' => 0,
                'statutoryNHIF' => 0,
                'housingLevy' => 0,
                'netPayable' => $net,
            ],
        ]);
    }

    public function payslip(Request $request, ?string $id = null): View
    {
        $selectedStaffId = (string) ($request->query('staff_id') ?: $id ?: '');
        $month = (string) $request->query('month', now()->format('F'));
        $year = (string) $request->query('year', now()->format('Y'));
        $allStaff = Staff::query()->with('user')->get()->mapWithKeys(function (Staff $member): array {
            $key = $member->staff_no ?: (string) $member->id;

            return [$key => [
                'id' => $key,
                'name' => $member->user?->name ?? 'Unknown',
            ]];
        })->all();
        if ($selectedStaffId === '' && $allStaff !== []) {
            $selectedStaffId = (string) array_key_first($allStaff);
        }
        $staff = Staff::query()->with('user')
            ->when($selectedStaffId !== '', fn ($q) => $q->where('staff_no', $selectedStaffId)->orWhere('id', $selectedStaffId))
            ->latest()->first();
        $availableMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $payslipData = [
            'staff_id' => $staff?->staff_no ?: '—',
            'year' => (int) $year,
            'month' => $month,
            'month_index' => max(1, (int) array_search($month, $availableMonths, true) + 1),
            'eft_ref' => '—',
            'pay_period' => $month.' '.$year,
            'staff_name' => $staff?->user?->name ?: 'No staff selected',
            'payslip_no' => '—',
            'designation' => $staff?->designation ?: '—',
            'department' => $staff?->department ?: '—',
            'faculty' => '—',
            'job_group' => $staff?->rank ?: '—',
            'id_no' => '—',
            'kra_pin' => '—',
            'nssf_no' => '—',
            'nhif_no' => '—',
            'bank_name' => '—',
            'branch' => '—',
            'account_no' => '—',
            'sort_code' => '—',
            'pay_date' => now()->format('d M Y'),
            'basic_salary' => 0,
            'house_allowance' => 0,
            'commuter_allowance' => 0,
            'responsibility_allowance' => 0,
            'gross_earnings' => 0,
            'gross_tax' => 0,
            'total_relief' => 0,
            'net_tax' => 0,
            'nssf_tier1' => 0,
            'nssf_tier2' => 0,
            'total_nssf' => 0,
            'nhif_sha' => 0,
            'housing_levy' => 0,
            'sacco_shares' => 0,
            'total_deductions' => 0,
            'net_pay' => 0,
            'net_pay_words' => 'Zero Kenya Shillings Only',
            'ytd_gross' => 0,
            'ytd_taxable' => 0,
            'ytd_paye' => 0,
            'ytd_nssf' => 0,
            'ytd_sha' => 0,
            'ytd_net_pay' => 0,
            'verification_hash' => hash('sha256', 'smhr-payslip-'.($staff?->id ?? 'none').'-'.$month.$year),
        ];

        return view('smhr.payslip', compact('payslipData', 'allStaff', 'selectedStaffId', 'availableMonths', 'month', 'year'));
    }

    public function p9Form(Request $request, ?string $staffId = null): View
    {
        $year = (string) $request->query('year', '2025');
        $selectedId = (string) ($request->query('staff_id') ?: $staffId ?: '');
        $allStaff = Staff::query()->with('user')->get()->mapWithKeys(function (Staff $member): array {
            $key = $member->staff_no ?: (string) $member->id;

            return [$key => [
                'id' => $key,
                'name' => $member->user?->name ?? 'Unknown',
            ]];
        })->all();
        if ($selectedId === '' && $allStaff !== []) {
            $selectedId = (string) array_key_first($allStaff);
        }
        $staff = Staff::query()->with('user')
            ->when($selectedId !== '', fn ($q) => $q->where('staff_no', $selectedId)->orWhere('id', $selectedId))
            ->latest()->first();
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $p9Rows = array_map(static fn (string $month): array => [
            'month' => $month,
            'col_a' => 0, 'col_b' => 0, 'col_c' => 0, 'col_d' => 0,
            'col_f' => 0, 'col_h' => 0, 'col_j' => 0, 'col_k' => 0, 'col_l' => 0,
        ], $months);
        $totals = [
            'col_a' => 0, 'col_b' => 0, 'col_c' => 0, 'col_d' => 0,
            'col_f' => 0, 'col_h' => 0, 'col_j' => 0, 'col_k' => 0, 'col_l' => 0,
        ];
        $staffDetails = [
            'employer_name' => config('app.name', 'MEMA University College'),
            'employer_pin' => '—',
            'name' => $staff?->user?->name ?? ($allStaff[$selectedId]['name'] ?? 'No staff selected'),
            'kra_pin' => '—',
            'staff_id' => $staff?->staff_no ?: ($selectedId ?: '—'),
        ];

        return view('smhr.p9-form', compact('year', 'allStaff', 'selectedId', 'p9Rows', 'totals', 'staffDetails'));
    }

    public function reports(Request $request): View
    {
        $p9StaffDirectory = Staff::query()->with('user')->latest()->get()->map(static function (Staff $member): array {
            return [
                'staff_id' => $member->staff_no ?: (string) $member->id,
                'name' => $member->user?->name ?? 'Unknown',
                'kra_pin' => '—',
                'designation' => trim(($member->designation ?: '—').', '.($member->department ?: '—')),
                'year' => now()->format('Y'),
            ];
        })->all();

        return $this->records->screen($request, 'smhr.reports', 'smhr', 'hr_report', 'payrollVarianceReport', [
            ['key' => 'annualPayrollSpend', 'op' => 'sum', 'field' => 'amount'],
            ['key' => 'totalPAYERemitted', 'op' => 'sum', 'field' => 'paye'],
            ['key' => 'leaveLiabilityHours', 'op' => 'count'],
            ['key' => 'complianceRate', 'op' => 'percent_match', 'field' => 'status', 'needle' => 'Compliant'],
        ], [
            ['name' => 'report_code', 'label' => 'Report code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'paye', 'label' => 'PAYE'],
            ['name' => 'status', 'label' => 'Status'],
        ], [
            'statutorySchedule' => [],
            'nssfSchedule' => [],
            'shaSchedule' => [],
            'establishmentAudit' => [],
            'p9StaffDirectory' => $p9StaffDirectory,
            'staffHeadcount' => Staff::query()->count(),
        ], 'reportMetrics');
    }

    public function disciplinaryRecords(Request $request): View
    {
        return $this->records->screen($request, 'smhr.disciplinary-records', 'smhr', 'disciplinary', 'records', [
            ['key' => 'totalIncidents', 'op' => 'count'],
            ['key' => 'resolved', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Closed'],
            ['key' => 'activeHearings', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'officialCommendations', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Commendation'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'case_ref', 'label' => 'Case reference', 'required' => true],
            ['name' => 'offense', 'label' => 'Offense'],
            ['name' => 'status', 'label' => 'Status'],
        ], [], 'governanceStats');
    }

    public function onboarding(Request $request): View
    {
        return $this->records->screen($request, 'smhr.onboarding', 'smhr', 'onboarding', 'candidates', [
            ['key' => 'inProgress', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Progress'],
            ['key' => 'completedThisQuarter', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Completed'],
            ['key' => 'avgOnboardingDays', 'op' => 'avg_days', 'field' => 'days'],
            ['key' => 'itAccountsProvisioned', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Provisioned'],
        ], [
            ['name' => 'staff_name', 'label' => 'New staff', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date'],
            ['name' => 'days', 'label' => 'Onboarding days'],
            ['name' => 'status', 'label' => 'Status'],
        ], [], 'onboardingStats');
    }

    public function storeOnboarding(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $this->records->store($request, 'smhr', 'onboarding');

        return redirect()->route('smhr.onboarding')->with('success', 'Onboarding case saved to the database.');
    }
}
