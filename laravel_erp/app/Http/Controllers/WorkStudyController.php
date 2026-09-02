<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkStudyController extends Controller
{
    /**
     * 1. Work Study Periods / Session Setup
     */
    public function periodSetup(Request $request): View
    {
        $stats = [
            'activeSession' => '2026/2027 Trimester II',
            'allocatedBudget' => 'KES 6,500,000',
            'hourlyRate' => 'KES 250 / Hour',
            'maxHoursPerWeek' => '15 Hours Max',
        ];

        $periods = [
            [
                'id' => 1,
                'academic_year' => '2026/2027',
                'trimester' => 'Trimester II (Jan - Apr 2027)',
                'application_start' => '10 Jan 2027',
                'application_deadline' => '31 Jan 2027',
                'total_budget' => 'KES 6,500,000',
                'committed_budget' => 'KES 4,120,000',
                'hourly_rate' => 'KES 250',
                'max_weekly_hours' => 15,
                'target_beneficiaries' => 120,
                'status' => 'Active / Open',
            ],
            [
                'id' => 2,
                'academic_year' => '2026/2027',
                'trimester' => 'Trimester I (Sep - Dec 2026)',
                'application_start' => '01 Sep 2026',
                'application_deadline' => '20 Sep 2026',
                'total_budget' => 'KES 5,800,000',
                'committed_budget' => 'KES 5,650,000',
                'hourly_rate' => 'KES 250',
                'max_weekly_hours' => 15,
                'target_beneficiaries' => 110,
                'status' => 'Completed / Closed',
            ],
            [
                'id' => 3,
                'academic_year' => '2025/2026',
                'trimester' => 'Trimester III (May - Aug 2026)',
                'application_start' => '05 May 2026',
                'application_deadline' => '25 May 2026',
                'total_budget' => 'KES 4,200,000',
                'committed_budget' => 'KES 4,180,000',
                'hourly_rate' => 'KES 220',
                'max_weekly_hours' => 15,
                'target_beneficiaries' => 85,
                'status' => 'Archived',
            ],
        ];

        return view('work-study.period-setup', compact('stats', 'periods'));
    }

    /**
     * 2. Work Study Positions & Job Requisitions
     */
    public function positions(Request $request): View
    {
        $stats = [
            'totalOpenSlots' => 86,
            'participatingDepts' => 14,
            'applicationsReceived' => 214,
            'approvedRequisitions' => 12,
        ];

        $positions = [
            [
                'id' => 1,
                'job_code' => 'WS-LIB-01',
                'title' => 'Digital Library & E-Resource Assistant',
                'department' => 'University Library Directorate',
                'supervisor' => 'Mr. Peter Ondieki (Head Librarian)',
                'slots_available' => 14,
                'slots_filled' => 10,
                'hours_per_week' => 12,
                'skills_required' => 'Cataloguing, DSpace, Koha, Student Helpdesk',
                'status' => 'Open Requisition',
            ],
            [
                'id' => 2,
                'job_code' => 'WS-ODEL-02',
                'title' => 'Virtual Campus & LMS Tech Support Assistant',
                'department' => 'Centre for ODeL & E-Learning',
                'supervisor' => 'Dr. Amina Hassan (ODeL Director)',
                'slots_available' => 18,
                'slots_filled' => 18,
                'hours_per_week' => 15,
                'skills_required' => 'Moodle LMS, Zoom Webinar Host, Ticket Resolution',
                'status' => 'Slots Filled',
            ],
            [
                'id' => 3,
                'job_code' => 'WS-SA-03',
                'title' => 'Student Affairs & Welfare Peer Counsellor Aide',
                'department' => 'Dean of Students Directorate',
                'supervisor' => 'Ms. Faith Mwangi (Dean of Students)',
                'slots_available' => 8,
                'slots_filled' => 6,
                'hours_per_week' => 10,
                'skills_required' => 'Peer Counselling, Records, Event Coordination',
                'status' => 'Open Requisition',
            ],
            [
                'id' => 4,
                'job_code' => 'WS-ICT-04',
                'title' => 'Campus Helpdesk & Hardware Support Aide',
                'department' => 'ICT Services Directorate',
                'supervisor' => 'Eng. Kevin Kibet (Systems Admin)',
                'slots_available' => 12,
                'slots_filled' => 8,
                'hours_per_week' => 15,
                'skills_required' => 'PC Hardware, LAN Troubleshooting, Active Directory',
                'status' => 'Open Requisition',
            ],
            [
                'id' => 5,
                'job_code' => 'WS-FIN-05',
                'title' => 'Student Fees Reconciliation & Data Clerk',
                'department' => 'Finance & Accounts Department',
                'supervisor' => 'Mr. Joseph Mwangi (Senior Accountant)',
                'slots_available' => 6,
                'slots_filled' => 4,
                'hours_per_week' => 10,
                'skills_required' => 'Excel, Bank Statement Recon, M-Pesa IPN Matching',
                'status' => 'Open Requisition',
            ],
            [
                'id' => 6,
                'job_code' => 'WS-EXAM-06',
                'title' => 'Examination Logistics & Packing Assistant',
                'department' => 'Examinations & Timetabling Office',
                'supervisor' => 'Dr. David Otieno (Registrar Academics)',
                'slots_available' => 10,
                'slots_filled' => 10,
                'hours_per_week' => 12,
                'skills_required' => 'Confidential Handling, Barcode Scanning, Audit',
                'status' => 'Slots Filled',
            ],
        ];

        return view('work-study.positions', compact('stats', 'positions'));
    }

    /**
     * 3. Student Applications & Need Assessment
     */
    public function applications(Request $request): View
    {
        $stats = [
            'totalApplicants' => 214,
            'vettedEligible' => 142,
            'pendingVetting' => 48,
            'rejectedCriteria' => 24,
        ];

        $applications = [
            [
                'id' => 1,
                'app_no' => 'WSA-2027-0041',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science (Year 3)',
                'current_gpa' => '3.45 (Second Upper)',
                'need_category' => 'Total Orphan / Level 5 HEF Band',
                'fee_arrears' => 'KES 48,500',
                'preferred_role' => 'Virtual Campus Tech Support',
                'socio_economic_score' => '94 / 100',
                'vetting_status' => 'Vetted & Approved',
            ],
            [
                'id' => 2,
                'app_no' => 'WSA-2027-0042',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'programme' => 'BSc. Information Technology (Year 4)',
                'current_gpa' => '3.20 (Second Upper)',
                'need_category' => 'Single Parent / Marginalized County',
                'fee_arrears' => 'KES 36,000',
                'preferred_role' => 'Campus Helpdesk & Hardware Aide',
                'socio_economic_score' => '88 / 100',
                'vetting_status' => 'Vetted & Approved',
            ],
            [
                'id' => 3,
                'app_no' => 'WSA-2027-0043',
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'programme' => 'Bachelor of Business Administration (Year 2)',
                'current_gpa' => '2.95 (Second Lower)',
                'need_category' => 'Student with Disability (PWD)',
                'fee_arrears' => 'KES 22,000',
                'preferred_role' => 'Digital Library Assistant',
                'socio_economic_score' => '91 / 100',
                'vetting_status' => 'Vetted & Approved',
            ],
            [
                'id' => 4,
                'app_no' => 'WSA-2027-0044',
                'student_name' => 'Dennis Otieno Oduor',
                'reg_no' => 'MEMA/BED/2025/0312',
                'programme' => 'Bachelor of Education (Arts) (Year 1)',
                'current_gpa' => '3.10 (Second Upper)',
                'need_category' => 'Low Income Family / Urban Informal',
                'fee_arrears' => 'KES 18,500',
                'preferred_role' => 'Student Affairs Peer Aide',
                'socio_economic_score' => '76 / 100',
                'vetting_status' => 'Under Vetting',
            ],
            [
                'id' => 5,
                'app_no' => 'WSA-2027-0045',
                'student_name' => 'Kelvin Mwenda Gitonga',
                'reg_no' => 'MEMA/BSC/2023/0744',
                'programme' => 'BSc. Data Analytics (Year 4)',
                'current_gpa' => '1.85 (Pass / Below 2.0 GPA)',
                'need_category' => 'Self-Sponsored Student',
                'fee_arrears' => 'KES 14,000',
                'preferred_role' => 'Examination Assistant',
                'socio_economic_score' => '42 / 100',
                'vetting_status' => 'Disqualified (Academic Bar)',
            ],
        ];

        return view('work-study.applications', compact('stats', 'applications'));
    }

    /**
     * 4. Placement Allocation & Supervisor Assignment
     */
    public function allocations(Request $request): View
    {
        $stats = [
            'activePlacements' => 78,
            'deptsHosting' => 11,
            'averageHoursPerWeek' => '13.5 Hrs',
            'monthlyStipendVolume' => 'KES 1,170,000',
        ];

        $allocations = [
            [
                'id' => 1,
                'allocation_code' => 'WSA-PLC-0881',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'department' => 'Centre for ODeL & E-Learning',
                'assigned_position' => 'Virtual Campus Tech Support',
                'supervisor' => 'Dr. Amina Hassan',
                'approved_weekly_hours' => 15,
                'start_date' => '15 Jan 2027',
                'end_date' => '15 Apr 2027',
                'contract_status' => 'Active Contract',
            ],
            [
                'id' => 2,
                'allocation_code' => 'WSA-PLC-0882',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'department' => 'ICT Services Directorate',
                'assigned_position' => 'Campus Helpdesk & Hardware Aide',
                'supervisor' => 'Eng. Kevin Kibet',
                'approved_weekly_hours' => 15,
                'start_date' => '15 Jan 2027',
                'end_date' => '15 Apr 2027',
                'contract_status' => 'Active Contract',
            ],
            [
                'id' => 3,
                'allocation_code' => 'WSA-PLC-0883',
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'department' => 'University Library Directorate',
                'assigned_position' => 'Digital Library & E-Resource Assistant',
                'supervisor' => 'Mr. Peter Ondieki',
                'approved_weekly_hours' => 12,
                'start_date' => '15 Jan 2027',
                'end_date' => '15 Apr 2027',
                'contract_status' => 'Active Contract',
            ],
            [
                'id' => 4,
                'allocation_code' => 'WSA-PLC-0884',
                'student_name' => 'Brian Ochieng Okoth',
                'reg_no' => 'MEMA/BCS/2024/0119',
                'department' => 'Finance & Accounts Department',
                'assigned_position' => 'Student Fees Reconciliation Clerk',
                'supervisor' => 'Mr. Joseph Mwangi',
                'approved_weekly_hours' => 10,
                'start_date' => '15 Jan 2027',
                'end_date' => '15 Apr 2027',
                'contract_status' => 'Active Contract',
            ],
            [
                'id' => 5,
                'allocation_code' => 'WSA-PLC-0885',
                'student_name' => 'Cynthia Wanjiku Kimani',
                'reg_no' => 'MEMA/BBA/2025/0429',
                'department' => 'Dean of Students Directorate',
                'assigned_position' => 'Peer Counsellor Aide',
                'supervisor' => 'Ms. Faith Mwangi',
                'approved_weekly_hours' => 10,
                'start_date' => '15 Jan 2027',
                'end_date' => '15 Apr 2027',
                'contract_status' => 'Pending Dean Sign-off',
            ],
        ];

        return view('work-study.allocations', compact('stats', 'allocations'));
    }

    /**
     * 5. Timesheet Submission & Attendance Logging
     */
    public function timesheets(Request $request): View
    {
        $stats = [
            'loggedHoursThisMonth' => '3,480 Hours',
            'submittedTimesheets' => 74,
            'approvedBySupervisor' => 68,
            'pendingSupervisorApproval' => 6,
        ];

        $timesheets = [
            [
                'id' => 1,
                'timesheet_no' => 'TS-2027-02-001',
                'student_name' => 'Brenda Chepkoech',
                'department' => 'Centre for ODeL & E-Learning',
                'month_cycle' => 'February 2027',
                'hours_claimed' => 60,
                'hourly_rate' => 'KES 250',
                'total_amount' => 'KES 15,000',
                'supervisor_rating' => '5.0 / 5.0 (Exemplary)',
                'supervisor_status' => 'Supervisor Approved',
                'payment_status' => 'Batch Ready',
            ],
            [
                'id' => 2,
                'timesheet_no' => 'TS-2027-02-002',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'department' => 'ICT Services Directorate',
                'month_cycle' => 'February 2027',
                'hours_claimed' => 58,
                'hourly_rate' => 'KES 250',
                'total_amount' => 'KES 14,500',
                'supervisor_rating' => '4.8 / 5.0 (Very Good)',
                'supervisor_status' => 'Supervisor Approved',
                'payment_status' => 'Batch Ready',
            ],
            [
                'id' => 3,
                'timesheet_no' => 'TS-2027-02-003',
                'student_name' => 'Faith Muthoni Ndirangu',
                'department' => 'University Library Directorate',
                'month_cycle' => 'February 2027',
                'hours_claimed' => 48,
                'hourly_rate' => 'KES 250',
                'total_amount' => 'KES 12,000',
                'supervisor_rating' => '4.9 / 5.0 (Exemplary)',
                'supervisor_status' => 'Supervisor Approved',
                'payment_status' => 'Batch Ready',
            ],
            [
                'id' => 4,
                'timesheet_no' => 'TS-2027-02-004',
                'student_name' => 'Brian Ochieng Okoth',
                'department' => 'Finance & Accounts Department',
                'month_cycle' => 'February 2027',
                'hours_claimed' => 40,
                'hourly_rate' => 'KES 250',
                'total_amount' => 'KES 10,000',
                'supervisor_rating' => '4.6 / 5.0 (Very Good)',
                'supervisor_status' => 'Awaiting Supervisor',
                'payment_status' => 'Pending Verification',
            ],
        ];

        return view('work-study.timesheets', compact('stats', 'timesheets'));
    }

    /**
     * 6. Payment Claims & Fee Account Credit / Disbursement
     */
    public function claims(Request $request): View
    {
        $stats = [
            'totalPaidToDate' => 'KES 2,450,000',
            'tuitionCredits' => 'KES 1,715,000 (70%)',
            'mpesaDisbursements' => 'KES 735,000 (30%)',
            'pendingFinanceApproval' => 'KES 385,000',
        ];

        $claims = [
            [
                'id' => 1,
                'voucher_no' => 'WSP-2027-B02-01',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'timesheet_ref' => 'TS-2027-02-001',
                'gross_amount' => 'KES 15,000',
                'fee_account_credit' => 'KES 10,500 (70%)',
                'cash_stipend' => 'KES 4,500 (30%)',
                'disbursement_mode' => 'M-Pesa (0722***412)',
                'audit_approval' => 'Dean & Finance Cleared',
                'disbursement_status' => 'Paid / Processed',
            ],
            [
                'id' => 2,
                'voucher_no' => 'WSP-2027-B02-02',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'timesheet_ref' => 'TS-2027-02-002',
                'gross_amount' => 'KES 14,500',
                'fee_account_credit' => 'KES 10,150 (70%)',
                'cash_stipend' => 'KES 4,350 (30%)',
                'disbursement_mode' => 'M-Pesa (0711***981)',
                'audit_approval' => 'Dean & Finance Cleared',
                'disbursement_status' => 'Paid / Processed',
            ],
            [
                'id' => 3,
                'voucher_no' => 'WSP-2027-B02-03',
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'timesheet_ref' => 'TS-2027-02-003',
                'gross_amount' => 'KES 12,000',
                'fee_account_credit' => 'KES 8,400 (70%)',
                'cash_stipend' => 'KES 3,600 (30%)',
                'disbursement_mode' => 'M-Pesa (0790***553)',
                'audit_approval' => 'Internal Audit Verified',
                'disbursement_status' => 'Ready for Payment',
            ],
            [
                'id' => 4,
                'voucher_no' => 'WSP-2027-B02-04',
                'student_name' => 'Brian Ochieng Okoth',
                'reg_no' => 'MEMA/BCS/2024/0119',
                'timesheet_ref' => 'TS-2027-02-004',
                'gross_amount' => 'KES 10,000',
                'fee_account_credit' => 'KES 7,000 (70%)',
                'cash_stipend' => 'KES 3,000 (30%)',
                'disbursement_mode' => 'Bank Transfer (KCB)',
                'audit_approval' => 'Under Audit Review',
                'disbursement_status' => 'Pending Approval',
            ],
        ];

        return view('work-study.claims', compact('stats', 'claims'));
    }
}
