<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\AcademicDepartment;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\SmhrAppraisal;
use App\Models\SmhrDisciplinaryRecord;
use App\Models\SmhrOnboardingCandidate;
use App\Models\SmhrPayrollItem;
use App\Models\SmhrPayrollVarianceReport;
use App\Models\SmhrStatutorySchedule;
use App\Models\SmhrWorkload;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\User;
use App\Services\OperationalRecordService;
use App\Support\SoftStatsBag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class SmhrController extends Controller
{
    use AuthorizesCataloguePermission;

    public function __construct(
        private readonly OperationalRecordService $records,
    ) {}

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
            'monthlyPayrollGross' => (float) SmhrPayrollItem::query()->sum('gross'),
            'pendingAppraisals' => SmhrAppraisal::query()->where('status', 'like', '%Pending%')->count(),
            'retentionRate' => ($staff->count() > 0 ? round(($activeStaff->count() / $staff->count()) * 100, 1) : 0.0).'%',
            'activeVacancies' => SmhrOnboardingCandidate::query()
                ->where(function ($q): void {
                    $q->where('status', 'like', '%Progress%')->orWhere('status', 'like', '%Open%');
                })->count(),
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
        $records = SmhrWorkload::query()->latest()->get();
        $allocations = $records->map(fn (SmhrWorkload $row): array => [
            'staff_id' => $row->staff_id ?? '—',
            'name' => $row->name,
            'dept' => $row->dept ?? '—',
            'units' => array_values(array_filter(array_map('trim', explode(',', (string) $row->units)))),
            'teaching_hours' => (int) $row->teaching_hours,
            'supervision_hours' => (int) $row->supervision_hours,
            'admin_hours' => (int) $row->admin_hours,
            'total_hours' => (int) $row->total_hours,
            'status' => $row->status,
        ])->all();
        $avg = $records->avg('total_hours') ?: 0;
        $workloadStats = new SoftStatsBag([
            'averageHours' => number_format((float) $avg, 1).' Hrs',
            'maxAllowedHours' => '40 Hrs',
            'fullyAllocatedFaculty' => $records->filter(fn (SmhrWorkload $r): bool => str_contains(strtoupper($r->status), 'OPTIMAL') || str_contains(strtoupper($r->status), 'ALLOC'))->count(),
            'overloadCount' => $records->filter(fn (SmhrWorkload $r): bool => str_contains(strtoupper($r->status), 'OVER') || (int) $r->total_hours > 40)->count(),
        ]);

        return view('smhr.workload-allocation', compact('allocations', 'workloadStats'))->with(
            'operationalCreate',
            $this->smhrForm('Add workload allocation', 'Persists to smhr_workloads.', 'smhr.workload-allocation.store', [
                ['name' => 'name', 'label' => 'Staff name', 'required' => true],
                ['name' => 'staff_id', 'label' => 'Staff ID'],
                ['name' => 'dept', 'label' => 'Department'],
                ['name' => 'units', 'label' => 'Units (comma-separated)'],
                ['name' => 'teaching_hours', 'label' => 'Teaching hours', 'type' => 'number'],
                ['name' => 'supervision_hours', 'label' => 'Supervision hours', 'type' => 'number'],
                ['name' => 'admin_hours', 'label' => 'Admin hours', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeWorkload(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'staff_id' => ['nullable', 'string', 'max:40'],
            'dept' => ['nullable', 'string', 'max:190'],
            'units' => ['nullable', 'string', 'max:255'],
            'teaching_hours' => ['nullable', 'integer', 'min:0'],
            'supervision_hours' => ['nullable', 'integer', 'min:0'],
            'admin_hours' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        $teaching = (int) ($data['teaching_hours'] ?? 0);
        $supervision = (int) ($data['supervision_hours'] ?? 0);
        $admin = (int) ($data['admin_hours'] ?? 0);
        SmhrWorkload::query()->create([
            ...$data,
            'teaching_hours' => $teaching,
            'supervision_hours' => $supervision,
            'admin_hours' => $admin,
            'total_hours' => $teaching + $supervision + $admin,
            'status' => $data['status'] ?? (($teaching + $supervision + $admin) > 40 ? 'OVERLOAD' : 'OPTIMAL'),
        ]);

        return back()->with('success', 'Workload allocation saved.');
    }

    public function performanceAppraisals(Request $request): View
    {
        $records = SmhrAppraisal::query()->latest()->get();
        $appraisals = $records->map(fn (SmhrAppraisal $row): array => [
            'staff_id' => $row->staff_id ?? '—',
            'name' => $row->name,
            'dept' => $row->dept ?? '—',
            'teaching_eval' => $row->teaching_eval ?? '—',
            'research_publications' => $row->research_publications ?? '—',
            'community_service' => $row->community_service ?? '—',
            'overall_score' => $row->overall_score ?? '—',
            'grade' => $row->grade ?? '—',
            'completed' => $row->completed ?? '—',
            'status' => $row->status,
        ])->all();
        $appraisalStats = new SoftStatsBag([
            'completed' => $records->filter(fn (SmhrAppraisal $r): bool => str_contains(strtolower($r->status), 'complet'))->count(),
            'pendingReview' => $records->filter(fn (SmhrAppraisal $r): bool => str_contains(strtolower($r->status), 'pending'))->count(),
            'averageScore' => $records->pluck('overall_score')->filter()->first() ?? '—',
            'topPerformers' => $records->filter(fn (SmhrAppraisal $r): bool => str_contains(strtolower($r->status), 'excellent') || str_contains(strtolower((string) $r->grade), 'a'))->count(),
        ]);

        return view('smhr.performance-appraisals', compact('appraisals', 'appraisalStats'))->with(
            'operationalCreate',
            $this->smhrForm('Add appraisal', 'Persists to smhr_appraisals.', 'smhr.performance-appraisals.store', [
                ['name' => 'name', 'label' => 'Staff name', 'required' => true],
                ['name' => 'staff_id', 'label' => 'Staff ID'],
                ['name' => 'dept', 'label' => 'Department'],
                ['name' => 'teaching_eval', 'label' => 'Teaching eval'],
                ['name' => 'research_publications', 'label' => 'Research publications'],
                ['name' => 'community_service', 'label' => 'Community service'],
                ['name' => 'overall_score', 'label' => 'Overall score'],
                ['name' => 'grade', 'label' => 'Grade'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeAppraisal(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'staff_id' => ['nullable', 'string', 'max:40'],
            'dept' => ['nullable', 'string', 'max:190'],
            'teaching_eval' => ['nullable', 'string', 'max:40'],
            'research_publications' => ['nullable', 'string', 'max:80'],
            'community_service' => ['nullable', 'string', 'max:80'],
            'overall_score' => ['nullable', 'string', 'max:40'],
            'grade' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        SmhrAppraisal::query()->create([
            ...$data,
            'completed' => str_contains(strtolower((string) ($data['status'] ?? '')), 'complet') ? 'Yes' : 'No',
            'status' => $data['status'] ?? 'Pending',
        ]);

        return back()->with('success', 'Appraisal saved.');
    }

    public function payrollRegister(Request $request): View
    {
        $records = SmhrPayrollItem::query()->latest()->get();
        $payrollItems = $records->map(fn (SmhrPayrollItem $row): array => [
            'id' => (string) $row->id,
            'staff_id' => $row->staff_id ?? '—',
            'name' => $row->name,
            'dept' => $row->dept ?? '—',
            'bank' => $row->bank ?? '—',
            'month' => $row->month ?? '—',
            'basic_pay' => (float) $row->basic_pay,
            'allowances' => (float) $row->allowances,
            'gross' => (float) $row->gross,
            'paye' => (float) $row->paye,
            'statutory' => (float) $row->statutory,
            'net_pay' => (float) $row->net_pay,
            'status' => $row->status,
        ])->all();
        $gross = (float) $records->sum('gross');
        $net = (float) $records->sum('net_pay');
        $allowances = (float) $records->sum('allowances');
        $paye = (float) $records->sum('paye');
        $statutory = (float) $records->sum('statutory');
        $payrollSummary = [
            'month' => now()->format('F Y'),
            'disbursedStatus' => $records->isNotEmpty() ? 'Database register loaded' : 'No payroll rows yet',
            'grossSalary' => $gross,
            'totalAllowances' => $allowances,
            'statutoryPAYE' => $paye,
            'statutoryNHIF' => $statutory,
            'housingLevy' => 0,
            'netPayable' => $net,
        ];

        return view('smhr.payroll-register', compact('payrollItems', 'payrollSummary'))->with(
            'operationalCreate',
            $this->smhrForm('Add payroll row', 'Persists to smhr_payroll_items.', 'smhr.payroll-register.store', [
                ['name' => 'name', 'label' => 'Staff name', 'required' => true],
                ['name' => 'staff_id', 'label' => 'Staff ID'],
                ['name' => 'dept', 'label' => 'Department'],
                ['name' => 'bank', 'label' => 'Bank'],
                ['name' => 'month', 'label' => 'Month'],
                ['name' => 'basic_pay', 'label' => 'Basic pay', 'type' => 'number'],
                ['name' => 'allowances', 'label' => 'Allowances', 'type' => 'number'],
                ['name' => 'gross', 'label' => 'Gross', 'type' => 'number'],
                ['name' => 'paye', 'label' => 'PAYE', 'type' => 'number'],
                ['name' => 'statutory', 'label' => 'Other statutory', 'type' => 'number'],
                ['name' => 'net_pay', 'label' => 'Net pay', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePayroll(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'staff_id' => ['nullable', 'string', 'max:40'],
            'dept' => ['nullable', 'string', 'max:190'],
            'bank' => ['nullable', 'string', 'max:120'],
            'month' => ['nullable', 'string', 'max:40'],
            'basic_pay' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'gross' => ['nullable', 'numeric', 'min:0'],
            'paye' => ['nullable', 'numeric', 'min:0'],
            'statutory' => ['nullable', 'numeric', 'min:0'],
            'net_pay' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        SmhrPayrollItem::query()->create([
            ...$data,
            'basic_pay' => $data['basic_pay'] ?? 0,
            'allowances' => $data['allowances'] ?? 0,
            'gross' => $data['gross'] ?? 0,
            'paye' => $data['paye'] ?? 0,
            'statutory' => $data['statutory'] ?? 0,
            'net_pay' => $data['net_pay'] ?? 0,
            'status' => $data['status'] ?? 'Pending',
        ]);

        return back()->with('success', 'Payroll row saved.');
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
        $payroll = SmhrPayrollItem::query()
            ->when($staff !== null, fn ($q) => $q->where('staff_id', $staff->staff_no)->orWhere('name', $staff->user?->name))
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
            'payslip_no' => $payroll ? 'PS-'.$payroll->id : '—',
            'designation' => $staff?->designation ?: '—',
            'department' => $staff?->department ?: '—',
            'faculty' => '—',
            'job_group' => $staff?->rank ?: '—',
            'id_no' => '—',
            'kra_pin' => '—',
            'nssf_no' => '—',
            'nhif_no' => '—',
            'bank_name' => $payroll?->bank ?: '—',
            'branch' => '—',
            'account_no' => '—',
            'sort_code' => '—',
            'pay_date' => now()->format('d M Y'),
            'basic_salary' => (float) ($payroll?->basic_pay ?? 0),
            'house_allowance' => 0,
            'commuter_allowance' => 0,
            'responsibility_allowance' => 0,
            'gross_earnings' => (float) ($payroll?->gross ?? 0),
            'gross_tax' => (float) ($payroll?->paye ?? 0),
            'total_relief' => 0,
            'net_tax' => (float) ($payroll?->paye ?? 0),
            'nssf_tier1' => 0,
            'nssf_tier2' => 0,
            'total_nssf' => 0,
            'nhif_sha' => (float) ($payroll?->statutory ?? 0),
            'housing_levy' => 0,
            'sacco_shares' => 0,
            'total_deductions' => (float) (($payroll?->paye ?? 0) + ($payroll?->statutory ?? 0)),
            'net_pay' => (float) ($payroll?->net_pay ?? 0),
            'net_pay_words' => ((float) ($payroll?->net_pay ?? 0)) > 0 ? 'As per payroll register' : 'Zero Kenya Shillings Only',
            'ytd_gross' => (float) ($payroll?->gross ?? 0),
            'ytd_taxable' => (float) ($payroll?->gross ?? 0),
            'ytd_paye' => (float) ($payroll?->paye ?? 0),
            'ytd_nssf' => 0,
            'ytd_sha' => (float) ($payroll?->statutory ?? 0),
            'ytd_net_pay' => (float) ($payroll?->net_pay ?? 0),
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
        $variance = SmhrPayrollVarianceReport::query()->latest()->get();
        $payrollVarianceReport = $variance->map(fn (SmhrPayrollVarianceReport $row): array => [
            'month' => $row->month,
            'staff_count' => (string) $row->staff_count,
            'gross' => $row->gross ?? '—',
            'paye' => $row->paye ?? '—',
            'variance' => $row->variance ?? '—',
            'reason' => $row->reason ?? '—',
        ])->all();
        $statutory = SmhrStatutorySchedule::query()->latest()->get();
        $statutorySchedule = $statutory->map(fn (SmhrStatutorySchedule $row): array => [
            'obligation' => $row->obligation,
            'authority' => $row->authority ?? '—',
            'frequency' => $row->frequency ?? '—',
            'amount' => $row->amount ?? '—',
            'ref' => $row->ref ?? '—',
            'status' => $row->status,
        ])->all();
        $reportMetrics = new SoftStatsBag([
            'annualPayrollSpend' => (float) $variance->sum('amount'),
            'totalPAYERemitted' => $variance->count(),
            'leaveLiabilityHours' => StaffLeaveRequest::query()->sum('days'),
            'complianceRate' => $statutory->count()
                ? round(($statutory->filter(fn (SmhrStatutorySchedule $r): bool => str_contains(strtolower($r->status), 'compliant'))->count() / max(1, $statutory->count())) * 100, 1).'%'
                : '0%',
        ]);

        return view('smhr.reports', [
            'payrollVarianceReport' => $payrollVarianceReport,
            'statutorySchedule' => $statutorySchedule,
            'nssfSchedule' => [],
            'shaSchedule' => [],
            'establishmentAudit' => [],
            'p9StaffDirectory' => $p9StaffDirectory,
            'staffHeadcount' => Staff::query()->count(),
            'reportMetrics' => $reportMetrics,
        ])->with('operationalCreate', $this->smhrForm('Add payroll variance row', 'Persists to smhr_payroll_variance_reports.', 'smhr.reports.store', [
            ['name' => 'month', 'label' => 'Month', 'required' => true],
            ['name' => 'staff_count', 'label' => 'Staff count', 'type' => 'number'],
            ['name' => 'gross', 'label' => 'Gross'],
            ['name' => 'paye', 'label' => 'PAYE'],
            ['name' => 'variance', 'label' => 'Variance %'],
            ['name' => 'reason', 'label' => 'Reason'],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'month' => ['required', 'string', 'max:40'],
            'staff_count' => ['nullable', 'integer', 'min:0'],
            'gross' => ['nullable', 'string', 'max:80'],
            'paye' => ['nullable', 'string', 'max:80'],
            'variance' => ['nullable', 'string', 'max:40'],
            'reason' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        SmhrPayrollVarianceReport::query()->create([
            ...$data,
            'staff_count' => $data['staff_count'] ?? 0,
            'amount' => $data['amount'] ?? 0,
            'status' => $data['status'] ?? 'Compliant',
        ]);

        return back()->with('success', 'Payroll variance report saved.');
    }

    public function disciplinaryRecords(Request $request): View
    {
        $records = SmhrDisciplinaryRecord::query()->latest()->get();
        $mapped = $records->map(fn (SmhrDisciplinaryRecord $row): array => [
            'id' => (string) $row->id,
            'staff_id' => $row->staff_id ?? '—',
            'staff_name' => $row->staff_name,
            'dept' => $row->dept ?? '—',
            'category' => $row->category ?? '—',
            'type' => $row->type ?? '—',
            'description' => $row->description ?? '—',
            'action_taken' => $row->action_taken ?? '—',
            'date' => $row->date ?? '—',
            'resolved' => $row->resolved ?? '—',
            'status' => $row->status,
        ])->all();
        $governanceStats = new SoftStatsBag([
            'totalIncidents' => $records->count(),
            'resolved' => $records->filter(fn (SmhrDisciplinaryRecord $r): bool => str_contains(strtolower($r->status), 'closed') || str_contains(strtolower($r->status), 'resolv'))->count(),
            'activeHearings' => $records->filter(fn (SmhrDisciplinaryRecord $r): bool => str_contains(strtolower($r->status), 'open') || str_contains(strtolower($r->status), 'active'))->count(),
            'officialCommendations' => $records->filter(fn (SmhrDisciplinaryRecord $r): bool => str_contains(strtolower($r->status), 'commend') || str_contains(strtolower((string) $r->category), 'commend'))->count(),
        ]);

        return view('smhr.disciplinary-records', [
            'records' => $mapped,
            'governanceStats' => $governanceStats,
        ])->with('operationalCreate', $this->smhrForm('Add disciplinary record', 'Persists to smhr_disciplinary_records.', 'smhr.disciplinary-records.store', [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'staff_id', 'label' => 'Staff ID'],
            ['name' => 'dept', 'label' => 'Department'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'type', 'label' => 'Type'],
            ['name' => 'description', 'label' => 'Description'],
            ['name' => 'action_taken', 'label' => 'Action taken'],
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeDisciplinary(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'staff_name' => ['required', 'string', 'max:190'],
            'staff_id' => ['nullable', 'string', 'max:40'],
            'dept' => ['nullable', 'string', 'max:190'],
            'category' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'action_taken' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        SmhrDisciplinaryRecord::query()->create([
            ...$data,
            'resolved' => str_contains(strtolower((string) ($data['status'] ?? '')), 'closed') ? 'Yes' : 'No',
            'status' => $data['status'] ?? 'Open',
        ]);

        return back()->with('success', 'Disciplinary record saved.');
    }

    public function onboarding(Request $request): View
    {
        $records = SmhrOnboardingCandidate::query()->latest()->get();
        $candidates = $records->map(fn (SmhrOnboardingCandidate $row): array => [
            'id' => (string) $row->id,
            'name' => $row->name,
            'designation' => $row->designation ?? '—',
            'department' => $row->department ?? '—',
            'joining_date' => $row->joining_date ?? '—',
            'stage' => $row->stage ?? '—',
            'progress' => $row->progress ?? '—',
            'checklist' => $row->checklist ?? '—',
        ])->all();
        $onboardingStats = new SoftStatsBag([
            'inProgress' => $records->filter(fn (SmhrOnboardingCandidate $r): bool => str_contains(strtolower($r->status), 'progress') || str_contains(strtolower($r->status), 'open'))->count(),
            'completedThisQuarter' => $records->filter(fn (SmhrOnboardingCandidate $r): bool => str_contains(strtolower($r->status), 'complet'))->count(),
            'avgOnboardingDays' => '—',
            'itAccountsProvisioned' => $records->filter(fn (SmhrOnboardingCandidate $r): bool => str_contains(strtolower((string) $r->checklist), 'it') || str_contains(strtolower($r->status), 'provision'))->count(),
        ]);

        return view('smhr.onboarding', compact('candidates', 'onboardingStats'));
    }

    public function storeOnboarding(Request $request): RedirectResponse
    {
        $this->authorizeHr($request, 'smhr.staff.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'designation' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'joining_date' => ['nullable', 'date'],
        ]);
        SmhrOnboardingCandidate::query()->create([
            ...$data,
            'joining_date' => $data['joining_date'] ?? null,
            'stage' => 'Induction',
            'progress' => '10%',
            'checklist' => 'Credentials pending',
            'status' => 'In Progress',
        ]);

        return redirect()->route('smhr.onboarding')->with('success', 'Onboarding case saved to the database.');
    }

    /**
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @return array{title: string, hint: string, action: string, fields: list<array{name: string, label: string, type?: string, required?: bool}>}
     */
    private function smhrForm(string $title, string $hint, string $route, array $fields): array
    {
        return [
            'title' => $title,
            'hint' => $hint,
            'action' => route($route),
            'fields' => $fields,
        ];
    }
}
