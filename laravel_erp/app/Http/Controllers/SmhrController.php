<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AcademicDepartment;
use App\Models\School;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class SmhrController extends Controller
{
    /**
     * 1. SMHR Executive Dashboard
     */
    public function dashboard(Request $request): View
    {
        $staff = Staff::query()->with(['user', 'course'])->get();
        $activeStaff = $staff->filter(fn (Staff $member): bool => (bool) $member->user?->is_active);
        $teachingFaculty = $activeStaff->whereNotNull('course_id')->count();
        $approvedLeave = DB::table('staff_leave_requests')->where('status', 'approved')->whereDate('leave_date', '>=', today())->count();

        $metrics = [
            'totalStaff' => $activeStaff->count(),
            'teachingFaculty' => $teachingFaculty,
            'administrativeStaff' => $activeStaff->count() - $teachingFaculty,
            'onLeave' => $approvedLeave,
            'monthlyPayrollGross' => 0,
            'pendingAppraisals' => 0,
            'retentionRate' => ($staff->count() > 0 ? round(($activeStaff->count() / $staff->count()) * 100, 1) : 0.0).'%',
            'activeVacancies' => 0,
        ];

        $departmentStats = $activeStaff
            ->groupBy(fn (Staff $member): string => $member->user?->department ?: $member->course?->name ?: 'Unassigned')
            ->map(fn ($members, string $department): array => [
                'dept' => $department,
                'teaching' => $members->whereNotNull('course_id')->count(),
                'admin' => $members->whereNull('course_id')->count(),
                'budget' => 'KES 0.00',
            ])->values()->all();

        $recentHires = $activeStaff->sortByDesc('created_at')->take(4)->map(fn (Staff $member): array => [
            'id' => 'EMP-'.str_pad((string) $member->id, 6, '0', STR_PAD_LEFT),
            'name' => $member->user?->name ?? 'Unknown',
            'role' => $member->user?->title ?: 'Staff member',
            'dept' => $member->user?->department ?: $member->course?->name ?: 'Unassigned',
            'type' => 'Active',
            'joined' => $member->created_at?->format('d M Y') ?? 'Not recorded',
        ])->values()->all();

        $pendingLeaves = DB::table('staff_leave_requests')
            ->join('staff', 'staff.id', '=', 'staff_leave_requests.staff_id')
            ->join('users', 'users.id', '=', 'staff.user_id')
            ->where('staff_leave_requests.status', 'pending')
            ->orderBy('staff_leave_requests.leave_date')
            ->limit(5)
            ->get(['staff_leave_requests.id', 'staff_leave_requests.leave_date', 'staff_leave_requests.message', 'staff_leave_requests.status', 'users.name'])
            ->map(fn ($leave): array => [
                'id' => $leave->id,
                'name' => $leave->name,
                'type' => 'Leave request',
                'days' => 1,
                'from' => date('d M Y', strtotime($leave->leave_date)),
                'to' => date('d M Y', strtotime($leave->leave_date)),
                'status' => ucfirst($leave->status),
            ])->all();

        return view('smhr.dashboard', compact('metrics', 'departmentStats', 'recentHires', 'pendingLeaves'));
    }

    /**
     * 2. Staff Directory & Profiles
     */
    public function staffDirectory(Request $request): View
    {
        $schools = School::all();
        $departments = AcademicDepartment::all();

        $staffMembers = [
            [
                'id' => 'EMP-2026-001',
                'name' => 'Prof. Allan Wabwire',
                'email' => 'a.wabwire@mema.ac.ke',
                'phone' => '+254 712 345 678',
                'designation' => 'Dean & Professor of Computing',
                'school' => 'School of Computing & Informatics',
                'department' => 'Computer Science & Software Eng.',
                'type' => 'Permanent / Tenured',
                'rank' => 'Professor',
                'status' => 'ACTIVE',
                'qualification' => 'PhD in Computer Science (MIT)',
                'joined' => '15 Jan 2018',
            ],
            [
                'id' => 'EMP-2026-014',
                'name' => 'Dr. Mercy Chebet',
                'email' => 'm.chebet@mema.ac.ke',
                'phone' => '+254 723 456 789',
                'designation' => 'Senior Lecturer in AI',
                'school' => 'School of Computing & Informatics',
                'department' => 'Computer Science & Software Eng.',
                'type' => 'Permanent',
                'rank' => 'Senior Lecturer',
                'status' => 'ACTIVE',
                'qualification' => 'PhD in Machine Learning (UoN)',
                'joined' => '01 Aug 2026',
            ],
            [
                'id' => 'EMP-2026-022',
                'name' => 'Dr. Emmanuel Mutua',
                'email' => 'e.mutua@mema.ac.ke',
                'phone' => '+254 734 567 890',
                'designation' => 'Head of Department (HOD)',
                'school' => 'School of Computing & Informatics',
                'department' => 'Information Technology',
                'type' => 'Permanent',
                'rank' => 'Senior Lecturer',
                'status' => 'ON LEAVE',
                'qualification' => 'PhD in Information Systems',
                'joined' => '10 Feb 2020',
            ],
            [
                'id' => 'EMP-2026-035',
                'name' => 'Prof. Peter Omwenga',
                'email' => 'p.omwenga@mema.ac.ke',
                'phone' => '+254 745 678 901',
                'designation' => 'Associate Professor of Engineering',
                'school' => 'School of Engineering',
                'department' => 'Electrical & Electronics Eng.',
                'type' => 'Permanent',
                'rank' => 'Associate Professor',
                'status' => 'ACTIVE',
                'qualification' => 'PhD in Power Systems',
                'joined' => '15 Jul 2026',
            ],
            [
                'id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'email' => 'b.achieng@mema.ac.ke',
                'phone' => '+254 756 789 012',
                'designation' => 'Senior Lecturer in Finance',
                'school' => 'School of Business & Economics',
                'department' => 'Accounting & Finance',
                'type' => '3-Year Contract',
                'rank' => 'Senior Lecturer',
                'status' => 'ACTIVE',
                'qualification' => 'PhD in Finance, CPA-K',
                'joined' => '01 Jan 2024',
            ],
            [
                'id' => 'EMP-2026-059',
                'name' => 'Dr. Samuel Kipchumba',
                'email' => 's.kipchumba@mema.ac.ke',
                'phone' => '+254 767 890 123',
                'designation' => 'Lecturer in Public Health',
                'school' => 'School of Health Sciences',
                'department' => 'Public Health & Epidemiology',
                'type' => '3-Year Contract',
                'rank' => 'Lecturer',
                'status' => 'ACTIVE',
                'qualification' => 'PhD in Public Health',
                'joined' => '01 Jul 2026',
            ],
            [
                'id' => 'EMP-2026-070',
                'name' => 'Faith Muthoni',
                'email' => 'f.muthoni@mema.ac.ke',
                'phone' => '+254 778 901 234',
                'designation' => 'Senior HR Operations Officer',
                'school' => 'Central Administration',
                'department' => 'Human Resources Department',
                'type' => 'Permanent',
                'rank' => 'Administrative Officer',
                'status' => 'ACTIVE',
                'qualification' => 'MSc Human Resource Mgt (CHRP-K)',
                'joined' => '10 Jul 2026',
            ],
        ];

        return view('smhr.staff-directory', compact('schools', 'departments', 'staffMembers'));
    }

    /**
     * Store new Staff Member
     */
    public function storeStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string'],
            'rank' => ['required', 'string'],
            'qualification' => ['required', 'string', 'max:255'],
        ]);

        return redirect()->route('smhr.staff-directory')->with('success', 'Staff member '.$validated['name'].' created and registered successfully with ID EMP-2026-'.rand(100, 999).'.');
    }

    /**
     * 3. Leave Management & Approvals
     */
    public function leaveManagement(Request $request): View
    {
        $leaveStats = [
            'totalOnLeave' => 8,
            'pendingApproval' => 5,
            'approvedThisMonth' => 19,
            'averageLeaveDays' => '14.2 Days',
        ];

        $leaveRequests = [
            [
                'id' => 'LV-2026-101',
                'staff_id' => 'EMP-2026-022',
                'name' => 'Dr. Emmanuel Mutua',
                'dept' => 'Information Technology',
                'type' => 'Annual Leave',
                'days' => 14,
                'start_date' => '10 Sep 2026',
                'end_date' => '24 Sep 2026',
                'reason' => 'Annual mandatory statutory leave break.',
                'reliever' => 'Dr. Mercy Chebet',
                'status' => 'PENDING',
                'balance_remaining' => 16,
            ],
            [
                'id' => 'LV-2026-102',
                'staff_id' => 'EMP-2026-035',
                'name' => 'Prof. Peter Omwenga',
                'dept' => 'Electrical Engineering',
                'type' => 'Study / Sabbatical',
                'days' => 60,
                'start_date' => '01 Oct 2026',
                'end_date' => '30 Nov 2026',
                'reason' => 'Visiting Research Fellowship at Oxford University for Clean Grid Energy.',
                'reliever' => 'Eng. Kevin Musyoka',
                'status' => 'PENDING',
                'balance_remaining' => 90,
            ],
            [
                'id' => 'LV-2026-103',
                'staff_id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'dept' => 'Accounting & Finance',
                'type' => 'Compassionate Leave',
                'days' => 5,
                'start_date' => '05 Sep 2026',
                'end_date' => '09 Sep 2026',
                'reason' => 'Family bereavement and funeral arrangements.',
                'reliever' => 'Mr. Charles Karanja',
                'status' => 'APPROVED',
                'balance_remaining' => 25,
            ],
            [
                'id' => 'LV-2026-104',
                'staff_id' => 'EMP-2026-059',
                'name' => 'Dr. Samuel Kipchumba',
                'dept' => 'Public Health',
                'type' => 'Sick Leave',
                'days' => 7,
                'start_date' => '01 Sep 2026',
                'end_date' => '08 Sep 2026',
                'reason' => 'Medical recuperation with attached doctor discharge slip.',
                'reliever' => 'Dr. Joan Wambui',
                'status' => 'APPROVED',
                'balance_remaining' => 30,
            ],
        ];

        return view('smhr.leave-management', compact('leaveStats', 'leaveRequests'));
    }

    /**
     * Submit Leave Application
     */
    public function submitLeave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_name' => ['required', 'string'],
            'leave_type' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'reliever' => ['required', 'string'],
        ]);

        return redirect()->route('smhr.leave-management')->with('success', 'Leave application for '.$validated['staff_name'].' submitted for HOD & HR approval.');
    }

    /**
     * Approve Leave Request
     */
    public function approveLeave(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('smhr.leave-management')->with('success', 'Leave request #'.$id.' approved successfully by HR.');
    }

    /**
     * Reject Leave Request
     */
    public function rejectLeave(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('smhr.leave-management')->with('error', 'Leave request #'.$id.' has been rejected. Notification sent to applicant.');
    }

    /**
     * 4. Faculty & Academic Workload Allocation
     */
    public function workloadAllocation(Request $request): View
    {
        $workloadStats = [
            'averageHours' => '12.4 Hrs/Wk',
            'maxAllowedHours' => '16.0 Hrs/Wk',
            'fullyAllocatedFaculty' => '94%',
            'overloadCount' => 2,
        ];

        $allocations = [
            [
                'id' => 'WK-2026-01',
                'staff_id' => 'EMP-2026-001',
                'name' => 'Prof. Allan Wabwire',
                'dept' => 'Computer Science',
                'units' => ['CSC 411: Distributed Systems Architecture', 'CSC 801: Advanced Neural Networks (PhD)'],
                'teaching_hours' => 6,
                'supervision_hours' => 6,
                'admin_hours' => 4,
                'total_hours' => 16,
                'status' => 'OPTIMAL',
            ],
            [
                'id' => 'WK-2026-02',
                'staff_id' => 'EMP-2026-014',
                'name' => 'Dr. Mercy Chebet',
                'dept' => 'Computer Science',
                'units' => ['CSC 312: Artificial Intelligence Principles', 'CSC 210: Data Structures & Algorithms'],
                'teaching_hours' => 8,
                'supervision_hours' => 4,
                'admin_hours' => 2,
                'total_hours' => 14,
                'status' => 'OPTIMAL',
            ],
            [
                'id' => 'WK-2026-03',
                'staff_id' => 'EMP-2026-035',
                'name' => 'Prof. Peter Omwenga',
                'dept' => 'Electrical Engineering',
                'units' => ['EEE 511: High Voltage Engineering', 'EEE 321: Power Transmission & Grid'],
                'teaching_hours' => 9,
                'supervision_hours' => 5,
                'admin_hours' => 3,
                'total_hours' => 17,
                'status' => 'OVERLOAD (+1 Hr)',
            ],
            [
                'id' => 'WK-2026-04',
                'staff_id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'dept' => 'Accounting & Finance',
                'units' => ['BAC 211: Financial Management I', 'BAC 415: Corporate Financial Strategy'],
                'teaching_hours' => 8,
                'supervision_hours' => 3,
                'admin_hours' => 1,
                'total_hours' => 12,
                'status' => 'OPTIMAL',
            ],
        ];

        return view('smhr.workload-allocation', compact('workloadStats', 'allocations'));
    }

    /**
     * 5. Performance Appraisals & KPIs
     */
    public function performanceAppraisals(Request $request): View
    {
        $appraisalStats = [
            'completed' => 112,
            'pendingReview' => 24,
            'averageScore' => '84.6 / 100',
            'topPerformers' => 38,
        ];

        $appraisals = [
            [
                'id' => 'APR-2026-01',
                'staff_id' => 'EMP-2026-014',
                'name' => 'Dr. Mercy Chebet',
                'dept' => 'Computer Science',
                'period' => '2025/2026 Annual Cycle',
                'teaching_eval' => 92,
                'research_publications' => 94,
                'community_service' => 85,
                'overall_score' => 90.3,
                'grade' => 'A (Exceeds Expectations)',
                'status' => 'APPROVED',
            ],
            [
                'id' => 'APR-2026-02',
                'staff_id' => 'EMP-2026-001',
                'name' => 'Prof. Allan Wabwire',
                'dept' => 'Computer Science',
                'period' => '2025/2026 Annual Cycle',
                'teaching_eval' => 95,
                'research_publications' => 98,
                'community_service' => 94,
                'overall_score' => 95.7,
                'grade' => 'A+ (Distinguished Service)',
                'status' => 'APPROVED',
            ],
            [
                'id' => 'APR-2026-03',
                'staff_id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'dept' => 'Accounting & Finance',
                'period' => '2025/2026 Annual Cycle',
                'teaching_eval' => 86,
                'research_publications' => 80,
                'community_service' => 88,
                'overall_score' => 84.7,
                'grade' => 'B+ (Meets All Standards)',
                'status' => 'APPROVED',
            ],
            [
                'id' => 'APR-2026-04',
                'staff_id' => 'EMP-2026-059',
                'name' => 'Dr. Samuel Kipchumba',
                'dept' => 'Public Health',
                'period' => '2025/2026 Annual Cycle',
                'teaching_eval' => 88,
                'research_publications' => 82,
                'community_service' => 80,
                'overall_score' => 83.3,
                'grade' => 'B+ (Meets All Standards)',
                'status' => 'PENDING DEAN SIGN-OFF',
            ],
        ];

        return view('smhr.performance-appraisals', compact('appraisalStats', 'appraisals'));
    }

    /**
     * 6. Payroll Batches & Salary Compensation
     */
    public function payrollRegister(Request $request): View
    {
        $payrollSummary = [
            'month' => 'August 2026',
            'grossSalary' => 18450000,
            'totalAllowances' => 3820000,
            'statutoryPAYE' => 3950000,
            'statutoryNHIF' => 420000,
            'statutoryNSSF' => 312000,
            'housingLevy' => 276750,
            'netPayable' => 13491250,
            'disbursedStatus' => 'PROCESSED & DISBURSED',
        ];

        $payrollItems = [
            [
                'id' => 'PAY-2026-08-01',
                'staff_id' => 'EMP-2026-001',
                'name' => 'Prof. Allan Wabwire',
                'dept' => 'Computer Science',
                'bank' => 'KCB Bank (Acc: ...4891)',
                'basic_pay' => 320000,
                'allowances' => 95000,
                'gross' => 415000,
                'paye' => 102500,
                'statutory' => 18500,
                'net_pay' => 294000,
                'status' => 'PAID',
            ],
            [
                'id' => 'PAY-2026-08-02',
                'staff_id' => 'EMP-2026-014',
                'name' => 'Dr. Mercy Chebet',
                'dept' => 'Computer Science',
                'bank' => 'Equity Bank (Acc: ...9201)',
                'basic_pay' => 240000,
                'allowances' => 65000,
                'gross' => 305000,
                'paye' => 74200,
                'statutory' => 14100,
                'net_pay' => 216700,
                'status' => 'PAID',
            ],
            [
                'id' => 'PAY-2026-08-03',
                'staff_id' => 'EMP-2026-035',
                'name' => 'Prof. Peter Omwenga',
                'dept' => 'Electrical Engineering',
                'bank' => 'Absa Bank (Acc: ...3044)',
                'basic_pay' => 290000,
                'allowances' => 80000,
                'gross' => 370000,
                'paye' => 91000,
                'statutory' => 16500,
                'net_pay' => 262500,
                'status' => 'PAID',
            ],
            [
                'id' => 'PAY-2026-08-04',
                'staff_id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'dept' => 'Accounting & Finance',
                'bank' => 'Co-operative Bank (Acc: ...7718)',
                'basic_pay' => 220000,
                'allowances' => 55000,
                'gross' => 275000,
                'paye' => 66000,
                'statutory' => 13200,
                'net_pay' => 195800,
                'status' => 'PAID',
            ],
            [
                'id' => 'PAY-2026-08-05',
                'staff_id' => 'EMP-2026-070',
                'name' => 'Faith Muthoni',
                'dept' => 'Human Resources',
                'bank' => 'Stanbic Bank (Acc: ...5120)',
                'basic_pay' => 140000,
                'allowances' => 35000,
                'gross' => 175000,
                'paye' => 38000,
                'statutory' => 8900,
                'net_pay' => 128100,
                'status' => 'PAID',
            ],
        ];

        return view('smhr.payroll-register', compact('payrollSummary', 'payrollItems'));
    }

    /**
     * 7. Interactive Official Payslip
     */
    public function payslip(Request $request, ?string $id = null): View
    {
        $allStaff = $this->getStaffMasterList();

        $selectedStaffId = $request->query('staff_id');
        if (! $selectedStaffId && $id) {
            // Check if $id is a staff ID
            if (isset($allStaff[$id])) {
                $selectedStaffId = $id;
            } else {
                // Otherwise find by payroll ID or default to first
                foreach ($allStaff as $stf) {
                    if ($stf['id'] === $id) {
                        $selectedStaffId = $stf['id'];
                        break;
                    }
                }
            }
        }

        $selectedStaffId = $selectedStaffId ?? 'EMP-2026-001';
        $staff = $allStaff[$selectedStaffId] ?? $allStaff['EMP-2026-001'];

        $month = $request->query('month', 'August');
        $year = $request->query('year', '2026');

        $availableMonths = [
            'August', 'July', 'June', 'May', 'April', 'March', 'February', 'January',
        ];

        $payslipData = $this->calculatePayslipData($staff, $month, $year);

        return view('smhr.payslip', compact('payslipData', 'allStaff', 'selectedStaffId', 'month', 'year', 'availableMonths'));
    }

    /**
     * 8. Official KRA Form P9A (Tax Deduction Card)
     */
    public function p9Form(Request $request, ?string $staffId = null): View
    {
        $year = $request->query('year', '2025');
        $allStaff = $this->getStaffMasterList();
        $selectedId = $staffId ?? $request->query('staff_id', 'EMP-2026-001');
        $staff = $allStaff[$selectedId] ?? $allStaff['EMP-2026-001'];

        $staffDetails = [
            'staff_id' => $staff['id'],
            'name' => $staff['name'],
            'kra_pin' => $staff['kra_pin'],
            'employer_name' => 'MEMA UNIVERSITY COLLEGE',
            'employer_pin' => 'P051234567Z',
            'tax_year' => $year,
            'designation' => $staff['designation'],
        ];

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $p9Rows = [];

        $basic = $staff['basic_salary'];
        $benefits = 0;
        $quarters = $staff['house_allowance'];
        $gross = $basic + $quarters + $staff['commuter_allowance'] + $staff['responsibility_allowance'];
        $nssf = 2160;
        $taxable = $gross - $nssf;

        // Calculate PAYE for 1 month
        $grossTax = 0.0;
        if ($taxable <= 24000) {
            $grossTax = $taxable * 0.10;
        } elseif ($taxable <= 32333) {
            $grossTax = (24000 * 0.10) + (($taxable - 24000) * 0.25);
        } elseif ($taxable <= 500000) {
            $grossTax = (24000 * 0.10) + (8333 * 0.25) + (($taxable - 32333) * 0.30);
        } else {
            $grossTax = (24000 * 0.10) + (8333 * 0.25) + (467667 * 0.30) + (($taxable - 500000) * 0.325);
        }

        $personalRelief = 2400;
        $paye = max(0, round($grossTax - $personalRelief, 0));

        foreach ($months as $m) {
            $p9Rows[] = [
                'month' => $m,
                'col_a' => $basic,
                'col_b' => $benefits,
                'col_c' => $quarters,
                'col_d' => $gross,
                'col_e1' => 0,
                'col_e2' => $nssf,
                'col_e3' => 20000,
                'col_f' => $nssf,
                'col_g' => 0,
                'col_h' => $taxable,
                'col_j' => (int) round($grossTax),
                'col_k' => $personalRelief,
                'col_l' => (int) $paye,
            ];
        }

        $totals = [
            'col_a' => $basic * 12,
            'col_b' => 0,
            'col_c' => $quarters * 12,
            'col_d' => $gross * 12,
            'col_f' => $nssf * 12,
            'col_h' => $taxable * 12,
            'col_j' => (int) round($grossTax) * 12,
            'col_k' => $personalRelief * 12,
            'col_l' => (int) $paye * 12,
        ];

        return view('smhr.p9-form', compact('staffDetails', 'p9Rows', 'totals', 'year', 'allStaff', 'selectedId'));
    }

    /**
     * Master Staff Profiles Database
     */
    private function getStaffMasterList(): array
    {
        return [
            'EMP-2026-001' => [
                'id' => 'EMP-2026-001',
                'name' => 'Prof. Allan Wabwire',
                'email' => 'a.wabwire@mema.ac.ke',
                'phone' => '+254 712 345 678',
                'designation' => 'Dean & Professor of Computing',
                'school' => 'School of Computing & Informatics',
                'department' => 'Computer Science & Software Eng.',
                'type' => 'Permanent / Tenured',
                'rank' => 'Professor',
                'grade' => 'Grade 15 (Professorial Scale)',
                'id_no' => '24891234',
                'kra_pin' => 'A009876543Z',
                'nssf_no' => 'NSSF-98765432',
                'nhif_no' => 'SHA-12345678',
                'bank_name' => 'Kenya Commercial Bank (KCB)',
                'account_no' => '1120984891',
                'branch' => 'University Way Branch',
                'sort_code' => '01-089',
                'basic_salary' => 320000,
                'house_allowance' => 50000,
                'commuter_allowance' => 20000,
                'responsibility_allowance' => 25000,
                'sacco_deduction' => 10000,
                'joined' => '15 Jan 2018',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-014' => [
                'id' => 'EMP-2026-014',
                'name' => 'Dr. Mercy Chebet',
                'email' => 'm.chebet@mema.ac.ke',
                'phone' => '+254 723 456 789',
                'designation' => 'Senior Lecturer in Artificial Intelligence',
                'school' => 'School of Computing & Informatics',
                'department' => 'Computer Science & Software Eng.',
                'type' => 'Permanent',
                'rank' => 'Senior Lecturer',
                'grade' => 'Grade 13 (Senior Lecturer)',
                'id_no' => '28901234',
                'kra_pin' => 'A008765432Y',
                'nssf_no' => 'NSSF-87654321',
                'nhif_no' => 'SHA-87654321',
                'bank_name' => 'Equity Bank Kenya',
                'account_no' => '081029849201',
                'branch' => 'Mombasa Supreme Branch',
                'sort_code' => '68-012',
                'basic_salary' => 240000,
                'house_allowance' => 40000,
                'commuter_allowance' => 15000,
                'responsibility_allowance' => 10000,
                'sacco_deduction' => 8000,
                'joined' => '01 Aug 2026',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-022' => [
                'id' => 'EMP-2026-022',
                'name' => 'Dr. Emmanuel Mutua',
                'email' => 'e.mutua@mema.ac.ke',
                'phone' => '+254 734 567 890',
                'designation' => 'Head of Department (HOD) - IT',
                'school' => 'School of Computing & Informatics',
                'department' => 'Information Technology',
                'type' => 'Permanent',
                'rank' => 'Senior Lecturer',
                'grade' => 'Grade 13 (Senior Lecturer)',
                'id_no' => '27192834',
                'kra_pin' => 'A007654321X',
                'nssf_no' => 'NSSF-76543210',
                'nhif_no' => 'SHA-76543210',
                'bank_name' => 'Standard Chartered Bank',
                'account_no' => '01050293312',
                'branch' => 'Treasury Square Branch',
                'sort_code' => '02-005',
                'basic_salary' => 235000,
                'house_allowance' => 38000,
                'commuter_allowance' => 15000,
                'responsibility_allowance' => 15000,
                'sacco_deduction' => 7500,
                'joined' => '10 Feb 2020',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-035' => [
                'id' => 'EMP-2026-035',
                'name' => 'Prof. Peter Omwenga',
                'email' => 'p.omwenga@mema.ac.ke',
                'phone' => '+254 745 678 901',
                'designation' => 'Associate Professor of Electrical Engineering',
                'school' => 'School of Engineering',
                'department' => 'Electrical & Electronics Eng.',
                'type' => 'Permanent',
                'rank' => 'Associate Professor',
                'grade' => 'Grade 14 (Associate Professor)',
                'id_no' => '23119876',
                'kra_pin' => 'A006543210W',
                'nssf_no' => 'NSSF-65432109',
                'nhif_no' => 'SHA-65432109',
                'bank_name' => 'Absa Bank Kenya PLC',
                'account_no' => '03099183044',
                'branch' => 'Digo Road Branch',
                'sort_code' => '03-014',
                'basic_salary' => 290000,
                'house_allowance' => 45000,
                'commuter_allowance' => 20000,
                'responsibility_allowance' => 15000,
                'sacco_deduction' => 9000,
                'joined' => '15 Jul 2026',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-048' => [
                'id' => 'EMP-2026-048',
                'name' => 'Dr. Beatrice Achieng',
                'email' => 'b.achieng@mema.ac.ke',
                'phone' => '+254 756 789 012',
                'designation' => 'Senior Lecturer in Accounting & Finance',
                'school' => 'School of Business & Economics',
                'department' => 'Accounting & Finance',
                'type' => '3-Year Contract',
                'rank' => 'Senior Lecturer',
                'grade' => 'Grade 13 (Senior Lecturer)',
                'id_no' => '25987123',
                'kra_pin' => 'A005432109V',
                'nssf_no' => 'NSSF-54321098',
                'nhif_no' => 'SHA-54321098',
                'bank_name' => 'Co-operative Bank of Kenya',
                'account_no' => '011293847718',
                'branch' => 'Nkrumah Road Branch',
                'sort_code' => '11-042',
                'basic_salary' => 220000,
                'house_allowance' => 35000,
                'commuter_allowance' => 12000,
                'responsibility_allowance' => 8000,
                'sacco_deduction' => 6000,
                'joined' => '01 Jan 2024',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-059' => [
                'id' => 'EMP-2026-059',
                'name' => 'Dr. Samuel Kipchumba',
                'email' => 's.kipchumba@mema.ac.ke',
                'phone' => '+254 767 890 123',
                'designation' => 'Lecturer in Public Health & Epidemiology',
                'school' => 'School of Health Sciences',
                'department' => 'Public Health & Epidemiology',
                'type' => '3-Year Contract',
                'rank' => 'Lecturer',
                'grade' => 'Grade 12 (Lecturer)',
                'id_no' => '29881726',
                'kra_pin' => 'A004321098U',
                'nssf_no' => 'NSSF-43210987',
                'nhif_no' => 'SHA-43210987',
                'bank_name' => 'NCBA Bank Kenya',
                'account_no' => '10293846011',
                'branch' => 'Nyali City Mall Branch',
                'sort_code' => '07-018',
                'basic_salary' => 180000,
                'house_allowance' => 30000,
                'commuter_allowance' => 12000,
                'responsibility_allowance' => 5000,
                'sacco_deduction' => 5000,
                'joined' => '01 Jul 2026',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-070' => [
                'id' => 'EMP-2026-070',
                'name' => 'Faith Muthoni',
                'email' => 'f.muthoni@mema.ac.ke',
                'phone' => '+254 778 901 234',
                'designation' => 'Senior HR Operations Officer',
                'school' => 'Central Administration',
                'department' => 'Human Resources Department',
                'type' => 'Permanent',
                'rank' => 'Administrative Officer',
                'grade' => 'Grade 11 (Senior Officer)',
                'id_no' => '30192847',
                'kra_pin' => 'A003210987T',
                'nssf_no' => 'NSSF-32109876',
                'nhif_no' => 'SHA-32109876',
                'bank_name' => 'Stanbic Bank Kenya',
                'account_no' => '010029385120',
                'branch' => 'Moi Avenue Branch',
                'sort_code' => '31-002',
                'basic_salary' => 140000,
                'house_allowance' => 25000,
                'commuter_allowance' => 10000,
                'responsibility_allowance' => 0,
                'sacco_deduction' => 4500,
                'joined' => '10 Jul 2026',
                'status' => 'ACTIVE',
            ],
            'EMP-2026-088' => [
                'id' => 'EMP-2026-088',
                'name' => 'James Maina',
                'email' => 'j.maina@mema.ac.ke',
                'phone' => '+254 789 012 345',
                'designation' => 'Senior Laboratory Technologist',
                'school' => 'School of Engineering',
                'department' => 'Mechanical Engineering',
                'type' => 'Permanent',
                'rank' => 'Technical Staff',
                'grade' => 'Grade 9 (Senior Technologist)',
                'id_no' => '31298471',
                'kra_pin' => 'A002109876S',
                'nssf_no' => 'NSSF-21098765',
                'nhif_no' => 'SHA-21098765',
                'bank_name' => 'Family Bank Kenya',
                'account_no' => '048291048190',
                'branch' => 'Mwembe Tayari Branch',
                'sort_code' => '70-008',
                'basic_salary' => 95000,
                'house_allowance' => 18000,
                'commuter_allowance' => 8000,
                'responsibility_allowance' => 0,
                'sacco_deduction' => 3000,
                'joined' => '15 Mar 2021',
                'status' => 'ACTIVE',
            ],
        ];
    }

    /**
     * Compute Real Kenyan Statutory Deductions & Payslip Breakdown
     */
    private function calculatePayslipData(array $staff, string $month = 'August', string $year = '2026'): array
    {
        $basic = (float) $staff['basic_salary'];
        $house = (float) $staff['house_allowance'];
        $commuter = (float) $staff['commuter_allowance'];
        $resp = (float) $staff['responsibility_allowance'];
        $gross = $basic + $house + $commuter + $resp;

        // NSSF Tier 1 (up to KES 7,000) & Tier 2 (up to KES 36,000)
        $nssfTier1 = (int) min(420, round($gross * 0.06));
        $nssfTier2 = (int) max(0, min(1740, round(($gross - 7000) * 0.06)));
        $totalNssf = $nssfTier1 + $nssfTier2;

        // Taxable pay
        $taxable = $gross - $totalNssf;

        // PAYE Tax Bands (Kenya Income Tax Act Cap 470)
        $grossTax = 0.0;
        if ($taxable <= 24000) {
            $grossTax = $taxable * 0.10;
        } elseif ($taxable <= 32333) {
            $grossTax = (24000 * 0.10) + (($taxable - 24000) * 0.25);
        } elseif ($taxable <= 500000) {
            $grossTax = (24000 * 0.10) + (8333 * 0.25) + (($taxable - 32333) * 0.30);
        } elseif ($taxable <= 800000) {
            $grossTax = (24000 * 0.10) + (8333 * 0.25) + (467667 * 0.30) + (($taxable - 500000) * 0.325);
        } else {
            $grossTax = (24000 * 0.10) + (8333 * 0.25) + (467667 * 0.30) + (300000 * 0.325) + (($taxable - 800000) * 0.35);
        }

        // Statutory Reliefs
        $personalRelief = 2400.0;
        $sha = round($gross * 0.0275, 2); // 2.75% Social Health Authority (SHA)
        $insuranceRelief = round(min(5000, $sha * 0.15), 2); // 15% of health insurance contribution
        $housingLevy = round($gross * 0.015, 2); // 1.5% Affordable Housing Levy
        $housingRelief = round($housingLevy * 0.15, 2); // 15% housing relief

        $totalRelief = $personalRelief + $insuranceRelief + $housingRelief;
        $netPaye = max(0, round($grossTax - $totalRelief, 2));

        $sacco = (float) ($staff['sacco_deduction'] ?? 0);
        $totalDeductions = round($netPaye + $totalNssf + $sha + $housingLevy + $sacco, 2);
        $netPay = round($gross - $totalDeductions, 2);

        $monthIndex = match (strtolower($month)) {
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, default => 12,
        };

        // YTD Accumulators
        $ytdGross = $gross * $monthIndex;
        $ytdTaxable = $taxable * $monthIndex;
        $ytdPaye = $netPaye * $monthIndex;
        $ytdNssf = $totalNssf * $monthIndex;
        $ytdSha = $sha * $monthIndex;
        $ytdNetPay = $netPay * $monthIndex;

        $words = $this->numberToWords((int) floor($netPay)).' Shillings';
        $cents = (int) round(($netPay - floor($netPay)) * 100);
        if ($cents > 0) {
            $words .= ' and '.$cents.'/100 Cents';
        }
        $words .= ' Only';

        return [
            'payslip_no' => 'PAY-'.$year.'-'.str_pad((string) $monthIndex, 2, '0', STR_PAD_LEFT).'-'.substr($staff['id'], -3),
            'pay_period' => $month.' '.$year,
            'pay_date' => '28 '.$month.' '.$year,
            'month' => $month,
            'year' => $year,
            'month_index' => $monthIndex,
            'staff_id' => $staff['id'],
            'staff_name' => $staff['name'],
            'designation' => $staff['designation'],
            'department' => $staff['department'],
            'faculty' => $staff['school'],
            'id_no' => $staff['id_no'],
            'kra_pin' => $staff['kra_pin'],
            'nssf_no' => $staff['nssf_no'],
            'nhif_no' => $staff['nhif_no'],
            'job_group' => $staff['grade'],
            'bank_name' => $staff['bank_name'],
            'account_no' => $staff['account_no'],
            'branch' => $staff['branch'],
            'sort_code' => $staff['sort_code'],
            'eft_ref' => 'EFT-'.$year.str_pad((string) $monthIndex, 2, '0', STR_PAD_LEFT).'-'.strtoupper(substr(md5($staff['id'].$month), 0, 8)),
            'verification_hash' => hash('sha256', $staff['id'].$month.$year.$netPay),

            // Earnings
            'basic_salary' => $basic,
            'house_allowance' => $house,
            'commuter_allowance' => $commuter,
            'responsibility_allowance' => $resp,
            'gross_earnings' => $gross,

            // Deductions
            'taxable_pay' => $taxable,
            'gross_tax' => $grossTax,
            'personal_relief' => $personalRelief,
            'insurance_relief' => $insuranceRelief,
            'housing_relief' => $housingRelief,
            'total_relief' => $totalRelief,
            'net_tax' => $netPaye,
            'nssf_tier1' => $nssfTier1,
            'nssf_tier2' => $nssfTier2,
            'total_nssf' => $totalNssf,
            'nhif_sha' => $sha,
            'housing_levy' => $housingLevy,
            'sacco_shares' => $sacco,
            'total_deductions' => $totalDeductions,

            // Net Pay
            'net_pay' => $netPay,
            'net_pay_words' => $words,

            // YTD
            'ytd_gross' => $ytdGross,
            'ytd_taxable' => $ytdTaxable,
            'ytd_paye' => $ytdPaye,
            'ytd_nssf' => $ytdNssf,
            'ytd_sha' => $ytdSha,
            'ytd_net_pay' => $ytdNetPay,
        ];
    }

    /**
     * Convert Integer Amount to Kenyan English Currency Words
     */
    private function numberToWords(int $num): string
    {
        if ($num === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', 'Thousand', 'Million', 'Billion'];

        $words = [];
        $scaleIndex = 0;

        while ($num > 0) {
            $chunk = $num % 1000;
            if ($chunk > 0) {
                $chunkWords = [];
                $h = (int) floor($chunk / 100);
                $r = $chunk % 100;

                if ($h > 0) {
                    $chunkWords[] = $ones[$h].' Hundred';
                }

                if ($r > 0) {
                    if ($r < 20) {
                        $chunkWords[] = $ones[$r];
                    } else {
                        $t = (int) floor($r / 10);
                        $u = $r % 10;
                        $chunkWords[] = $tens[$t].($u > 0 ? ' '.$ones[$u] : '');
                    }
                }

                if ($scales[$scaleIndex] !== '') {
                    $chunkWords[] = $scales[$scaleIndex];
                }

                array_unshift($words, implode(' ', $chunkWords));
            }

            $num = (int) floor($num / 1000);
            $scaleIndex++;
        }

        return implode(', ', $words);
    }

    /**
     * 9. SMHR Reports Dashboard
     */
    public function reports(Request $request): View
    {
        $reportMetrics = [
            'activeStaff' => 148,
            'annualPayrollSpend' => 221400000,
            'totalPAYERemitted' => 47400000,
            'leaveLiabilityHours' => '1,840 Hrs',
            'overtimeSpend' => 2450000,
            'complianceRate' => '100%',
        ];

        $payrollVarianceReport = [
            ['month' => 'August 2026', 'staff_count' => 148, 'gross' => 'KES 18,450,000', 'paye' => 'KES 3,950,000', 'variance' => '+1.2%', 'reason' => '1 New Senior Lecturer joined (AI)'],
            ['month' => 'July 2026', 'staff_count' => 147, 'gross' => 'KES 18,230,000', 'paye' => 'KES 3,900,000', 'variance' => '+2.4%', 'reason' => '2 Associate Professors recruited'],
            ['month' => 'June 2026', 'staff_count' => 145, 'gross' => 'KES 17,800,000', 'paye' => 'KES 3,810,000', 'variance' => '0.0%', 'reason' => 'Regular monthly baseline'],
            ['month' => 'May 2026', 'staff_count' => 145, 'gross' => 'KES 17,800,000', 'paye' => 'KES 3,810,000', 'variance' => '+0.5%', 'reason' => 'Annual increment adjustments'],
        ];

        $statutorySchedule = [
            ['obligation' => 'KRA PAYE Tax Returns', 'authority' => 'Kenya Revenue Authority', 'frequency' => 'Monthly (9th)', 'amount' => 'KES 3,950,000', 'status' => 'FILED & REMITTED', 'ref' => 'KRA-ITAX-992014'],
            ['obligation' => 'SHA / NHIF Contributions', 'authority' => 'Social Health Authority', 'frequency' => 'Monthly (9th)', 'amount' => 'KES 420,000', 'status' => 'FILED & REMITTED', 'ref' => 'SHA-EFT-481920'],
            ['obligation' => 'NSSF Pension Tier I & II', 'authority' => 'National Social Security Fund', 'frequency' => 'Monthly (9th)', 'amount' => 'KES 312,000', 'status' => 'FILED & REMITTED', 'ref' => 'NSSF-EP-559102'],
            ['obligation' => 'Affordable Housing Levy (1.5%)', 'authority' => 'KRA Housing Fund', 'frequency' => 'Monthly (9th)', 'amount' => 'KES 276,750', 'status' => 'FILED & REMITTED', 'ref' => 'KRA-HL-882019'],
            ['obligation' => 'NITA Training Levy', 'authority' => 'National Ind. Training Authority', 'frequency' => 'Monthly (9th)', 'amount' => 'KES 7,400', 'status' => 'FILED & REMITTED', 'ref' => 'NITA-DIR-11029'],
        ];

        return view('smhr.reports', compact('reportMetrics', 'payrollVarianceReport', 'statutorySchedule'));
    }

    /**
     * 10. Staff Onboarding & Induction Pipeline
     */
    public function onboarding(Request $request): View
    {
        $onboardingStats = [
            'inProgress' => 4,
            'completedThisQuarter' => 12,
            'avgOnboardingDays' => '3.5 Days',
            'itAccountsProvisioned' => '100%',
        ];

        $candidates = [
            [
                'id' => 'ONB-2026-01',
                'name' => 'Dr. Mercy Chebet',
                'designation' => 'Senior Lecturer in Artificial Intelligence',
                'department' => 'Computer Science & Software Eng.',
                'joining_date' => '01 Aug 2026',
                'progress' => 100,
                'stage' => 'COMPLETED & ONBOARDED',
                'checklist' => [
                    'Personal & KYC Verified' => true,
                    'KRA PIN & Bank Details' => true,
                    'Academic Certificates Audited' => true,
                    'Staff ID & University Email Provisioned' => true,
                    'Department HOD Induction Completed' => true,
                ],
            ],
            [
                'id' => 'ONB-2026-02',
                'name' => 'Prof. Peter Omwenga',
                'designation' => 'Associate Professor of Electrical Engineering',
                'department' => 'Electrical & Electronics Eng.',
                'joining_date' => '15 Jul 2026',
                'progress' => 100,
                'stage' => 'COMPLETED & ONBOARDED',
                'checklist' => [
                    'Personal & KYC Verified' => true,
                    'KRA PIN & Bank Details' => true,
                    'Academic Certificates Audited' => true,
                    'Staff ID & University Email Provisioned' => true,
                    'Department HOD Induction Completed' => true,
                ],
            ],
            [
                'id' => 'ONB-2026-03',
                'name' => 'Dr. Jane Mwangi',
                'designation' => 'Senior Lecturer in Software Engineering',
                'department' => 'Computer Science',
                'joining_date' => '15 Sep 2026',
                'progress' => 60,
                'stage' => 'IT PROVISIONING & ASSETS',
                'checklist' => [
                    'Personal & KYC Verified' => true,
                    'KRA PIN & Bank Details' => true,
                    'Academic Certificates Audited' => true,
                    'Staff ID & University Email Provisioned' => false,
                    'Department HOD Induction Completed' => false,
                ],
            ],
            [
                'id' => 'ONB-2026-04',
                'name' => 'Brian Omondi',
                'designation' => 'Laboratory Technologist (Physics)',
                'department' => 'Physical Sciences',
                'joining_date' => '20 Sep 2026',
                'progress' => 40,
                'stage' => 'CREDENTIAL VERIFICATION',
                'checklist' => [
                    'Personal & KYC Verified' => true,
                    'KRA PIN & Bank Details' => true,
                    'Academic Certificates Audited' => false,
                    'Staff ID & University Email Provisioned' => false,
                    'Department HOD Induction Completed' => false,
                ],
            ],
        ];

        return view('smhr.onboarding', compact('onboardingStats', 'candidates'));
    }

    /**
     * Store Onboarding Record
     */
    public function storeOnboarding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'designation' => ['required', 'string'],
            'department' => ['required', 'string'],
            'joining_date' => ['required', 'date'],
        ]);

        return redirect()->route('smhr.onboarding')->with('success', 'New employee onboarding process initiated for '.$validated['name'].'. Welcome pack & document upload links dispatched.');
    }

    /**
     * 11. Disciplinary & HR Governance
     */
    public function disciplinaryRecords(Request $request): View
    {
        $governanceStats = [
            'totalIncidents' => 4,
            'resolved' => 3,
            'activeHearings' => 1,
            'officialCommendations' => 18,
        ];

        $records = [
            [
                'id' => 'DISC-2026-01',
                'staff_id' => 'EMP-2026-059',
                'staff_name' => 'Dr. Samuel Kipchumba',
                'dept' => 'Public Health',
                'type' => 'Official Commendation Letter',
                'category' => 'OUTSTANDING SERVICE',
                'date' => '14 Jun 2026',
                'description' => 'Awarded Vice Chancellor’s commendation for securing KES 15M USAID research grant in community health.',
                'action_taken' => 'Letter placed in permanent file & KES 100k bonus awarded.',
                'status' => 'RESOLVED / FILED',
            ],
            [
                'id' => 'DISC-2026-02',
                'staff_id' => 'EMP-2026-088',
                'staff_name' => 'James Maina (Lab Assistant)',
                'dept' => 'Mechanical Engineering',
                'type' => 'Formal Caution / First Warning',
                'category' => 'ABSENTEEISM',
                'date' => '22 May 2026',
                'description' => 'Unexcused absence during mandatory laboratory practical examination sessions.',
                'action_taken' => 'Written warning issued following departmental committee hearing. Attendance tracking active.',
                'status' => 'ON MONITORING (6 MONTHS)',
            ],
            [
                'id' => 'DISC-2026-03',
                'staff_id' => 'EMP-2026-014',
                'staff_name' => 'Dr. Mercy Chebet',
                'dept' => 'Computer Science',
                'type' => 'Research Excellence Citation',
                'category' => 'RESEARCH CITATION',
                'date' => '10 May 2026',
                'description' => 'Best Paper Award at IEEE Africa Conference on Explainable AI for Healthcare.',
                'action_taken' => 'Conferred Research Fellowship Grant & Senate commendation.',
                'status' => 'RESOLVED / FILED',
            ],
        ];

        return view('smhr.disciplinary-records', compact('governanceStats', 'records'));
    }
}
