<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReportsController extends Controller
{
    /**
     * Serves all 29 standard reports dynamically.
     */
    public function showReport(string $report): View
    {
        $reportData = $this->getReportMetadata($report);

        return view('reports.view', [
            'reportKey' => $report,
            'title' => $reportData['title'],
            'description' => $reportData['description'],
            'stats' => $reportData['stats'],
            'headers' => $reportData['headers'],
            'rows' => $reportData['rows'],
        ]);
    }

    /**
     * Super premium Advanced Analytics and Insights dashboard.
     */
    public function advancedAnalytics(Request $request): View
    {
        $stats = [
            'totalEnrollment' => 14850,
            'clearedRatio' => '91.2%',
            'retentionRate' => '98.4%',
            'graduationAccuracy' => '100% Verified',
        ];

        return view('reports.advanced-analytics', compact('stats'));
    }

    /**
     * Dictionary of reports data.
     */
    private function getReportMetadata(string $key): array
    {
        $data = [
            'application-status' => [
                'title' => 'Application Status Report',
                'description' => 'Verify student admission application statuses, shortlisted portfolios, and vetting reviews.',
                'stats' => [
                    ['label' => 'Total Applications', 'val' => '14,850'],
                    ['label' => 'Approved', 'val' => '11,200'],
                    ['label' => 'Pending Review', 'val' => '2,450'],
                    ['label' => 'Rejected', 'val' => '1,200'],
                ],
                'headers' => ['Application Ref', 'Applicant Name', 'Target Programme', 'Vetting Status', 'Date Submitted'],
                'rows' => [
                    ['APP-2027-0102', 'Brenda Chepkoech', 'BSc. Computer Science', 'Approved & Admissions Cleared', '12 Jan 2027'],
                    ['APP-2027-0103', 'Emmanuel Kiprono Mutai', 'BSc. Information Technology', 'Approved & Admissions Cleared', '13 Jan 2027'],
                    ['APP-2027-0104', 'Kelvin Mwenda Gitonga', 'BSc. Data Analytics', 'Pending Document Vetting', '15 Jan 2027'],
                ],
            ],
            'programme-applicants' => [
                'title' => 'Programme Wise Applicants',
                'description' => 'Audit the volume of student applications categorized by specific degree programs and streams.',
                'stats' => [
                    ['label' => 'Active Programs', 'val' => '18'],
                    ['label' => 'SST Capacity', 'val' => '800'],
                    ['label' => 'Business Capacity', 'val' => '1,200'],
                    ['label' => 'Total Applicants', 'val' => '14,850'],
                ],
                'headers' => ['Programme Name', 'Intake Stream', 'Cohort Tag', 'Applicants Registered', 'Status'],
                'rows' => [
                    ['Bachelor of Science in Computer Science', 'September Intake', 'COH-2024-SEP-MAIN', '840 Candidates', 'Capacity Locked'],
                    ['PhD in Computer Science', 'January Intake', 'COH-2025-JAN-INT', '45 Candidates', 'Active Stream'],
                    ['BSc. Information Technology', 'September Intake', 'COH-2024-SEP-MAIN', '560 Candidates', 'Active Stream'],
                ],
            ],
            'registration-report' => [
                'title' => 'Registration Report',
                'description' => 'Consolidated log of trimester student registration activations, cohort check-ins, and timelines.',
                'stats' => [
                    ['label' => 'Registered Today', 'val' => '112'],
                    ['label' => 'Registered Trimester', 'val' => '12,110'],
                    ['label' => 'Late Registration', 'val' => '340'],
                    ['label' => 'Exempted Status', 'val' => '84'],
                ],
                'headers' => ['Reg Number', 'Student Name', 'Cohort Tag', 'Academic Year', 'Registration Date'],
                'rows' => [
                    ['MEMA/BCS/2024/0912', 'Brenda Chepkoech', 'COH-2024-SEP-MAIN', '2026/2027', '28 Aug 2026'],
                    ['MEMA/BIT/2023/1104', 'Emmanuel Kiprono Mutai', 'COH-2024-SEP-MAIN', '2026/2027', '28 Aug 2026'],
                    ['MEMA/BSC/2023/0744', 'Kelvin Mwenda Gitonga', 'COH-2025-JAN-INT', '2026/2027', '29 Aug 2026'],
                ],
            ],
            'gender-wise-list' => [
                'title' => 'Gender Wise List',
                'description' => 'Comprehensive gender diversity ratios across cohorts, schools, and programs.',
                'stats' => [
                    ['label' => 'Total Students', 'val' => '14,850'],
                    ['label' => 'Male Scholars', 'val' => '8,910 (60%)'],
                    ['label' => 'Female Scholars', 'val' => '5,940 (40%)'],
                    ['label' => 'STEM Gender Ratio', 'val' => '1.5 : 1'],
                ],
                'headers' => ['Reg Number', 'Full Student Name', 'Gender', 'Accredited Programme', 'Cohort Tag'],
                'rows' => [
                    ['MEMA/BCS/2024/0912', 'Brenda Chepkoech', 'Female', 'BSc. Computer Science', 'COH-2024-SEP-MAIN'],
                    ['MEMA/BIT/2023/1104', 'Emmanuel Kiprono Mutai', 'Male', 'BSc. Information Technology', 'COH-2024-SEP-MAIN'],
                    ['MEMA/BSC/2023/0744', 'Kelvin Mwenda Gitonga', 'Male', 'BSc. Data Analytics', 'COH-2025-JAN-INT'],
                ],
            ],
            'dynamic-report' => [
                'title' => 'Dynamic Report Builder',
                'description' => 'Customized query builder and schema designer logs for ad-hoc institutional reporting.',
                'stats' => [
                    ['label' => 'Saved Templates', 'val' => '14'],
                    ['label' => 'Custom Runs Today', 'val' => '42'],
                    ['label' => 'Data Export Volume', 'val' => '850 MB'],
                    ['label' => 'System Load Ratio', 'val' => 'Normal'],
                ],
                'headers' => ['Query ID', 'Custom Report Title', 'Created By staff', 'Target Database Schema', 'Last Executed'],
                'rows' => [
                    ['QRY-891044', 'SST Scholarship Beneficiaries List', 'Dr. Amina Hassan', 'Student + Fee Source Ledger', '29-08-2026 08:12'],
                    ['QRY-891045', 'PhD Thesis Defense Overdue Alert list', 'Registrar Academic Office', 'Postgraduate Thesis Registry', '29-08-2026 08:30'],
                ],
            ],
            'dynamic-payment' => [
                'title' => 'Dynamic Payment Report',
                'description' => 'Cross-channel payments auditing ledger matching direct bank uploads, M-Pesa push notifications, and cash accounts.',
                'stats' => [
                    ['label' => 'Total Payment Logs', 'val' => '18,450'],
                    ['label' => 'Bank IPN Cleared', 'val' => '12,800'],
                    ['label' => 'M-Pesa Cleared', 'val' => '5,650'],
                    ['label' => 'Suspense Ledger', 'val' => 'KES 420,000'],
                ],
                'headers' => ['Receipt ID', 'Student Beneficiary', 'Amount Paid', 'Payment Channel Mode', 'Transaction Timestamp'],
                'rows' => [
                    ['REC-2027-0912', 'Brenda Chepkoech (MEMA/BCS/2024/0912)', 'KES 53,005', 'Equity Bank Direct IPN', '28-08-2026 15:30'],
                    ['REC-2027-0913', 'Emmanuel Kiprono Mutai (MEMA/BIT/2023/1104)', 'KES 45,000', 'Safaricom M-Pesa Paybill', '29-08-2026 06:10'],
                ],
            ],
            'student-fee-statement' => [
                'title' => 'Student Fee Statement',
                'description' => 'Tuition statements, ledger debits/credits balance mapping, and clearance certificates status.',
                'stats' => [
                    ['label' => 'Active Invoices', 'val' => '14,850'],
                    ['label' => 'Cleared Statements', 'val' => '13,510'],
                    ['label' => 'Partially Paid', 'val' => '1,210'],
                    ['label' => 'Zero-Activity Accounts', 'val' => '130'],
                ],
                'headers' => ['Student Roster', 'Trimester Billing', 'Net Amount Paid', 'Outstanding Arrears Balance', 'Clearance Tag'],
                'rows' => [
                    ['Brenda Chepkoech (MEMA/BCS/2024/0912)', 'KES 53,005', 'KES 53,005', 'KES 0', 'Cleared (100%)'],
                    ['Emmanuel Kiprono Mutai (MEMA/BIT/2023/1104)', 'KES 53,005', 'KES 45,000', 'KES 8,005', 'Partially Cleared (84.8%)'],
                ],
            ],
            'user-details' => [
                'title' => 'User Details Report',
                'description' => 'Manage system profiles metadata, assigned departments, active email channels, and role classifications.',
                'stats' => [
                    ['label' => 'Total ERP Users', 'val' => '15,034'],
                    ['label' => 'Staff Accounts', 'val' => '184'],
                    ['label' => 'Active Students', 'val' => '14,850'],
                    ['label' => 'System Admins', 'val' => '4'],
                ],
                'headers' => ['User Account Code', 'Full Name & Email', 'Role Category', 'Linked Department/School', 'Last Profile Edit'],
                'rows' => [
                    ['MEMA-STAFF-0112', 'Dr. Amina Hassan (amina@mema.ac.ke)', 'Executive Dean SST', 'School of Science & Technology', '24 Aug 2026'],
                    ['MEMA-STUD-0912', 'Brenda Chepkoech (brenda@student.mema.ac.ke)', 'Student Roster', 'Department of Computer Science', '12 Sep 2024'],
                ],
            ],
            'nominal-roll' => [
                'title' => 'Nominal Roll',
                'description' => 'The official nominal roll roster of active students checked-in for the current trimester cohort.',
                'stats' => [
                    ['label' => 'Nominal Roll Size', 'val' => '14,850'],
                    ['label' => 'On-Campus Mode', 'val' => '12,450'],
                    ['label' => 'Distance Learning', 'val' => '2,400'],
                    ['label' => 'Roll Checked-in Rate', 'val' => '98.5%'],
                ],
                'headers' => ['Reg Number', 'Full Student Name', 'Academic Cohort Tag', 'Study Mode Channel', 'Check-In Status'],
                'rows' => [
                    ['MEMA/BCS/2024/0912', 'Brenda Chepkoech', 'COH-2024-SEP-MAIN', 'Full-Time On-Campus', 'Active Roll Checked-in'],
                    ['MEMA/BIT/2023/1104', 'Emmanuel Kiprono Mutai', 'COH-2024-SEP-MAIN', 'Full-Time On-Campus', 'Active Roll Checked-in'],
                    ['MEMA/BSC/2023/0744', 'Kelvin Mwenda Gitonga', 'COH-2025-JAN-INT', 'Part-Time Distance', 'Active Roll Checked-in'],
                ],
            ],
            'student-registration-details' => [
                'title' => 'Student Registration Details',
                'description' => 'Audit biographical indices, emergency contacts, primary funding sources, and high school placement details.',
                'stats' => [
                    ['label' => 'Incomplete Profiles', 'val' => '14'],
                    ['label' => 'Next of Kin Mapped', 'val' => '14,836'],
                    ['label' => 'Biometric Enrolled', 'val' => '100% Complete'],
                    ['label' => 'Audit Standing', 'val' => 'Compliant'],
                ],
                'headers' => ['Reg Number & Name', 'Active Student Contact', 'Primary Sponsor Source', 'Emergency Kin Name', 'Biometric status'],
                'rows' => [
                    ['MEMA/BCS/2024/0912 - Brenda Chepkoech', '+254 712 345 678', 'Self-Sponsored / Private', 'Gideon Chepkoech (Father)', 'Enrolled & Verified'],
                    ['MEMA/BIT/2023/1104 - Emmanuel Kiprono Mutai', '+254 723 456 789', 'HELB Government Scheme', 'Richard Mutai (Uncle)', 'Enrolled & Verified'],
                ],
            ],
            'course-registration' => [
                'title' => 'Course Registration Report',
                'description' => 'Course unit registration audits, unit workload limits validations, and academic trimester card activations.',
                'stats' => [
                    ['label' => 'Total Registered Units', 'val' => '74,250'],
                    ['label' => 'Average Units/Student', 'val' => '5.2 Units'],
                    ['label' => 'Max Unit Overload Requests', 'val' => '32'],
                    ['label' => 'Roster Confirmed Rate', 'val' => '97.2%'],
                ],
                'headers' => ['Reg Number & Student Name', 'Registered Units Count', 'Trimester Stage Load', 'Date Registry Confirmed', 'Registration Status'],
                'rows' => [
                    ['MEMA/BCS/2024/0912 - Brenda Chepkoech', '6 Course Units Mapped', 'Semester 1 (18 Credits)', '28 Aug 2026', 'Registry Confirmed'],
                    ['MEMA/BIT/2023/1104 - Emmanuel Kiprono Mutai', '5 Course Units Mapped', 'Semester 1 (15 Credits)', '28 Aug 2026', 'Registry Confirmed'],
                ],
            ],
            'exemption-report' => [
                'title' => 'Exemption Report',
                'description' => 'Log of accredited course exemptions granted based on prior university transfers or diplomas.',
                'stats' => [
                    ['label' => 'Exemptions Logged', 'val' => '84'],
                    ['label' => 'Senate Moderated', 'val' => '84'],
                    ['label' => 'Pending Dean Vetting', 'val' => '0'],
                    ['label' => 'Exemption Revenue', 'val' => 'KES 420,000'],
                ],
                'headers' => ['Reg Number & Student Name', 'Exempted Unit Code', 'Exempted Unit Title', 'Moderation Grade Approved', 'Clearance Date'],
                'rows' => [
                    ['MEMA/BCS/2024/0912 - Brenda Chepkoech', 'CS-101', 'Introduction to Computing', 'Credit Transferred (Grade B equivalency)', '28 Aug 2026'],
                ],
            ],
            'reattempt-report' => [
                'title' => 'Re-Attempt(s) Report',
                'description' => 'Supplementary and retake examinations tracking ledger for failing progression grades.',
                'stats' => [
                    ['label' => 'Supplementary Orders', 'val' => '412'],
                    ['label' => 'Retakes Registered', 'val' => '120'],
                    ['label' => 'Pending Assessments', 'val' => '532'],
                    ['label' => 'Cleared Re-attempts', 'val' => '390'],
                ],
                'headers' => ['Reg Number & Student Name', 'Unit Code Ref', 'Unit Title', 'Re-Attempt Type', 'Assessments Ledger Status'],
                'rows' => [
                    ['MEMA/BSC/2023/0744 - Kelvin Mwenda Gitonga', 'CS-202', 'Data Structures & Algorithms', 'Supplementary Exam Order', 'Awaiting Examination Block'],
                ],
            ],
            'cohort-curriculum-mapping' => [
                'title' => 'Cohort Curriculum Mapping Report',
                'description' => 'Mapping of specific student cohorts to active curriculum guides and syllabus models approved by Senate.',
                'stats' => [
                    ['label' => 'Active Curriculums', 'val' => '12'],
                    ['label' => 'Senate Approved Mapping', 'val' => '12'],
                    ['label' => 'Pending Alignment', 'val' => '0'],
                    ['label' => 'Syllabus Version Control', 'v2.4.1 Active'],
                ],
                'headers' => ['Target Cohort Tag', 'Linked Programme Roster', 'Mapping Config ID', 'Syllabus Guide Version', 'Status'],
                'rows' => [
                    ['COH-2024-SEP-MAIN', 'Bachelor of Science in Computer Science', 'MAP-CS-2024-V2', 'Syllabus Guide 2024 v2.1', 'Approved & Locked'],
                    ['COH-2025-JAN-INT', 'BSc. Information Technology', 'MAP-IT-2025-V1', 'Syllabus Guide 2025 v1.0', 'Approved & Locked'],
                ],
            ],
            'audit-trail-user' => [
                'title' => 'Audit Trail by User',
                'description' => 'Security and administrative trail tracking logins, profile changes, and transaction approvals.',
                'stats' => [
                    ['label' => 'Audited Accounts', 'val' => '184 Staff'],
                    ['label' => 'Total Security Trails', 'val' => '18,450'],
                    ['label' => 'IP Exceptions Logged', 'val' => '0'],
                    ['label' => 'Ledger Integrity', 'val' => '100% Cryptographic Lock'],
                ],
                'headers' => ['Security Timestamp', 'Staff Account User', 'Administrative Action Logged', 'IP Address Source', 'Verdict Status'],
                'rows' => [
                    ['29-08-2026 07:12:14', 'Dr. Amina Hassan', 'Dean Approved & Signed VAL-2027-SST List', '192.168.1.14', 'Authorized Action Logged'],
                    ['29-08-2026 08:30:11', 'Office of Registrar Academic', 'Published Official 5th Congregation Pass List', '192.168.1.10', 'Authorized Action Logged'],
                ],
            ],
            'student-progression' => [
                'title' => 'Student Progression Report',
                'description' => 'Student progression standings, promotions, supplementaries, repeating, and de-registrations.',
                'stats' => [
                    ['label' => 'Promoted Scholars', 'val' => '14,200'],
                    ['label' => 'Supplementary Standings', 'val' => '412'],
                    ['label' => 'Failing / Repeating', 'val' => '120'],
                    ['label' => 'Total Progression Audit', 'val' => '14,850'],
                ],
                'headers' => ['Reg Number', 'Full Student Name', 'Current Academic Year', 'Cumulative CGPA', 'Progression Recommendation'],
                'rows' => [
                    ['MEMA/BCS/2024/0912', 'Brenda Chepkoech', 'Year 3 Semester 2', '3.45 CGPA', 'Promote to Year 4 / Graduand List'],
                    ['MEMA/BIT/2023/1104', 'Emmanuel Kiprono Mutai', 'Year 4 Semester 2', '3.20 CGPA', 'Promote to Year 4 / Graduand List'],
                    ['MEMA/BSC/2023/0744', 'Kelvin Mwenda Gitonga', 'Year 2 Semester 2', '1.85 CGPA', 'Supplementary ordered for CS-202'],
                ],
            ],
            'report-by-source' => [
                'title' => 'Report By Source',
                'description' => 'Disbursements audit by funding source including HELB loans, CDF bursaries, and scholarship funds.',
                'stats' => [
                    ['label' => 'Total Sponsorships', 'val' => 'KES 24,800,200'],
                    ['label' => 'HELB Schemes', 'val' => 'KES 18,450,000'],
                    ['label' => 'CDF Bursary Allocations', 'val' => 'KES 4,450,200'],
                    ['label' => 'Research Grants', 'val' => 'KES 1,900,000'],
                ],
                'headers' => ['Funding Source Sponsor', 'Total Mapped Beneficiaries', 'Total Trimester Disbursement', 'Ledger Allocation Rule', 'Status'],
                'rows' => [
                    ['Higher Education Loans Board (HELB)', '1,850 Scholars Mapped', 'KES 18,450,000', 'Batch Smart Split Allocation', 'Reconciled & Cleared'],
                    ['Constituency Development Fund (CDF)', '450 Scholars Mapped', 'KES 4,450,200', 'Voucher Code Clearance Verification', 'Reconciled & Cleared'],
                ],
            ],
            'fee-movement' => [
                'title' => 'Fee Movement Report',
                'description' => 'Audit of student accounts financial ledger entries showing balances before and after transactions.',
                'stats' => [
                    ['label' => 'Ledger Transactions Count', 'val' => '11,240'],
                    ['label' => 'Tuition Adjustments', 'val' => 'KES 1,200,000'],
                    ['label' => 'Overpayments Transfered', 'val' => 'KES 450,000'],
                    ['label' => 'Ledger Variance Balance', 'val' => 'KES 0 (Balanced)'],
                ],
                'headers' => ['Linked Student Account', 'Balance Before Transaction', 'Movement Change Value', 'Balance After Transaction', 'Change Timestamp'],
                'rows' => [
                    ['Brenda Chepkoech (MEMA/BCS/2024/0912)', 'KES 53,005 (Debit)', 'KES 53,005 (Credit)', 'KES 0 (Cleared)', '28-08-2026 15:30'],
                    ['Emmanuel Kiprono Mutai (MEMA/BIT/2023/1104)', 'KES 53,005 (Debit)', 'KES 45,000 (Credit)', 'KES 8,005 (Debit)', '29-08-2026 06:10'],
                ],
            ],
            'debtors-report' => [
                'title' => 'Debtors Report',
                'description' => 'Outstanding fee balances debtor report, tracking student arrears and billing accounts.',
                'stats' => [
                    ['label' => 'Active Debtor Accounts', 'val' => '1,340'],
                    ['label' => 'Total Arrears Value', 'val' => 'KES 66,050,000'],
                    ['label' => 'Average Arrears/Student', 'val' => 'KES 49,290'],
                    ['label' => 'Collection Action Flagged', 'val' => '84 Accounts'],
                ],
                'headers' => ['Reg Number & Student Name', 'Total Trimester Billed', 'Net Paid Amount', 'Outstanding Debtors Arrears', 'Action Status'],
                'rows' => [
                    ['MEMA/BIT/2023/1104 - Emmanuel Kiprono Mutai', 'KES 53,005', 'KES 45,000', 'KES 8,005', 'Active Reminder Issued'],
                    ['MEMA/BSC/2023/0744 - Kelvin Mwenda Gitonga', 'KES 53,005', 'KES 0', 'KES 53,005', 'Portal Check-in Blocked'],
                ],
            ],
            'fee-overpayment' => [
                'title' => 'Student Fee Overpayment Report',
                'description' => 'Tuition accounts overpaid ledger tracking credit adjustments and student refund rosters.',
                'stats' => [
                    ['label' => 'Overpaid Accounts', 'val' => '42 Accounts'],
                    ['label' => 'Total Overpaid Value', 'val' => 'KES 1,240,000'],
                    ['label' => 'Refunds Processed', 'val' => 'KES 840,000'],
                    ['label' => 'Credit Carryovers Mapped', 'val' => 'KES 400,000'],
                ],
                'headers' => ['Reg Number & Student Name', 'Cumulative Overpayment Value', 'Ledger Reference ID', 'Refund Status', 'Audit Clearance Date'],
                'rows' => [
                    ['MEMA/BCS/2024/0912 - Brenda Chepkoech', 'KES 12,000', 'LDG-OVP-891044', 'Carryover to next Trimester', '28 Aug 2026'],
                ],
            ],
            'search-student-short-courses' => [
                'title' => 'Search By Student for Short Courses',
                'description' => 'Registry list mapping short course professional developments, grades, and certification badges.',
                'stats' => [
                    ['label' => 'Short Courses Registered', 'val' => '1,420'],
                    ['label' => 'Certificates Issued', 'val' => '1,240'],
                    ['label' => 'Badges Verified', 'val' => '1,240'],
                    ['label' => 'Sponsor Clearances', 'val' => '100%'],
                ],
                'headers' => ['Reg Number & Student Name', 'Short Course Title', 'Cumulative Score / Grade', 'Enrollment Date', 'Completion badge Status'],
                'rows' => [
                    ['MEMA/BCS/2024/0912 - Brenda Chepkoech', 'Python Data Analytics Masterclass', 'Grade A (Score: 92%)', '15 May 2026', 'Completed & Badge Issued'],
                    ['MEMA/BIT/2023/1104 - Emmanuel Kiprono Mutai', 'Cloud Architecture Foundations', 'Grade B (Score: 84%)', '12 May 2026', 'Completed & Badge Issued'],
                ],
            ],
            'search-payment-source' => [
                'title' => 'Search By Payment Source',
                'description' => 'Verify voucher codes and allocation IDs by specific student payment source sponsorship.',
                'stats' => [
                    ['label' => 'Vouchers Mapped Today', 'val' => '112'],
                    ['label' => 'Corporate Contracts active', 'val' => '8'],
                    ['label' => 'Reconciliation Success Rate', 'val' => '100%'],
                    ['label' => 'Audit Clearance Date', 'val' => '29-08-2026'],
                ],
                'headers' => ['Funding Source Sponsor', 'Beneficiary Full Name', 'Reference Voucher Code', 'Allocated Value', 'Clearance Date'],
                'rows' => [
                    ['Constituency Development Fund (CDF)', 'Brenda Chepkoech', 'CDF-VOUCH-891044', 'KES 25,000', '28 Aug 2026'],
                ],
            ],
            'search-transaction-id' => [
                'title' => 'Search By Transaction ID',
                'description' => 'Verify Safaricom M-Pesa transaction reference IDs, bank slip IDs, and direct billing traces.',
                'stats' => [
                    ['label' => 'Searches Executed Today', 'val' => '84'],
                    ['label' => 'Matches Found', 'val' => '82'],
                    ['label' => 'Manual Escalations', 'val' => '2'],
                    ['label' => 'Query Engine Speed', 'val' => '12ms'],
                ],
                'headers' => ['Bank Transaction Reference ID', 'M-Pesa API push ID Reference', 'Student Registration ID', 'Transaction Value', 'Reconciliation Status'],
                'rows' => [
                    ['TRX-EQY-891044', 'QRT8913B92', 'MEMA/BCS/2024/0912', 'KES 53,005', 'Reconciled & Receipt Issued'],
                ],
            ],
            'fees-collection' => [
                'title' => 'Fees Collection Report',
                'description' => 'Trimester cash inflows summary and payment method splits daily tracking report.',
                'stats' => [
                    ['label' => 'Invoiced Total', 'val' => 'KES 748,500,200'],
                    ['label' => 'Bank direct Net', 'val' => 'KES 638,450,200'],
                    ['label' => 'M-Pesa Net', 'val' => 'KES 44,000,000'],
                    ['label' => 'Suspense Cash', 'val' => 'KES 0 (Balanced)'],
                ],
                'headers' => ['Financial Roster Group', 'Bank Direct Total Inflow', 'M-Pesa API Total Inflow', 'Total Trimester Revenue', 'Collection Date'],
                'rows' => [
                    ['School of Science & Technology', 'KES 24,150,000', 'KES 5,900,200', 'KES 30,050,200', '28 Aug 2026'],
                ],
            ],
            'fee-summary' => [
                'title' => 'Fee Summary Report',
                'description' => 'Aggregate summary of university-wide billings, receipts, and cash flow deficits.',
                'stats' => [
                    ['label' => 'Total Invoiced', 'val' => 'KES 748,500,200'],
                    ['label' => 'Total Collected', 'val' => 'KES 682,450,200'],
                    ['label' => 'Total Deficit Arrears', 'val' => 'KES 66,050,000'],
                    ['label' => 'Cleared Ratio', 'val' => '91.2% Cleared'],
                ],
                'headers' => ['Trimester Block Stage', 'Total Invoiced Value', 'Total Net Cash Collected', 'Total Outstanding Arrears', 'Collection rate'],
                'rows' => [
                    ['2026/2027 Academic Year Trimester 2', 'KES 748,500,200', 'KES 682,450,200', 'KES 66,050,000', '91.2% Net Collection Rate'],
                ],
            ],
            'student-invoices' => [
                'title' => 'Student Invoices Report',
                'description' => 'Manage invoices ledger issued to students for tuition and trimester fees.',
                'stats' => [
                    ['label' => 'Total Invoices Issued', 'val' => '14,850'],
                    ['label' => 'Settled Invoices', 'val' => '13,510'],
                    ['label' => 'Unpaid Invoices', 'val' => '1,340'],
                    ['label' => 'Voided Invoices', 'val' => '0'],
                ],
                'headers' => ['Invoice Reference ID', 'Student Full Name', 'Programme Stream', 'Billed Invoice Amount', 'Date Issued', 'Status'],
                'rows' => [
                    ['INV-2027-T2-0912', 'Brenda Chepkoech', 'BSc. Computer Science', 'KES 53,005', '20 Aug 2026', 'Invoice Settled (Paid)'],
                    ['INV-2027-T2-1104', 'Emmanuel Kiprono Mutai', 'BSc. Information Technology', 'KES 53,005', '20 Aug 2026', 'Invoice Unpaid (Arrears)'],
                ],
            ],
            'debtors-ageing-analysis' => [
                'title' => 'Debtors Ageing Analysis Report',
                'description' => 'Aged student debtors balance analysis mapping outstanding values across 30, 60, and 90+ days intervals.',
                'stats' => [
                    ['label' => 'Active Debtors count', 'val' => '1,340 Students'],
                    ['label' => 'Current (0-30 Days)', 'val' => 'KES 42,000,000'],
                    ['label' => 'Delinquent (31-60 Days)', 'val' => 'KES 18,050,000'],
                    ['label' => 'Critical (61-90+ Days)', 'val' => 'KES 6,000,000'],
                ],
                'headers' => ['Reg Number & Student Name', 'Outstanding Arrears Balance', 'Current (0-30 Days) Value', 'Delinquent (31-60 Days)', 'Critical (61-90+ Days)', 'Escalation Status'],
                'rows' => [
                    ['MEMA/BIT/2023/1104 - Emmanuel Kiprono Mutai', 'KES 8,005', 'KES 8,005', 'KES 0', 'KES 0', 'Active Reminder Issued'],
                    ['MEMA/BSC/2023/0744 - Kelvin Mwenda Gitonga', 'KES 53,005', 'KES 0', 'KES 40,000', 'KES 13,005', 'Critical Registry Hold'],
                ],
            ],
            'kuccps-students' => [
                'title' => 'KUCCPS Students placements',
                'description' => 'Admissions placements register for student intakes placed through the Kenya Universities and Colleges Central Placement Service.',
                'stats' => [
                    ['label' => 'KUCCPS Placed', 'val' => '1,850'],
                    ['label' => 'Admission Confirmed', 'val' => '1,720'],
                    ['label' => 'Unconfirmed Cohorts', 'val' => '130'],
                    ['label' => 'Verify Success Rate', 'val' => '100%'],
                ],
                'headers' => ['KUCCPS Placement Index', 'Student Full Name', 'Target Placed Programme', 'Admission Confirmation Status', 'Ingest Date'],
                'rows' => [
                    ['KUCCPS-CS-0912', 'Brenda Chepkoech', 'BSc. Computer Science', 'Admission Confirmed & Portal Active', '12 Aug 2026'],
                    ['KUCCPS-CS-1104', 'Emmanuel Kiprono Mutai', 'BSc. Information Technology', 'Admission Confirmed & Portal Active', '12 Aug 2026'],
                ],
            ],
            'student-provisional-transcripts' => [
                'title' => 'Student Provisional Transcripts Report',
                'description' => 'Manage generated student provisional academic transcripts with cumulative CGPA status.',
                'stats' => [
                    ['label' => 'Transcripts Compiled', 'val' => '14,850'],
                    ['label' => 'Provisional Prints Checked', 'val' => '12,450'],
                    ['label' => 'Awaiting Board Review', 'val' => '0'],
                    ['label' => 'System Load Ratio', 'val' => 'Optimal'],
                ],
                'headers' => ['Student Roster', 'Target Programme Stream', 'Current Academic Year', 'Cumulative CGPA Score', 'PDF Print Status'],
                'rows' => [
                    ['Brenda Chepkoech (MEMA/BCS/2024/0912)', 'BSc. Computer Science', 'Year 3 Semester 2', '3.45 CGPA', 'PDF Generated & Signed off'],
                    ['Emmanuel Kiprono Mutai (MEMA/BIT/2023/1104)', 'BSc. Information Technology', 'Year 4 Semester 2', '3.20 CGPA', 'PDF Generated & Signed off'],
                ],
            ],
        ];

        return $data[$key] ?? [
            'title' => 'System Report',
            'description' => 'Detailed MEMA ERP registry and administrative database report.',
            'stats' => [
                ['label' => 'Total Records', 'val' => '14,850'],
                ['label' => 'Verification Status', 'val' => 'Certified'],
                ['label' => 'Reconciliation Ratios', 'val' => '100%'],
                ['label' => 'System Engine Stand', 'val' => 'Operational'],
            ],
            'headers' => ['Index Ref', 'Record Name', 'Category / Group', 'Audit Verdict', 'Timestamp'],
            'rows' => [
                ['MEMA-REC-01', 'General Roster Audit', 'Academic Central', 'Certified Correct', '29-08-2026 08:30'],
            ],
        ];
    }
}
