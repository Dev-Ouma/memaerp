<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class GraduationController extends Controller
{
    /**
     * 1. Graduation Criteria
     */
    public function criteria(Request $request): View
    {
        $stats = [
            'activeRules' => 14,
            'undergradCreditMin' => 120,
            'mastersCreditMin' => 60,
            'phdThesisRequirement' => '1 Published Peer-Reviewed Journal',
        ];

        $criteria = [
            [
                'id' => 1,
                'programme' => 'Bachelor of Science in Computer Science',
                'min_credits' => '120 Credit Hours',
                'min_cgpa' => '2.00 (Pass Division)',
                'thesis_required' => 'No (Senior Project Required)',
                'clearance_nodes' => 'Finance, Library, Registrar, Department',
                'status' => 'Active Policy',
            ],
            [
                'id' => 2,
                'programme' => 'PhD in Computer Science',
                'min_credits' => '72 Credit Hours',
                'min_cgpa' => '3.00 (B Grade Average)',
                'thesis_required' => 'Yes (Senate Moderated Defense)',
                'clearance_nodes' => 'SST Dean, Library, Finance, Registrar',
                'status' => 'Active Policy',
            ],
        ];

        return view('graduation.criteria', compact('stats', 'criteria'));
    }

    /**
     * 2. Clearance Checklist
     */
    public function clearanceChecklist(Request $request): View
    {
        $stats = [
            'totalClearanceNodes' => 5,
            'autoCheckNodes' => 3,
            'manualSignatureNodes' => 2,
            'slaTargetDays' => '2 Business Days',
        ];

        $checklists = [
            [
                'id' => 1,
                'node_name' => 'Finance & Student Accounts Department',
                'check_type' => 'Automated Ledger Zero-Balance Check',
                'assigned_role' => 'Finance Officer Group',
                'requires_approval' => 'Auto-Bypass if Balance <= 0',
                'status' => 'Operational',
            ],
            [
                'id' => 2,
                'node_name' => 'University Library Registry',
                'check_type' => 'Manual Book Return Check',
                'assigned_role' => 'Chief Librarian',
                'requires_approval' => 'Manual Clearance Stamp Required',
                'status' => 'Operational',
            ],
        ];

        return view('graduation.clearance-checklist', compact('stats', 'checklists'));
    }

    /**
     * 3. Finance Clearance
     */
    public function financeClearance(Request $request): View
    {
        $stats = [
            'graduatingCandidates' => 1340,
            'financiallyCleared' => 1210,
            'unclearedBalances' => 130,
            'totalBalanceOwed' => 'KES 1,420,000',
        ];

        $clearances = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science',
                'ledger_balance' => 'KES 0 (Cleared)',
                'last_payment_date' => '28 Aug 2026',
                'status' => 'Finance Cleared',
            ],
            [
                'id' => 2,
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'programme' => 'BSc. Information Technology',
                'ledger_balance' => 'KES 8,005 (Arrears)',
                'last_payment_date' => '15 Jun 2026',
                'status' => 'Uncleared Balance',
            ],
        ];

        return view('graduation.finance-clearance', compact('stats', 'clearances'));
    }

    /**
     * 4. Graduation Grade List
     */
    public function gradeList(Request $request): View
    {
        $stats = [
            'firstClassHonours' => 126,
            'secondClassUpper' => 740,
            'secondClassLower' => 420,
            'passDivision' => 54,
        ];

        $grades = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'cgpa' => '3.45',
                'classification' => 'Second Class Honours (Upper Division)',
                'grades_distribution' => 'A: 18, B: 24, C: 6, D: 0, F: 0',
                'status' => 'Senate Moderated',
            ],
            [
                'id' => 2,
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'cgpa' => '3.20',
                'classification' => 'Second Class Honours (Upper Division)',
                'grades_distribution' => 'A: 14, B: 28, C: 6, D: 0, F: 0',
                'status' => 'Senate Moderated',
            ],
        ];

        return view('graduation.grade-list', compact('stats', 'grades'));
    }

    /**
     * 5. Graduation List Generation
     */
    public function generateList(Request $request): View
    {
        $stats = [
            'candidatesProcessed' => 14850,
            'criteriaCleared' => 1340,
            'academicDeficits' => 1320,
            'financialDeficits' => 190,
        ];

        $generators = [
            [
                'id' => 1,
                'generation_run' => 'GEN-RUN-2027-01',
                'run_date' => '28 Feb 2027',
                'school' => 'School of Science & Technology',
                'cohort' => 'COH-2024-SEP-MAIN',
                'total_qualified' => '450 qualified',
                'status' => 'List Compiled',
            ],
        ];

        return view('graduation.generate-list', compact('stats', 'generators'));
    }

    /**
     * 6. Validate Graduation List
     */
    public function validateList(Request $request): View
    {
        $stats = [
            'awaitingValidation' => 3,
            'validatedByDean' => 15,
            'ratifiedBySenate' => 12,
            'totalPendingSignoff' => 2,
        ];

        $validations = [
            [
                'id' => 1,
                'validation_code' => 'VAL-2027-SST',
                'school' => 'School of Science & Technology',
                'total_candidates' => 450,
                'dean_signoff' => 'Dr. Amina Hassan (Dean Signed)',
                'registrar_audit' => 'Academic Audit Completed',
                'status' => 'Validated & Signed',
            ],
        ];

        return view('graduation.validate-list', compact('stats', 'validations'));
    }

    /**
     * 7. Publish Graduation List
     */
    public function publishList(Request $request): View
    {
        $stats = [
            'publishedPortalsCount' => 1210,
            'lastPublishTimestamp' => '29-08-2026 08:00',
            'portalAudience' => 'Public & Student Portal',
            'securityVerification' => 'Enabled (Verifiable QR)',
        ];

        $publications = [
            [
                'id' => 1,
                'publication_code' => 'PUB-GRAD-2027',
                'list_title' => 'Official 5th Graduation Congregation Pass List',
                'total_graduands' => 1340,
                'date_published' => '15 Mar 2027',
                'published_by' => 'Office of Registrar Academic',
                'status' => 'Published & Portal Active',
            ],
        ];

        return view('graduation.publish-list', compact('stats', 'publications'));
    }

    /**
     * 8. Graduation List Report
     */
    public function listReport(Request $request): View
    {
        $stats = [
            'totalReportPrints' => 142,
            'schoolWiseSlices' => 4,
            'departmentWiseSlices' => 12,
            'accuracyVerdict' => '100% Certified Correct',
        ];

        $reports = [
            [
                'id' => 1,
                'report_ref' => 'REP-GRAD-CS-Y3',
                'school' => 'School of Science & Technology',
                'department' => 'Department of Computer Science',
                'total_candidates' => 240,
                'file_format' => 'PDF / Excel Portfolio',
                'status' => 'Report Ready',
            ],
        ];

        return view('graduation.list-report', compact('stats', 'reports'));
    }

    /**
     * 9. Graduation Summary List
     */
    public function summaryList(Request $request): View
    {
        $stats = [
            'senateSummaryCode' => 'SEN-SUM-2027',
            'totalGraduands' => 1340,
            'mscPhdGraduands' => 110,
            'ugGraduands' => 1230,
        ];

        $summaries = [
            [
                'id' => 1,
                'school' => 'School of Science & Technology',
                'phd_count' => 12,
                'masters_count' => 38,
                'degree_count' => 400,
                'diploma_count' => 0,
                'total' => 450,
                'status' => 'Senate Ratified',
            ],
        ];

        return view('graduation.summary-list', compact('stats', 'summaries'));
    }

    /**
     * 10. Progressive Certification Setup
     */
    public function certificationSetup(Request $request): View
    {
        $stats = [
            'activeCertificateTemplates' => 4,
            'blockchainRegistries' => 1,
            'issuedDigitalCredentials' => 840,
            'validationSuccessRate' => '100.0%',
        ];

        $templates = [
            [
                'id' => 1,
                'template_code' => 'TMP-UG-DEGREE',
                'name' => 'Undergraduate Degree Certificate Template',
                'dimensions' => 'A4 Landscape',
                'security_features' => 'UV Pattern, Verifiable QR Code, Blockchain IPFS Hash',
                'signatories' => 'Vice Chancellor & Registrar Academic',
                'status' => 'Active System Template',
            ],
        ];

        return view('graduation.certification-setup', compact('stats', 'templates'));
    }

    /**
     * 11. Alumni Student List
     */
    public function alumniList(Request $request): View
    {
        $stats = [
            'totalAlumniRecords' => 4820,
            'promotedFromThisSession' => 1210,
            'activeAlumniPortals' => 3450,
            'donationsReconciliation' => 'Operational',
        ];

        $alumni = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science',
                'grad_year' => '2026/2027',
                'alumni_code' => 'MEMA-ALM-2027-0912',
                'contact' => 'brenda.c@alumni.mema.ac.ke',
                'status' => 'Alumni Activated',
            ],
        ];

        return view('graduation.alumni-list', compact('stats', 'alumni'));
    }

    /**
     * 12. Graduation Ceremony
     */
    public function ceremony(Request $request): View
    {
        $stats = [
            'ceremonyDate' => '18 Jun 2027',
            'venue' => 'MEMA University Sports Pavilion',
            'reservedGowns' => 1120,
            'expectedGuests' => 4500,
        ];

        $ceremonies = [
            [
                'id' => 1,
                'congregation_number' => '5th Graduation Ceremony',
                'date' => '18 Jun 2027',
                'chief_guest' => 'Cabinet Secretary, Ministry of Education',
                'gown_return_deadline' => '02 Jul 2027',
                'gown_fine_rate' => 'KES 500 per Day late',
                'status' => 'Logistics Configured',
            ],
        ];

        return view('graduation.ceremony', compact('stats', 'ceremonies'));
    }

    /**
     * 13. Graduation Ceremony Report
     */
    public function ceremonyReport(Request $request): View
    {
        $stats = [
            'totalExpenseAudited' => 'KES 4,850,000',
            'gownRevenueCollected' => 'KES 1,200,000',
            'invitationCardsDispatched' => 2680,
            'reportVerdict' => 'Approved & Filed in Archives',
        ];

        $reports = [
            [
                'id' => 1,
                'report_ref' => 'REP-GRAD-CER-05',
                'title' => 'Post-Ceremony Administrative & Financial Report - 5th Congregation',
                'audit_date' => '12 Jul 2027',
                'compiled_by' => 'Chairman, Graduation Committee',
                'senate_submission' => 'Ratified in Senate Minute 412/2027',
                'status' => 'Report Filed',
            ],
        ];

        return view('graduation.ceremony-report', compact('stats', 'reports'));
    }
}
