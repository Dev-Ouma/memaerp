<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class ImprestController extends Controller
{
    /**
     * 1. Imprest Permissions & Authority Thresholds Setup
     */
    public function permissions(Request $request): View
    {
        $stats = [
            'activeAuthorityTiers' => 6,
            'maxApprovalLimit' => 'KES 2,000,000',
            'authorizedApprovers' => 28,
            'activeVoteHeads' => 42,
        ];

        $permissions = [
            [
                'id' => 1,
                'role_title' => 'Vice Chancellor / Accounting Officer',
                'authority_level' => 'Tier 1 (Executive)',
                'min_limit' => 'KES 500,001',
                'max_limit' => 'KES 2,000,000',
                'allowed_categories' => 'Institutional Operations, Capital Projects, International Delegations',
                'mandate_rule' => 'Dual Sign-off with Finance Officer',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'role_title' => 'Deputy Vice Chancellor (Academic & Research)',
                'authority_level' => 'Tier 2 (Senior Management)',
                'min_limit' => 'KES 250,001',
                'max_limit' => 'KES 500,000',
                'allowed_categories' => 'Research Grants, PG Defense Panels, Curriculum Review Conferences',
                'mandate_rule' => 'Internal Audit Pre-clearance Required',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'role_title' => 'Executive Deans of Schools',
                'authority_level' => 'Tier 3 (Faculty Level)',
                'min_limit' => 'KES 100,001',
                'max_limit' => 'KES 250,000',
                'allowed_categories' => 'School Fieldwork, External Examiner Honoraria, Academic Workshops',
                'mandate_rule' => 'Within School Vote-head Budget Ceiling',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'role_title' => 'Heads of Academic Departments (HODs)',
                'authority_level' => 'Tier 4 (Departmental)',
                'min_limit' => 'KES 20,001',
                'max_limit' => 'KES 100,000',
                'allowed_categories' => 'Departmental Practicals, Local Travel, Moderation Meetings',
                'mandate_rule' => 'Dean Concurrence Required',
                'status' => 'Active',
            ],
            [
                'id' => 5,
                'role_title' => 'Principal Investigators (Research Grants)',
                'authority_level' => 'Tier 5 (Grant Funded)',
                'min_limit' => 'KES 50,001',
                'max_limit' => 'KES 350,000',
                'allowed_categories' => 'Grant Field Surveys, Community Engagement, Data Collection',
                'mandate_rule' => 'Direct Grant Account Charge with DVC R&I clearance',
                'status' => 'Active',
            ],
            [
                'id' => 6,
                'role_title' => 'Administrative Officers / Section Heads',
                'authority_level' => 'Tier 6 (Petty Imprest)',
                'min_limit' => 'KES 5,000',
                'max_limit' => 'KES 20,000',
                'allowed_categories' => 'Urgent Office Supplies, Courier, Emergency Meeting Welfare',
                'mandate_rule' => 'Direct Finance Accountant Clearance',
                'status' => 'Active',
            ],
        ];

        return view('imprest.permissions', compact('stats', 'permissions'));
    }

    /**
     * 2. Claim Approval Permission & Workflow Matrix
     */
    public function claimApprovals(Request $request): View
    {
        $stats = [
            'pendingClaimVetting' => 18,
            'approvedThisMonth' => 'KES 4,890,000',
            'avgProcessingSLA' => '24.5 Hours',
            'escalationRulesActive' => 8,
        ];

        $approvalMatrices = [
            [
                'id' => 1,
                'workflow_code' => 'APV-RES-01',
                'claim_category' => 'Postgraduate Research & Defense Logistics',
                'originating_unit' => 'School of Postgraduate Studies (SPGS)',
                'workflow_sequence' => 'HOD -> Dean SPGS -> Internal Audit -> Finance Officer',
                'auto_escalation_hours' => 48,
                'delegate_allowed' => 'Yes (Associate Dean)',
                'status' => 'Active Matrix',
            ],
            [
                'id' => 2,
                'workflow_code' => 'APV-ODEL-02',
                'claim_category' => 'ODeL Regional Centres & Exam Distribution',
                'originating_unit' => 'Centre for Open & Distance Learning',
                'workflow_sequence' => 'ODeL Director -> Registrar Academic -> Finance Officer -> VC',
                'auto_escalation_hours' => 24,
                'delegate_allowed' => 'No (Substantive Only)',
                'status' => 'Active Matrix',
            ],
            [
                'id' => 3,
                'workflow_code' => 'APV-ICT-03',
                'claim_category' => 'Campus Cloud Infrastructure & ERP Maintenance',
                'originating_unit' => 'ICT Services Directorate',
                'workflow_sequence' => 'ICT Director -> DVC Finance -> Internal Audit -> Finance Officer',
                'auto_escalation_hours' => 48,
                'delegate_allowed' => 'Yes (Systems Lead)',
                'status' => 'Active Matrix',
            ],
            [
                'id' => 4,
                'workflow_code' => 'APV-DEPT-04',
                'claim_category' => 'Departmental Academic Fieldtrips & Practicals',
                'originating_unit' => 'All Academic Faculties',
                'workflow_sequence' => 'Course Lecturer -> HOD -> Faculty Dean -> Finance Audit',
                'auto_escalation_hours' => 72,
                'delegate_allowed' => 'Yes (Deputy HOD)',
                'status' => 'Active Matrix',
            ],
        ];

        return view('imprest.claim-approvals', compact('stats', 'approvalMatrices'));
    }

    /**
     * 3. Imprest Surrender Permission & Strict Compliance Policies
     */
    public function surrenderPermissions(Request $request): View
    {
        $stats = [
            'surrenderGracePeriod' => '14 Calendar Days',
            'activeRecoveryTriggers' => 4,
            'complianceRate' => '94.2%',
            'outstandingOverdue' => 'KES 420,000',
        ];

        $surrenderRules = [
            [
                'id' => 1,
                'policy_code' => 'SURR-POL-01',
                'title' => 'Statutory 14-Day Post-Activity Surrender Rule',
                'timeline' => 'Within 14 calendar days of activity completion',
                'document_requirements' => 'KRA ETIMS Receipts, Boarding Passes, Attendance Registers, Back-to-Office Report',
                'non_compliance_action' => 'Automatic blockage of subsequent imprest requests',
                'waiver_authority' => 'Finance Officer only',
                'status' => 'Strict Enforced',
            ],
            [
                'id' => 2,
                'policy_code' => 'SURR-POL-02',
                'title' => 'Automatic Payroll Recovery Deduction Trigger',
                'timeline' => 'Day 30 post-deadline without approved surrender',
                'document_requirements' => 'Finance warning notice at Day 15, Internal Audit debit note at Day 25',
                'non_compliance_action' => '100% deduction from monthly staff salary payroll via ERP sync',
                'waiver_authority' => 'Vice Chancellor only',
                'status' => 'Strict Enforced',
            ],
            [
                'id' => 3,
                'policy_code' => 'SURR-POL-03',
                'title' => 'KRA ETIMS Invoice Verification Tolerance',
                'timeline' => 'At time of surrender submission',
                'document_requirements' => 'QR Code validation on all vendor receipts exceeding KES 2,000',
                'non_compliance_action' => 'Receipt rejection; unverified expenditure treated as personal cash due',
                'waiver_authority' => 'Chief Internal Auditor',
                'status' => 'Active Rule',
            ],
            [
                'id' => 4,
                'policy_code' => 'SURR-POL-04',
                'title' => 'Unspent Balance Immediate Banking Rule',
                'timeline' => 'Within 48 hours of return',
                'document_requirements' => 'Direct University Bank Deposit Slip / KCB Paybill Receipt',
                'non_compliance_action' => 'Surrender rejected until cash refund receipt attached',
                'waiver_authority' => 'Cash Office Senior Accountant',
                'status' => 'Active Rule',
            ],
        ];

        return view('imprest.surrender-permissions', compact('stats', 'surrenderRules'));
    }

    /**
     * 4. Imprest Requisitions & Applications Ledger
     */
    public function requisitions(Request $request): View
    {
        $stats = [
            'totalRequisitions' => 142,
            'approvedDisbursed' => 'KES 8,920,000',
            'pendingApprovals' => 12,
            'disbursedViaMpesa' => 'KES 3,450,000',
        ];

        $requisitions = [
            [
                'id' => 1,
                'requisition_no' => 'IMP-2027-0144',
                'applicant_name' => 'Dr. Amina Hassan',
                'department' => 'School of Science & Technology',
                'purpose' => 'Postgraduate Thesis Defense Panels & External Moderator Honoraria',
                'amount_requested' => 'KES 180,000',
                'vote_head' => 'VOTE-SST-RES-2027 (Research Logistics)',
                'disbursement_mode' => 'Bank EFT (Equity Bank)',
                'expected_activity_date' => '10 Feb 2027 - 14 Feb 2027',
                'surrender_due_date' => '28 Feb 2027',
                'status' => 'Disbursed / Open',
            ],
            [
                'id' => 2,
                'requisition_no' => 'IMP-2027-0145',
                'applicant_name' => 'Eng. Kevin Kibet',
                'department' => 'ICT Services Directorate',
                'purpose' => 'Fibre Optic Redundancy Testing & Regional Centre Inspection',
                'amount_requested' => 'KES 95,000',
                'vote_head' => 'VOTE-ICT-MAINT-2027 (Infrastructure)',
                'disbursement_mode' => 'M-Pesa B2C (0722***890)',
                'expected_activity_date' => '15 Feb 2027 - 18 Feb 2027',
                'surrender_due_date' => '04 Mar 2027',
                'status' => 'Disbursed / Open',
            ],
            [
                'id' => 3,
                'requisition_no' => 'IMP-2027-0146',
                'applicant_name' => 'Ms. Faith Mwangi',
                'department' => 'Dean of Students Directorate',
                'purpose' => 'Annual Inter-University Student Leadership Summit (Mombasa)',
                'amount_requested' => 'KES 320,000',
                'vote_head' => 'VOTE-SA-LEAD-2027 (Student Welfare)',
                'disbursement_mode' => 'Bank EFT (KCB Bank)',
                'expected_activity_date' => '20 Feb 2027 - 25 Feb 2027',
                'surrender_due_date' => '11 Mar 2027',
                'status' => 'Approved / Pending Banking',
            ],
            [
                'id' => 4,
                'requisition_no' => 'IMP-2027-0147',
                'applicant_name' => 'Dr. Daniel Otieno',
                'department' => 'School of Business & Economics',
                'purpose' => 'Economics Curriculum Stakeholder Engagement Workshop',
                'amount_requested' => 'KES 140,000',
                'vote_head' => 'VOTE-SBE-CURR-2027 (Curriculum)',
                'disbursement_mode' => 'M-Pesa B2C (0711***451)',
                'expected_activity_date' => '22 Feb 2027 - 24 Feb 2027',
                'surrender_due_date' => '10 Mar 2027',
                'status' => 'Under Audit Review',
            ],
        ];

        return view('imprest.requisitions', compact('stats', 'requisitions'));
    }

    /**
     * 5. Imprest Surrender & Expense Reconciliation
     */
    public function surrenders(Request $request): View
    {
        $stats = [
            'surrenderedThisMonth' => 'KES 6,480,000',
            'fullyReconciled' => 64,
            'pendingAuditVerification' => 9,
            'refundsRecovered' => 'KES 312,000',
        ];

        $surrenders = [
            [
                'id' => 1,
                'surrender_no' => 'SUR-2027-0091',
                'requisition_ref' => 'IMP-2027-0130',
                'staff_name' => 'Prof. Peter Ondieki',
                'department' => 'University Library Directorate',
                'imprest_amount' => 'KES 120,000',
                'actual_expenditure' => 'KES 114,500',
                'unspent_refund' => 'KES 5,500 (Banked)',
                'etims_compliance' => '100% ETIMS Validated',
                'audit_verdict' => 'Internal Audit Cleared',
                'surrender_status' => 'Fully Reconciled & Closed',
            ],
            [
                'id' => 2,
                'surrender_no' => 'SUR-2027-0092',
                'requisition_ref' => 'IMP-2027-0132',
                'staff_name' => 'Dr. Grace Njeri',
                'department' => 'School of Education',
                'imprest_amount' => 'KES 85,000',
                'actual_expenditure' => 'KES 88,200',
                'supplementary_claim' => 'KES 3,200 (Reimbursement)',
                'etims_compliance' => '100% ETIMS Validated',
                'audit_verdict' => 'Pending Finance Officer Sign-off',
                'surrender_status' => 'Under Verification',
            ],
            [
                'id' => 3,
                'surrender_no' => 'SUR-2027-0093',
                'requisition_ref' => 'IMP-2027-0135',
                'staff_name' => 'Mr. Joseph Mwangi',
                'department' => 'Finance Department',
                'imprest_amount' => 'KES 50,000',
                'actual_expenditure' => 'KES 42,000',
                'unspent_refund' => 'KES 8,000 (Awaiting Bank Slip)',
                'etims_compliance' => 'Missing 1 Taxi Receipt',
                'audit_verdict' => 'Queried by Audit',
                'surrender_status' => 'Audit Query Raised',
            ],
        ];

        return view('imprest.surrenders', compact('stats', 'surrenders'));
    }

    /**
     * 6. Imprest Audit Ledger & Aging Analysis
     */
    public function auditLedger(Request $request): View
    {
        $stats = [
            'totalActiveImprest' => 'KES 12,400,000',
            'currentNotDue' => 'KES 9,800,000 (79%)',
            'overdue1to14Days' => 'KES 2,180,000 (17%)',
            'criticalOverdueSalaryRecovery' => 'KES 420,000 (4%)',
        ];

        $agingRecords = [
            [
                'id' => 1,
                'staff_name' => 'Dr. George Omondi',
                'staff_no' => 'MEMA-STAFF-0291',
                'department' => 'School of Agriculture',
                'imprest_ref' => 'IMP-2026-0810',
                'amount_due' => 'KES 145,000',
                'issue_date' => '10 Dec 2026',
                'due_date' => '24 Dec 2026',
                'days_overdue' => 66,
                'risk_category' => 'Critical Overdue (30+ Days)',
                'recovery_status' => 'Triggered for Feb Payroll Deduction',
            ],
            [
                'id' => 2,
                'staff_name' => 'Ms. Mercy Chebet',
                'staff_no' => 'MEMA-STAFF-0344',
                'department' => 'Administration Directorate',
                'imprest_ref' => 'IMP-2027-0012',
                'amount_due' => 'KES 65,000',
                'issue_date' => '05 Jan 2027',
                'due_date' => '19 Jan 2027',
                'days_overdue' => 40,
                'risk_category' => 'Critical Overdue (30+ Days)',
                'recovery_status' => 'Final 7-Day Demand Notice Issued',
            ],
            [
                'id' => 3,
                'staff_name' => 'Dr. Wilson Koech',
                'staff_no' => 'MEMA-STAFF-0182',
                'department' => 'School of Science & Technology',
                'imprest_ref' => 'IMP-2027-0089',
                'amount_due' => 'KES 80,000',
                'issue_date' => '20 Jan 2027',
                'due_date' => '03 Feb 2027',
                'days_overdue' => 25,
                'risk_category' => 'Warning Stage (15-29 Days)',
                'recovery_status' => 'First Reminder Notice Dispatched',
            ],
            [
                'id' => 4,
                'staff_name' => 'Mr. Brian Mutiso',
                'staff_no' => 'MEMA-STAFF-0419',
                'department' => 'Estates & Facilities',
                'imprest_ref' => 'IMP-2027-0115',
                'amount_due' => 'KES 45,000',
                'issue_date' => '01 Feb 2027',
                'due_date' => '15 Feb 2027',
                'days_overdue' => 13,
                'risk_category' => 'Grace Period (1-14 Days)',
                'recovery_status' => 'Friendly Email Reminder',
            ],
        ];

        return view('imprest.audit-ledger', compact('stats', 'agingRecords'));
    }
}
