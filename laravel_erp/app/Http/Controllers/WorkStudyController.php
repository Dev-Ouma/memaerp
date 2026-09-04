<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OperationalRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkStudyController extends Controller
{
    public function __construct(private readonly OperationalRecordService $records) {}

    public function periodSetup(Request $request): View
    {
        return $this->records->screen($request, 'work-study.period-setup', 'student-affairs', 'period', 'periods', [
            ['key' => 'activePeriods', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'upcoming', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Upcoming'],
            ['key' => 'closed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Closed'],
            ['key' => 'budgetPool', 'op' => 'sum_money', 'field' => 'budget'],
        ], [
            ['name' => 'period_code', 'label' => 'Period code', 'required' => true],
            ['name' => 'name', 'label' => 'Period name', 'required' => true],
            ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'End date', 'type' => 'date'],
            ['name' => 'budget', 'label' => 'Budget'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function positions(Request $request): View
    {
        return $this->records->screen($request, 'work-study.positions', 'student-affairs', 'position', 'positions', [
            ['key' => 'openPositions', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'filled', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Filled'],
            ['key' => 'closed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Closed'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'position_code', 'label' => 'Position code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'slots', 'label' => 'Slots'],
            ['name' => 'hourly_rate', 'label' => 'Hourly rate'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function applications(Request $request): View
    {
        return $this->records->screen($request, 'work-study.applications', 'student-affairs', 'application', 'applications', [
            ['key' => 'submitted', 'op' => 'count'],
            ['key' => 'shortlisted', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Shortlisted'],
            ['key' => 'approved', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Approved'],
            ['key' => 'rejected', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Rejected'],
        ], [
            ['name' => 'student_name', 'label' => 'Student', 'required' => true],
            ['name' => 'reg_no', 'label' => 'Registration number'],
            ['name' => 'position_code', 'label' => 'Position'],
            ['name' => 'gpa', 'label' => 'GPA'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function allocations(Request $request): View
    {
        return $this->records->screen($request, 'work-study.allocations', 'student-affairs', 'allocation', 'allocations', [
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'completed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Completed'],
            ['key' => 'revoked', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Revoked'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'allocation_ref', 'label' => 'Allocation ref', 'required' => true],
            ['name' => 'student_name', 'label' => 'Student', 'required' => true],
            ['name' => 'position_code', 'label' => 'Position'],
            ['name' => 'hours_per_week', 'label' => 'Hours / week'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function timesheets(Request $request): View
    {
        return $this->records->screen($request, 'work-study.timesheets', 'student-affairs', 'timesheet', 'timesheets', [
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'approved', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Approved'],
            ['key' => 'rejected', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Rejected'],
            ['key' => 'hoursLogged', 'op' => 'count'],
        ], [
            ['name' => 'timesheet_ref', 'label' => 'Timesheet ref', 'required' => true],
            ['name' => 'student_name', 'label' => 'Student', 'required' => true],
            ['name' => 'hours', 'label' => 'Hours'],
            ['name' => 'week_ending', 'label' => 'Week ending', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function claims(Request $request): View
    {
        return $this->records->screen($request, 'work-study.claims', 'student-affairs', 'claim', 'claims', [
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'paid', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Paid'],
            ['key' => 'rejected', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Rejected'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'claim_ref', 'label' => 'Claim ref', 'required' => true],
            ['name' => 'student_name', 'label' => 'Student', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'period', 'label' => 'Period'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }
}
