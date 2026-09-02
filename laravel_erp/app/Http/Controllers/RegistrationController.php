<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class RegistrationController extends Controller
{
    /**
     * 1. Application Verification
     */
    public function applicationVerification(Request $request): View
    {
        $stats = [
            'pendingVerification' => 48,
            'verifiedToday' => 126,
            'docAuthenticityRate' => '98.4%',
            'escalatedToKNEC' => 5,
        ];

        $applications = [
            [
                'id' => 1,
                'app_no' => 'APP-2027-0481',
                'applicant_name' => 'Wanjiku Mary Njeri',
                'programme' => 'Bachelor of Science in Computer Science',
                'school' => 'School of Science & Technology',
                'entry_qualifications' => 'KCSE 2025: Grade B+ (Mean 71 Pts)',
                'docs_status' => 'KCSE Result Slip & ID Uploaded',
                'verification_stage' => 'Academic Document Audit',
                'status' => 'Pending Verification',
            ],
            [
                'id' => 2,
                'app_no' => 'APP-2027-0482',
                'applicant_name' => 'Kipchumba Evans Rotich',
                'programme' => 'Bachelor of Business Administration',
                'school' => 'School of Business & Economics',
                'entry_qualifications' => 'KNEC Diploma in Business Mgt (Credit)',
                'docs_status' => 'Diploma Certificate & Transcripts',
                'verification_stage' => 'Credit Exemption Vetting',
                'status' => 'Pending Verification',
            ],
            [
                'id' => 3,
                'app_no' => 'APP-2027-0483',
                'applicant_name' => 'Fatuma Abdi Hassan',
                'programme' => 'Master of Data Science',
                'school' => 'School of Science & Technology',
                'entry_qualifications' => 'BSc. Applied Statistics (First Class)',
                'docs_status' => 'Degree Certificate & 2 Referees',
                'verification_stage' => 'Postgraduate Eligibility Check',
                'status' => 'Verified & Ready for Approval',
            ],
            [
                'id' => 4,
                'app_no' => 'APP-2027-0484',
                'applicant_name' => 'Onyango Kevin Otieno',
                'programme' => 'Bachelor of Education (Arts)',
                'school' => 'School of Education',
                'entry_qualifications' => 'KCSE 2024: Grade C+ (Mean 62 Pts)',
                'docs_status' => 'Certified KNEC Result Slip',
                'verification_stage' => 'Subject Cluster Check',
                'status' => 'Verified & Ready for Approval',
            ],
        ];

        return view('registration.application-verification', compact('stats', 'applications'));
    }

    /**
     * 2. Application Approval
     */
    public function applicationApproval(Request $request): View
    {
        $stats = [
            'approvedThisIntake' => 2480,
            'pendingDeanSignoff' => 34,
            'admissionLettersIssued' => 2412,
            'acceptanceFeePaid' => '89.5%',
        ];

        $approvals = [
            [
                'id' => 1,
                'app_no' => 'APP-2027-0470',
                'applicant_name' => 'Achieng Linet Odhiambo',
                'programme' => 'Bachelor of Science in Computer Science',
                'academic_year' => '2026/2027',
                'intake_session' => 'May 2027 Trimester',
                'committee_verdict' => 'Admissions Committee Approved',
                'dean_signoff' => 'Executive Dean SST Cleared',
                'letter_status' => 'Admission Letter Generated',
            ],
            [
                'id' => 2,
                'app_no' => 'APP-2027-0471',
                'applicant_name' => 'Mwangi Samuel Kamau',
                'programme' => 'Bachelor of Business Information Technology',
                'academic_year' => '2026/2027',
                'intake_session' => 'May 2027 Trimester',
                'committee_verdict' => 'Admissions Committee Approved',
                'dean_signoff' => 'Executive Dean SBE Cleared',
                'letter_status' => 'Admission Letter Generated',
            ],
            [
                'id' => 3,
                'app_no' => 'APP-2027-0472',
                'applicant_name' => 'Koech Sharon Chelangat',
                'programme' => 'Master of Data Science',
                'academic_year' => '2026/2027',
                'intake_session' => 'May 2027 Trimester',
                'committee_verdict' => 'SPGS Board Approved',
                'dean_signoff' => 'Pending Dean Sign-off',
                'letter_status' => 'Queued for Release',
            ],
        ];

        return view('registration.application-approval', compact('stats', 'approvals'));
    }

    /**
     * 3. Rejected List
     */
    public function rejectedList(Request $request): View
    {
        $stats = [
            'totalRejected' => 184,
            'clusterDeficit' => '62%',
            'incompleteDocuments' => '28%',
            'appealsLodged' => 14,
        ];

        $rejected = [
            [
                'id' => 1,
                'app_no' => 'APP-2027-0310',
                'applicant_name' => 'Kibet Hillary Kiprono',
                'programme' => 'Bachelor of Science in Computer Science',
                'rejection_reason' => 'Cluster Deficit: Mathematics Grade C (Required C+ or above)',
                'alternative_offered' => 'Diploma in Information Technology',
                'rejection_date' => '12 Feb 2027',
                'appeal_status' => 'No Appeal Lodged',
            ],
            [
                'id' => 2,
                'app_no' => 'APP-2027-0311',
                'applicant_name' => 'Nekesa Brenda Wafula',
                'programme' => 'Master of Business Administration',
                'rejection_reason' => 'Inadequate Work Experience (< 2 Years Post-Undergraduate)',
                'alternative_offered' => 'Postgraduate Diploma in Strategic Mgt',
                'rejection_date' => '14 Feb 2027',
                'appeal_status' => 'Under Appeal Review',
            ],
            [
                'id' => 3,
                'app_no' => 'APP-2027-0312',
                'applicant_name' => 'Omondi Victor Ooko',
                'programme' => 'Bachelor of Education (Science)',
                'rejection_reason' => 'Unverified KNEC Certificate (Mismatched Index Series)',
                'alternative_offered' => 'None (Fraud Escalation)',
                'rejection_date' => '16 Feb 2027',
                'appeal_status' => 'Barred from Re-application',
            ],
        ];

        return view('registration.rejected-list', compact('stats', 'rejected'));
    }

    /**
     * 4. KUCCPS Student Registration
     */
    public function kuccpsRegistration(Request $request): View
    {
        $stats = [
            'totalKuccpsPlaced' => 3200,
            'reportedRegistered' => 2890,
            'placementReportingRate' => '90.3%',
            'unclaimedSlots' => 310,
        ];

        $kuccpsRecords = [
            [
                'id' => 1,
                'kuccps_index' => '11200001045/2025',
                'student_name' => 'Kariuki Dennis Gitonga',
                'placed_programme' => 'Bachelor of Science in Computer Science',
                'gender' => 'Male',
                'county' => 'Nyeri County',
                'cluster_points' => '41.240 WCP',
                'mema_reg_no' => 'MEMA/BCS/2026/0881',
                'reporting_status' => 'Reported & Biometric Enrolled',
            ],
            [
                'id' => 2,
                'kuccps_index' => '20400012019/2025',
                'student_name' => 'Mutua Mercy Mwende',
                'placed_programme' => 'Bachelor of Business Administration',
                'gender' => 'Female',
                'county' => 'Machakos County',
                'cluster_points' => '36.850 WCP',
                'mema_reg_no' => 'MEMA/BBA/2026/0412',
                'reporting_status' => 'Reported & Biometric Enrolled',
            ],
            [
                'id' => 3,
                'kuccps_index' => '34500009112/2025',
                'student_name' => 'Kipruto Collins Cheruiyot',
                'placed_programme' => 'Bachelor of Science in Data Analytics',
                'gender' => 'Male',
                'county' => 'Uasin Gishu County',
                'cluster_points' => '38.410 WCP',
                'mema_reg_no' => 'Pending Generation',
                'reporting_status' => 'Admission Letter Downloaded',
            ],
        ];

        return view('registration.kuccps-registration', compact('stats', 'kuccpsRecords'));
    }

    /**
     * 5. Student Registrations
     */
    public function studentRegistrations(Request $request): View
    {
        $stats = [
            'totalEnrolledScholars' => 14850,
            'newIntakeRegistered' => 2480,
            'biometricCompleted' => '96.8%',
            'smartIdCardsIssued' => 13940,
        ];

        $students = [
            [
                'id' => 1,
                'reg_no' => 'MEMA/BCS/2024/0912',
                'student_name' => 'Brenda Chepkoech',
                'programme' => 'BSc. Computer Science',
                'cohort' => 'COH-2024-SEP-MAIN',
                'academic_year' => 'Year 3 Trimester 1',
                'sponsor_type' => 'Government Sponsored (KUCCPS)',
                'national_id' => '38491022',
                'registration_date' => '04 Sep 2024',
                'status' => 'Fully Registered / Active',
            ],
            [
                'id' => 2,
                'reg_no' => 'MEMA/BIT/2023/1104',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'programme' => 'BSc. Information Technology',
                'cohort' => 'COH-2023-SEP-MAIN',
                'academic_year' => 'Year 4 Trimester 1',
                'sponsor_type' => 'Self Sponsored (SSP)',
                'national_id' => '37182901',
                'registration_date' => '08 Sep 2023',
                'status' => 'Fully Registered / Active',
            ],
            [
                'id' => 3,
                'reg_no' => 'MEMA/BBA/2024/0831',
                'student_name' => 'Faith Muthoni Ndirangu',
                'programme' => 'Bachelor of Business Administration',
                'cohort' => 'COH-2024-SEP-MAIN',
                'academic_year' => 'Year 2 Trimester 2',
                'sponsor_type' => 'Government Sponsored (KUCCPS)',
                'national_id' => '39201944',
                'registration_date' => '05 Sep 2024',
                'status' => 'Fully Registered / Active',
            ],
            [
                'id' => 4,
                'reg_no' => 'MEMA/MDS/2025/0118',
                'student_name' => 'Geoffrey Mutua',
                'programme' => 'Master of Data Science',
                'cohort' => 'COH-2025-JAN-INT',
                'academic_year' => 'Year 2 Trimester 1',
                'sponsor_type' => 'Postgraduate Self-Sponsored',
                'national_id' => '30192841',
                'registration_date' => '15 Jan 2025',
                'status' => 'Fully Registered / Active',
            ],
        ];

        return view('registration.student-registrations', compact('stats', 'students'));
    }

    /**
     * 6. Course Registration and Confirmation Periods
     */
    public function courseRegistrationPeriods(Request $request): View
    {
        $stats = [
            'activeRegistrationSession' => 'Trimester II 2026/2027',
            'unitsRegisteredTotal' => 74250,
            'addDropWindowCloses' => '15 Mar 2027',
            'lateRegistrationPenalty' => 'KES 2,000 (Day 15+)',
        ];

        $periods = [
            [
                'id' => 1,
                'session_code' => 'REG-2026-TRIM2',
                'academic_session' => 'Trimester II (2026/2027 Academic Year)',
                'start_date' => '15 Jan 2027',
                'regular_deadline' => '15 Feb 2027',
                'late_registration_deadline' => '15 Mar 2027',
                'min_max_units' => 'Min: 4 Units | Max: 7 Units (28 Credit Hours)',
                'financial_gating' => 'At least 50% Tuition Cleared for Unit Pick',
                'status' => 'Add/Drop Window Active',
            ],
            [
                'id' => 2,
                'session_code' => 'REG-2026-TRIM3',
                'academic_session' => 'Trimester III (2026/2027 Summer/Executive)',
                'start_date' => '15 May 2027',
                'regular_deadline' => '15 Jun 2027',
                'late_registration_deadline' => '30 Jun 2027',
                'min_max_units' => 'Min: 3 Units | Max: 6 Units (24 Credit Hours)',
                'financial_gating' => 'At least 50% Tuition Cleared for Unit Pick',
                'status' => 'Scheduled / Upcoming',
            ],
            [
                'id' => 3,
                'session_code' => 'REG-2026-TRIM1',
                'academic_session' => 'Trimester I (2026/2027 Academic Year)',
                'start_date' => '01 Sep 2026',
                'regular_deadline' => '30 Sep 2026',
                'late_registration_deadline' => '15 Oct 2026',
                'min_max_units' => 'Min: 4 Units | Max: 7 Units (28 Credit Hours)',
                'financial_gating' => '100% Tuition Cleared for Exam Entry',
                'status' => 'Closed / Exam Completed',
            ],
        ];

        return view('registration.course-registration-periods', compact('stats', 'periods'));
    }

    /**
     * 7. Promotions & Academic Progression
     */
    public function promotions(Request $request): View
    {
        $stats = [
            'promotedToNextYear' => 12450,
            'deansListHonours' => 840,
            'academicWarning' => 210,
            'repeatYearOrders' => 45,
        ];

        $promotions = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science',
                'from_stage' => 'Year 2 Trimester 3',
                'to_stage' => 'Year 3 Trimester 1',
                'cumulative_gpa' => '3.45 (Second Upper)',
                'credits_passed' => '64 / 64 Credits',
                'promotion_verdict' => 'Promoted (Normal Progression)',
                'senate_date' => '10 Dec 2026',
            ],
            [
                'id' => 2,
                'student_name' => 'Kevin Kibet Koech',
                'reg_no' => 'MEMA/BCS/2024/0441',
                'programme' => 'BSc. Computer Science',
                'from_stage' => 'Year 2 Trimester 3',
                'to_stage' => 'Year 3 Trimester 1',
                'cumulative_gpa' => '3.82 (First Class Track)',
                'credits_passed' => '64 / 64 Credits',
                'promotion_verdict' => 'Promoted with Dean\'s List Honours',
                'senate_date' => '10 Dec 2026',
            ],
            [
                'id' => 3,
                'student_name' => 'Dennis Mutua Ochieng',
                'reg_no' => 'MEMA/BIT/2024/0119',
                'programme' => 'BSc. Information Technology',
                'from_stage' => 'Year 1 Trimester 3',
                'to_stage' => 'Year 1 Trimester 3 (Trailing)',
                'cumulative_gpa' => '1.92 (Below 2.0 Bar)',
                'credits_passed' => '48 / 64 Credits (2 Failed Units)',
                'promotion_verdict' => 'Promoted on Academic Warning (Trailing Units)',
                'senate_date' => '10 Dec 2026',
            ],
        ];

        return view('registration.promotions', compact('stats', 'promotions'));
    }

    /**
     * 8. Professional Development Courses User List
     */
    public function professionalDevelopmentUsers(Request $request): View
    {
        $stats = [
            'totalCPDEnrolled' => 480,
            'activeShortCourses' => 8,
            'certificatesAwarded' => 395,
            'corporateSponsors' => 24,
        ];

        $cpdUsers = [
            [
                'id' => 1,
                'participant_no' => 'CPD-2027-0104',
                'full_name' => 'Arch. Robert Githinji',
                'organization' => 'National Construction Authority',
                'course_enrolled' => 'Executive Certificate in Generative AI for Business Leaders',
                'completion_progress' => '100% (6/6 Modules)',
                'cpd_points_awarded' => '15 CPD Units',
                'certificate_ref' => 'CERT-MEMA-CPD-2027-0091',
                'status' => 'Certified & Completed',
            ],
            [
                'id' => 2,
                'participant_no' => 'CPD-2027-0105',
                'full_name' => 'Adv. Clara Ndung\'u',
                'organization' => 'Kenya School of Law',
                'course_enrolled' => 'Data Protection & Privacy Compliance (Kenya DPA 2019)',
                'completion_progress' => '75% (3/4 Modules)',
                'cpd_points_awarded' => 'Pending Final Exam',
                'certificate_ref' => 'In Progress',
                'status' => 'Active Learner',
            ],
            [
                'id' => 3,
                'participant_no' => 'CPD-2027-0106',
                'full_name' => 'CPA Moses Cherotich',
                'organization' => 'Bomet County Government',
                'course_enrolled' => 'Public Sector Imprest & Financial Accounting Standards',
                'completion_progress' => '100% (3/3 Modules)',
                'cpd_points_awarded' => '8 CPD Units',
                'certificate_ref' => 'CERT-MEMA-CPD-2027-0092',
                'status' => 'Certified & Completed',
            ],
        ];

        return view('registration.professional-development-users', compact('stats', 'cpdUsers'));
    }

    /**
     * 9. ERP-Moodle Course Unit Sync
     */
    public function moodleSync(Request $request): View
    {
        $stats = [
            'syncedCourseUnits' => 342,
            'syncedStudentEnrollments' => 74250,
            'syncLatency' => '0.8 Seconds',
            'moodleApiStatus' => 'Connected (REST API Token Valid)',
        ];

        $syncLogs = [
            [
                'id' => 1,
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'moodle_course_id' => 'MDL-CRS-8901',
                'enrolled_students' => 240,
                'instructor_assigned' => 'Prof. Peter Ondieki',
                'last_synced_at' => '29-08-2026 07:45:12',
                'sync_status' => '100% Synced (Zero Errors)',
            ],
            [
                'id' => 2,
                'unit_code' => 'DS-204',
                'unit_title' => 'Machine Learning & Neural Networks',
                'moodle_course_id' => 'MDL-CRS-8902',
                'enrolled_students' => 185,
                'instructor_assigned' => 'Dr. Amina Hassan',
                'last_synced_at' => '29-08-2026 07:45:14',
                'sync_status' => '100% Synced (Zero Errors)',
            ],
            [
                'id' => 3,
                'unit_code' => 'BBA-201',
                'unit_title' => 'Strategic Human Resource Management',
                'moodle_course_id' => 'MDL-CRS-8903',
                'enrolled_students' => 320,
                'instructor_assigned' => 'Dr. Daniel Otieno',
                'last_synced_at' => '29-08-2026 07:45:18',
                'sync_status' => '100% Synced (Zero Errors)',
            ],
        ];

        return view('registration.moodle-sync', compact('stats', 'syncLogs'));
    }

    /**
     * 10. Student Information Update
     */
    public function studentInfoUpdate(Request $request): View
    {
        $stats = [
            'pendingUpdateRequests' => 19,
            'approvedThisTrimester' => 142,
            'gazetteNameAlterations' => 8,
            'avgResolutionTime' => '12.4 Hours',
        ];

        $updateRequests = [
            [
                'id' => 1,
                'request_no' => 'SIU-2027-0081',
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'update_type' => 'Contact & County Change',
                'current_details' => 'Phone: 0711***992 | Kiambu County',
                'requested_details' => 'Phone: 0722***415 | Nairobi West',
                'supporting_doc' => 'Utility Bill & National ID',
                'verification_status' => 'Approved by Student Registry',
            ],
            [
                'id' => 2,
                'request_no' => 'SIU-2027-0082',
                'student_name' => 'Mercy Chebet Korir (Formerly Mercy Chebet)',
                'reg_no' => 'MEMA/BCS/2025/0312',
                'update_type' => 'Official Name Change (Marriage Gazette)',
                'current_details' => 'Mercy Chebet',
                'requested_details' => 'Mercy Chebet Korir',
                'supporting_doc' => 'Kenya Gazette Notice & Marriage Cert',
                'verification_status' => 'Pending Registrar Academic Sign-off',
            ],
            [
                'id' => 3,
                'request_no' => 'SIU-2027-0083',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'update_type' => 'Next of Kin Amendment',
                'current_details' => 'Parent: John Mutai (0722***111)',
                'requested_details' => 'Guardian: Mary Mutai (0733***222)',
                'supporting_doc' => 'Affidavit of Guardianship',
                'verification_status' => 'Approved by Student Registry',
            ],
        ];

        return view('registration.student-info-update', compact('stats', 'updateRequests'));
    }

    /**
     * 11. Reminder & Notification Engine
     */
    public function reminders(Request $request): View
    {
        $stats = [
            'activeAutomatedCampaigns' => 6,
            'smsBroadcastDelivered' => 48200,
            'emailsDelivered' => 56400,
            'deliverySuccessRate' => '99.1%',
        ];

        $campaigns = [
            [
                'id' => 1,
                'campaign_code' => 'REM-FEE-TRIM2',
                'title' => 'Trimester II Fee Payment 100% Clearance Deadline',
                'target_audience' => 'All Enrolled Students with Fee Balances > 0',
                'dispatch_channels' => 'SMS Gateway & Student Email',
                'trigger_schedule' => 'T-14 Days, T-7 Days, T-2 Days to Exam',
                'total_recipients' => 3840,
                'status' => 'Active Campaign',
            ],
            [
                'id' => 2,
                'campaign_code' => 'REM-UNIT-ADDDROP',
                'title' => 'Course Unit Add/Drop Window Closure Reminder',
                'target_audience' => 'Students with Unconfirmed/Draft Unit Selections',
                'dispatch_channels' => 'Student Portal Push & SMS',
                'trigger_schedule' => 'Every 48h until 15 Mar 2027',
                'total_recipients' => 620,
                'status' => 'Active Campaign',
            ],
            [
                'id' => 3,
                'campaign_code' => 'REM-PG-DEFENCE',
                'title' => 'Postgraduate Defense Schedule & Panel Invitations',
                'target_audience' => 'PG Scholars & Appointed Examination Readers',
                'dispatch_channels' => 'Staff Email & Scholar Email',
                'trigger_schedule' => '5 Days Prior to Viva Session',
                'total_recipients' => 45,
                'status' => 'Active Campaign',
            ],
        ];

        return view('registration.reminders', compact('stats', 'campaigns'));
    }

    /**
     * 12. User Registration
     */
    public function userRegistration(Request $request): View
    {
        $stats = [
            'totalRegisteredUsers' => 15640,
            'academicFacultyUsers' => 412,
            'adminStaffUsers' => 378,
            'activeMfaEnrolled' => '94.6%',
        ];

        $users = [
            [
                'id' => 1,
                'user_code' => 'USR-FAC-0142',
                'full_name' => 'Prof. Peter Ondieki',
                'email' => 'p.ondieki@mema.ac.ke',
                'role' => 'Professor / Dean of Science',
                'department' => 'School of Science & Technology',
                'account_status' => 'Active (2FA Enabled)',
                'last_login' => '29-08-2026 07:15',
            ],
            [
                'id' => 2,
                'user_code' => 'USR-FAC-0189',
                'full_name' => 'Dr. Amina Hassan',
                'email' => 'a.hassan@mema.ac.ke',
                'role' => 'Senior Lecturer / PG Supervisor',
                'department' => 'Department of Computer Science',
                'account_status' => 'Active (2FA Enabled)',
                'last_login' => '29-08-2026 06:40',
            ],
            [
                'id' => 3,
                'user_code' => 'USR-ADM-0051',
                'full_name' => 'Mr. Joseph Mwangi',
                'email' => 'j.mwangi@mema.ac.ke',
                'role' => 'Senior Accountant / Imprest Lead',
                'department' => 'Finance & Accounts Directorate',
                'account_status' => 'Active (2FA Enabled)',
                'last_login' => '29-08-2026 07:30',
            ],
        ];

        return view('registration.user-registration', compact('stats', 'users'));
    }

    /**
     * 13. Student Password Management
     */
    public function studentPassword(Request $request): View
    {
        $stats = [
            'studentAccounts' => 14850,
            'passwordsResetToday' => 64,
            'lockedAccounts' => 8,
            'selfServiceSuccess' => '98.2%',
        ];

        $passwordLogs = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science',
                'password_status' => 'Active / Secure',
                'last_changed' => '10 Jan 2027',
                'failed_attempts' => 0,
                'mfa_channel' => 'SMS OTP (0722***412)',
                'actions' => 'Reset / Force Expire',
            ],
            [
                'id' => 2,
                'student_name' => 'Dennis Otieno Oduor',
                'reg_no' => 'MEMA/BED/2025/0312',
                'programme' => 'Bachelor of Education (Arts)',
                'password_status' => 'Temporary Pin Issued',
                'last_changed' => '28 Feb 2027',
                'failed_attempts' => 1,
                'mfa_channel' => 'Email Token',
                'actions' => 'Send OTP SMS',
            ],
            [
                'id' => 3,
                'student_name' => 'Kelvin Mwenda Gitonga',
                'reg_no' => 'MEMA/BSC/2023/0744',
                'programme' => 'BSc. Data Analytics',
                'password_status' => 'Locked (5 Failed Attempts)',
                'last_changed' => '05 Nov 2026',
                'failed_attempts' => 5,
                'mfa_channel' => 'SMS OTP (0711***774)',
                'actions' => 'Unlock Account',
            ],
        ];

        return view('registration.student-password', compact('stats', 'passwordLogs'));
    }

    /**
     * 14. Staff Password Management
     */
    public function staffPassword(Request $request): View
    {
        $stats = [
            'totalStaffAccounts' => 790,
            'mfaEnforcedRate' => '100% Mandatory',
            'expiringIn30Days' => 42,
            'privilegedSecurityTiers' => 5,
        ];

        $staffPasswords = [
            [
                'id' => 1,
                'staff_name' => 'Prof. Peter Ondieki',
                'staff_no' => 'MEMA-STAFF-0101',
                'designation' => 'Executive Dean / Professor',
                'email' => 'p.ondieki@mema.ac.ke',
                'privilege_tier' => 'Tier 1 (Admin & Approver)',
                'password_age_days' => 45,
                'mfa_type' => 'FIDO2 / Authenticator App',
                'status' => 'Compliant (90-Day Policy)',
            ],
            [
                'id' => 2,
                'staff_name' => 'Eng. Kevin Kibet',
                'staff_no' => 'MEMA-STAFF-0144',
                'designation' => 'Director ICT Services',
                'email' => 'k.kibet@mema.ac.ke',
                'privilege_tier' => 'Super Administrator',
                'password_age_days' => 18,
                'mfa_type' => 'Hardware Key (YubiKey)',
                'status' => 'Compliant (90-Day Policy)',
            ],
            [
                'id' => 3,
                'staff_name' => 'Ms. Faith Mwangi',
                'staff_no' => 'MEMA-STAFF-0210',
                'designation' => 'Dean of Students',
                'email' => 'f.mwangi@mema.ac.ke',
                'privilege_tier' => 'Tier 2 (Faculty Head)',
                'password_age_days' => 84,
                'mfa_type' => 'Authenticator App',
                'status' => 'Password Expiring Soon (6 Days)',
            ],
        ];

        return view('registration.staff-password', compact('stats', 'staffPasswords'));
    }

    /**
     * 15. Password Reset & Audit Logs
     */
    public function passwordReset(Request $request): View
    {
        $stats = [
            'totalResetRequests' => 4820,
            'selfServiceOtpResets' => '91.4%',
            'helpdeskAssisted' => '8.6%',
            'securityBreachAttempts' => 0,
        ];

        $resetAudits = [
            [
                'id' => 1,
                'audit_no' => 'RST-2027-0941',
                'account_user' => 'Brenda Chepkoech (MEMA/BCS/2024/0912)',
                'role_type' => 'Student',
                'reset_method' => 'Self-Service Mobile OTP',
                'ip_address' => '102.219.208.12 (Safaricom ISP)',
                'timestamp' => '29-08-2026 06:12:45',
                'security_verdict' => 'Verified & Completed',
            ],
            [
                'id' => 2,
                'audit_no' => 'RST-2027-0942',
                'account_user' => 'Dr. Amina Hassan (MEMA-STAFF-0182)',
                'role_type' => 'Academic Staff',
                'reset_method' => 'Institutional Email Token',
                'ip_address' => '41.89.20.15 (University Campus WiFi)',
                'timestamp' => '28-08-2026 19:40:22',
                'security_verdict' => 'Verified & Completed',
            ],
            [
                'id' => 3,
                'audit_no' => 'RST-2027-0943',
                'account_user' => 'Brian Ochieng Okoth (MEMA/BCS/2024/0119)',
                'role_type' => 'Student',
                'reset_method' => 'ICT Helpdesk Admin Override',
                'ip_address' => '192.168.1.50 (Internal Support Desk)',
                'timestamp' => '28-08-2026 14:15:09',
                'security_verdict' => 'Helpdesk Admin Logged',
            ],
        ];

        return view('registration.password-reset', compact('stats', 'resetAudits'));
    }
}
