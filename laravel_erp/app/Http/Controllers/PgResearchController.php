<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class PgResearchController extends Controller
{
    /**
     * 1. Research Eligibility & Coursework Gating (R19)
     */
    public function eligibilityGating(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'totalPostgrads' => 184,
            'fullyEligible' => 128,
            'provisionalWaivers' => 34,
            'courseworkPending' => 22,
        ];

        $candidates = [
            [
                'id' => 1,
                'student_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'degree_level' => 'PhD',
                'programme' => 'PhD in Computer Science',
                'coursework_status' => '100% Passed (8/8 Units, GPA: 3.88)',
                'fee_status' => '100% Cleared (KES 0 Balance)',
                'registration_status' => 'Active 2026/2027',
                'eligibility_verdict' => 'Fully Eligible',
                'waiver_applied' => 'No Waiver Needed',
            ],
            [
                'id' => 2,
                'student_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'degree_level' => 'Master',
                'programme' => 'Master of Data Science',
                'coursework_status' => '100% Passed (10/10 Units, GPA: 3.72)',
                'fee_status' => '100% Cleared (KES 0 Balance)',
                'registration_status' => 'Active 2026/2027',
                'eligibility_verdict' => 'Fully Eligible',
                'waiver_applied' => 'No Waiver Needed',
            ],
            [
                'id' => 3,
                'student_name' => 'Harrison Kiprono',
                'reg_no' => 'PHD-CS/2021/002',
                'degree_level' => 'PhD',
                'programme' => 'PhD in Computer Science',
                'coursework_status' => 'Pending Official Mark Release (DSC 902 Exam Sat)',
                'fee_status' => '100% Cleared (KES 0 Balance)',
                'registration_status' => 'Active 2026/2027',
                'eligibility_verdict' => 'Provisional Research Clearance',
                'waiver_applied' => 'R19 Provisional Waiver Approved (Dean Sign-off)',
            ],
            [
                'id' => 4,
                'student_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'degree_level' => 'PhD',
                'programme' => 'PhD in Economics',
                'coursework_status' => '100% Passed (8/8 Units, GPA: 3.91)',
                'fee_status' => 'Fee Balance Outstanding (KES 24,500)',
                'registration_status' => 'Provisional Registration',
                'eligibility_verdict' => 'Blocked on Fees',
                'waiver_applied' => 'No Waiver',
            ],
            [
                'id' => 5,
                'student_name' => 'Faith Jepchirchir',
                'reg_no' => 'MSC-CYB/2024/0034',
                'degree_level' => 'Master',
                'programme' => 'MSc in Cybersecurity & Forensics',
                'coursework_status' => '100% Passed (10/10 Units, GPA: 3.80)',
                'fee_status' => '100% Cleared (KES 0 Balance)',
                'registration_status' => 'Active 2026/2027',
                'eligibility_verdict' => 'Fully Eligible',
                'waiver_applied' => 'No Waiver Needed',
            ],
        ];

        return view('pg-research.eligibility-gating', compact('stats', 'candidates', 'status', 'search'));
    }

    /**
     * 2. Supervisor Allocation & Workload Distribution (R2)
     */
    public function supervisorAllocation(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'allocatedScholars' => 148,
            'unassignedScholars' => 16,
            'phdTwoSupervisorRatio' => '100% (2 Supervisors)',
            'mscOneSupervisorRatio' => '100% (1 Supervisor)',
        ];

        $allocations = [
            [
                'id' => 1,
                'student_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'degree_level' => 'PhD (Requires 2 Supervisors)',
                'programme' => 'PhD in Computer Science',
                'research_domain' => 'Federated Machine Learning & Distributed Systems',
                'supervisor_1' => 'Prof. James Mwangi (Lead / Internal)',
                'supervisor_2' => 'Dr. Amina Hassan (Co-Supervisor / Internal)',
                'optional_mentor' => 'Prof. David Ndetei (External Mentor)',
                'status' => 'Fully Assigned & Active',
            ],
            [
                'id' => 2,
                'student_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'degree_level' => 'Master (Requires 1 Supervisor)',
                'programme' => 'Master of Data Science',
                'research_domain' => 'Spatial Graph Neural Networks & Agro-Meteorology',
                'supervisor_1' => 'Dr. Amina Hassan (Primary Supervisor)',
                'supervisor_2' => 'N/A (Master\'s Policy 1 Supervisor)',
                'optional_mentor' => 'Dr. Sarah Rotich (Industry Fellow)',
                'status' => 'Fully Assigned & Active',
            ],
            [
                'id' => 3,
                'student_name' => 'Grace Wanjiku Njuguna',
                'reg_no' => 'MED/2024/0052',
                'degree_level' => 'Master (Requires 1 Supervisor)',
                'programme' => 'Master of Education in Learning Design',
                'research_domain' => 'Digital Pedagogies & Secondary STEM Curricula',
                'supervisor_1' => 'Dr. Grace Njeri (Primary Supervisor)',
                'supervisor_2' => 'N/A (Master\'s Policy 1 Supervisor)',
                'optional_mentor' => 'None',
                'status' => 'Fully Assigned & Active',
            ],
            [
                'id' => 4,
                'student_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'degree_level' => 'PhD (Requires 2 Supervisors)',
                'programme' => 'PhD in Economics',
                'research_domain' => 'Cross-Border Mobile Remittances & Macro-Policy',
                'supervisor_1' => 'Dr. Daniel Otieno (Lead / Internal)',
                'supervisor_2' => 'Pending Co-Supervisor Allocation',
                'optional_mentor' => 'None',
                'status' => 'Pending Supervisor 2 Allocation',
            ],
        ];

        return view('pg-research.supervisor-allocation', compact('stats', 'allocations', 'status', 'search'));
    }

    /**
     * 3. Supervisor Role Configuration
     */
    public function supervisorRoles(Request $request): View
    {
        $stats = [
            'totalRoles' => 6,
            'activeSupervisors' => 142,
            'activeScholars' => 486,
            'maxRatio' => '1:5 PhD / 1:8 MSc',
        ];

        $roles = [
            [
                'id' => 1,
                'role_code' => 'SUP-LEAD-01',
                'role_title' => 'Lead Doctoral Supervisor (Major Advisor)',
                'min_qualification' => 'PhD / Associate Professor or Professor',
                'max_quota' => '5 PhD Candidates',
                'sign_off_scope' => 'Concept, Proposal, Ethics, Viva, Final Submission',
                'honorarium_unit' => 'KES 45,000 / candidate',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'role_code' => 'SUP-CO-02',
                'role_title' => 'Co-Supervisor (Internal)',
                'min_qualification' => 'PhD / Senior Lecturer',
                'max_quota' => '8 PG Candidates',
                'sign_off_scope' => 'Methodology, Data Analysis, Thesis Chapters',
                'honorarium_unit' => 'KES 30,000 / candidate',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'role_code' => 'SUP-EXT-03',
                'role_title' => 'External Academic Advisor / Industry Specialist',
                'min_qualification' => 'PhD / Certified Industry Fellow',
                'max_quota' => '3 PG Candidates',
                'sign_off_scope' => 'Industry Validation & Applied Research Modules',
                'honorarium_unit' => 'KES 40,000 / candidate',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'role_code' => 'SUP-MSC-04',
                'role_title' => 'Master\'s Principal Supervisor',
                'min_qualification' => 'PhD / Lecturer with 3+ yrs experience',
                'max_quota' => '8 Master Candidates',
                'sign_off_scope' => 'Full Dissertation Lifecycle Sign-off',
                'honorarium_unit' => 'KES 25,000 / candidate',
                'status' => 'Active',
            ],
            [
                'id' => 5,
                'role_code' => 'SUP-MENTOR-05',
                'role_title' => 'Early Career Researcher Mentor',
                'min_qualification' => 'Professor / Distinguished Scholar',
                'max_quota' => '4 Post-Docs / Junior Faculty',
                'sign_off_scope' => 'Grant Writing & Publication Milestone Review',
                'honorarium_unit' => 'KES 20,000 / candidate',
                'status' => 'Active',
            ],
            [
                'id' => 6,
                'role_code' => 'SUP-EXAM-06',
                'role_title' => 'Internal Viva Examiner / Board Reader',
                'min_qualification' => 'PhD / Senior Academic Staff',
                'max_quota' => '12 Examinations / Year',
                'sign_off_scope' => 'Independent Blind Examination & Defense Verdict',
                'honorarium_unit' => 'KES 15,000 / oral defense',
                'status' => 'Inactive',
            ],
        ];

        return view('pg-research.supervisor-roles', compact('stats', 'roles'));
    }

    /**
     * 4. Proposal Reader / Internal Examiner Review Stage (R6 - Blocking)
     */
    public function proposalReaderReview(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'proposalsUnderReview' => 28,
            'readerApproved' => 42,
            'readerRevisions' => 9,
            'readerTurnaround' => '10.2 Days (SLA <= 14 Days)',
        ];

        $proposals = [
            [
                'id' => 1,
                'student_name' => 'Harrison Kiprono',
                'reg_no' => 'PHD-CS/2021/002',
                'programme' => 'PhD in Computer Science',
                'proposal_title' => 'High-Throughput Genomic Sequence Indexing on Distributed Edge Architectures',
                'appointed_reader' => 'Prof. David Kiplagat (Designated Internal Examiner)',
                'reader_verdict' => 'Approved to Proceed to Proposal Defence Panel',
                'comments_summary' => 'Comprehensive literature survey; research methodology is mathematically sound.',
                'assigned_date' => '08-08-2026',
                'reviewed_date' => '18-08-2026',
                'status' => 'Reader Cleared',
            ],
            [
                'id' => 2,
                'student_name' => 'Lorna Anyango',
                'reg_no' => 'MED/2023/0019',
                'programme' => 'Master of Education in Leadership',
                'proposal_title' => 'Institutional Governance Models and Learner Achievement in TVET Colleges',
                'appointed_reader' => 'Dr. Grace Njeri (Designated Internal Examiner)',
                'reader_verdict' => 'Minor Revisions Required on Conceptual Framework',
                'comments_summary' => 'Strengthen sampling frame in Chapter 3; clarify ethical clearance protocols.',
                'assigned_date' => '12-08-2026',
                'reviewed_date' => '22-08-2026',
                'status' => 'Revisions Under Review',
            ],
            [
                'id' => 3,
                'student_name' => 'Samuel Kibor Koech',
                'reg_no' => 'PHD-MED/2022/001',
                'programme' => 'PhD in Technology Education',
                'proposal_title' => 'Immersive Virtual Reality Simulation Frameworks for Technical Engineering Vocations',
                'appointed_reader' => 'Dr. Jeremiah Onunga (Designated Internal Examiner)',
                'reader_verdict' => 'Approved to Proceed to Proposal Defence Panel',
                'comments_summary' => 'Sound theoretical foundations and practical laboratory test-bed design.',
                'assigned_date' => '15-08-2026',
                'reviewed_date' => '25-08-2026',
                'status' => 'Reader Cleared',
            ],
        ];

        return view('pg-research.proposal-reader-review', compact('stats', 'proposals', 'status', 'search'));
    }

    /**
     * 5. Postgraduate Seminar Presentations Tracking (R3)
     */
    public function seminarPresentations(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'seminarsCompleted' => 56,
            'departmentalSeminars' => 32,
            'preDefenseSeminars' => 24,
            'attendanceRate' => '96.2%',
        ];

        $seminars = [
            [
                'id' => 1,
                'candidate_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'programme' => 'PhD in Computer Science',
                'seminar_type' => 'Doctoral Pre-Defense Seminar 3 (School Level)',
                'presentation_date' => '14-08-2026',
                'moderator' => 'Prof. Patrick Ouma (Dean, SST)',
                'panel_feedback' => 'Commended for robust empirical simulations; cleared to lodge formal Notice of Intent to Defend.',
                'status' => 'Completed & Certified',
            ],
            [
                'id' => 2,
                'candidate_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'programme' => 'Master of Data Science',
                'seminar_type' => 'Master\'s Research Findings Seminar (Departmental)',
                'presentation_date' => '10-08-2026',
                'moderator' => 'Dr. Kikete Wabuya (Ag. Chair, Math & Stat)',
                'panel_feedback' => 'Spatial prediction models validated; feedback incorporated into draft dissertation.',
                'status' => 'Completed & Certified',
            ],
            [
                'id' => 3,
                'candidate_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'programme' => 'PhD in Economics',
                'seminar_type' => 'Doctoral Methodology Seminar 2 (Faculty Level)',
                'presentation_date' => '04-09-2026 (Upcoming)',
                'moderator' => 'Dr. Daniel Otieno',
                'panel_feedback' => 'Scheduled for presentation in Faculty Boardroom B402.',
                'status' => 'Scheduled',
            ],
        ];

        return view('pg-research.seminar-presentations', compact('stats', 'seminars', 'status', 'search'));
    }

    /**
     * 6. Research Progress Reports (Forms A, B, C) (R5, R14)
     */
    public function progressReports(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'totalReportsSubmitted' => 214,
            'formACount' => 86, // Initial Inception & Literature
            'formBCount' => 74, // Data Collection & Analysis
            'formCCount' => 54, // Thesis Draft & Milestone Clearance
            'complianceRate' => '92.8%',
        ];

        $reports = [
            [
                'id' => 1,
                'student_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'degree_level' => 'PhD (Requires 6 Periodic Reports)',
                'report_stage' => 'Form C - Report 6 of 6 (Final Thesis Draft)',
                'submission_date' => '18-08-2026',
                'supervisor_endorsement' => 'Prof. James Mwangi (Lead Supervisor - Approved)',
                'milestone_summary' => 'Completed all 5 chapters, Turnitin 8.2%, 2 Scopus papers published.',
                'self_service_action' => 'Locked (Approved by Supervisor)',
                'status' => 'Approved by Directorate',
            ],
            [
                'id' => 2,
                'student_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'degree_level' => 'Master (Requires 3 Periodic Reports)',
                'report_stage' => 'Form C - Report 3 of 3 (Final Dissertation Draft)',
                'submission_date' => '15-08-2026',
                'supervisor_endorsement' => 'Dr. Amina Hassan (Approved)',
                'milestone_summary' => 'Model trained on Kenya Meteorological dataset; discussion chapter completed.',
                'self_service_action' => 'Locked (Approved by Supervisor)',
                'status' => 'Approved by Directorate',
            ],
            [
                'id' => 3,
                'student_name' => 'Dennis Kioko Mutisya',
                'reg_no' => 'MBA/2023/0440',
                'degree_level' => 'Master (Requires 3 Periodic Reports)',
                'report_stage' => 'Form B - Report 2 of 3 (Data Collection)',
                'submission_date' => '22-08-2026',
                'supervisor_endorsement' => 'Dr. Daniel Otieno (Pending Review)',
                'milestone_summary' => 'Administered 180 questionnaires to Nairobi manufacturing cluster.',
                'self_service_action' => 'Recall / Replace Available (Self-Service R14)',
                'status' => 'Under Supervisor Review',
            ],
        ];

        return view('pg-research.progress-reports', compact('stats', 'reports', 'status', 'search'));
    }

    /**
     * 7. Defence Request Approval
     */
    public function defenceRequestApproval(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'totalRequests' => 64,
            'pendingApproval' => 18,
            'clearedForViva' => 39,
            'sentBack' => 7,
            'avgTurnitin' => '11.4%',
        ];

        $requests = [
            [
                'id' => 1,
                'student_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'programme' => 'PhD in Computer Science',
                'thesis_title' => 'Federated Machine Learning Frameworks for Resilient Rural Telehealth Diagnostics',
                'lead_supervisor' => 'Prof. James Mwangi',
                'turnitin_score' => '8.2%',
                'coursework_gpa' => '3.88',
                'publications_count' => '2 Indexed Articles',
                'fee_clearance' => 'Cleared (100%)',
                'status' => 'Pending Approval',
                'submitted_at' => '24-08-2026',
            ],
            [
                'id' => 2,
                'student_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'programme' => 'Master of Data Science',
                'thesis_title' => 'Predictive Spatial Modeling of Agricultural Yield Fluctuations under Climate Volatility in Kenya',
                'lead_supervisor' => 'Dr. Amina Hassan',
                'turnitin_score' => '12.5%',
                'coursework_gpa' => '3.72',
                'publications_count' => '1 Peer-Reviewed Article',
                'fee_clearance' => 'Cleared (100%)',
                'status' => 'Cleared for Viva',
                'submitted_at' => '22-08-2026',
            ],
            [
                'id' => 3,
                'student_name' => 'Grace Wanjiku Njuguna',
                'reg_no' => 'MED/2024/0052',
                'programme' => 'Master of Education in Learning Design',
                'thesis_title' => 'Digital Pedagogical Tool Adoption in Secondary STEM Curricula in Lake Region Counties',
                'lead_supervisor' => 'Dr. Grace Njeri',
                'turnitin_score' => '14.1%',
                'coursework_gpa' => '3.65',
                'publications_count' => '1 Peer-Reviewed Article',
                'fee_clearance' => 'Cleared (100%)',
                'status' => 'Cleared for Viva',
                'submitted_at' => '19-08-2026',
            ],
            [
                'id' => 4,
                'student_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'programme' => 'PhD in Economics',
                'thesis_title' => 'Macroeconomic Implications of Cross-Border Mobile Money Remittances within EAC Economies',
                'lead_supervisor' => 'Dr. Daniel Otieno',
                'turnitin_score' => '19.4%',
                'coursework_gpa' => '3.91',
                'publications_count' => '2 Indexed Articles',
                'fee_clearance' => 'Cleared (100%)',
                'status' => 'Sent Back',
                'submitted_at' => '18-08-2026',
            ],
            [
                'id' => 5,
                'student_name' => 'Faith Jepchirchir',
                'reg_no' => 'MSC-CYB/2024/0034',
                'programme' => 'MSc in Cybersecurity & Forensics',
                'thesis_title' => 'Zero-Trust Protocol Implementations in Critical National Banking Infrastructure',
                'lead_supervisor' => 'Prof. James Mwangi',
                'turnitin_score' => '9.7%',
                'coursework_gpa' => '3.80',
                'publications_count' => '1 Peer-Reviewed Article',
                'fee_clearance' => 'Cleared (100%)',
                'status' => 'Pending Approval',
                'submitted_at' => '25-08-2026',
            ],
        ];

        return view('pg-research.defence-request-approval', compact('stats', 'requests', 'status', 'search'));
    }

    /**
     * 8. Examiner Dashboard & Mark Entry
     */
    public function examinerDashboard(Request $request): View
    {
        $stats = [
            'assignedManuscripts' => 14,
            'evaluationsCompleted' => 9,
            'evaluationsPending' => 5,
            'avgTurnaroundDays' => '18 Days (SLA <= 21 Days)',
        ];

        $assignments = [
            [
                'id' => 1,
                'examiner_name' => 'Prof. Timothy Wafula',
                'examiner_type' => 'External Examiner (University of Nairobi)',
                'candidate_code' => 'CAND-PHD-CS-2026-04',
                'thesis_title' => 'Federated Machine Learning Frameworks for Resilient Rural Telehealth Diagnostics',
                'dispatch_date' => '05-08-2026',
                'due_date' => '26-08-2026',
                'report_status' => 'Report & Rubric Submitted (Score: 81.5%)',
                'honorarium_status' => 'Approved (KES 35,000)',
            ],
            [
                'id' => 2,
                'examiner_name' => 'Dr. Amina Hassan',
                'examiner_type' => 'Internal Examiner (School of Computing)',
                'candidate_code' => 'CAND-PHD-CS-2026-04',
                'thesis_title' => 'Federated Machine Learning Frameworks for Resilient Rural Telehealth Diagnostics',
                'dispatch_date' => '05-08-2026',
                'due_date' => '26-08-2026',
                'report_status' => 'Report & Rubric Submitted (Score: 84.0%)',
                'honorarium_status' => 'Approved (KES 15,000)',
            ],
            [
                'id' => 3,
                'examiner_name' => 'Dr. Sarah Rotich',
                'examiner_type' => 'External Examiner (Kenyatta University)',
                'candidate_code' => 'CAND-MDS-2026-118',
                'thesis_title' => 'Predictive Spatial Modeling of Agricultural Yield Fluctuations under Climate Volatility in Kenya',
                'dispatch_date' => '12-08-2026',
                'due_date' => '02-09-2026',
                'report_status' => 'Under Review (Draft Saved)',
                'honorarium_status' => 'Pending Submission',
            ],
            [
                'id' => 4,
                'examiner_name' => 'Prof. David Ndetei',
                'examiner_type' => 'External Examiner (Kenyatta University)',
                'candidate_code' => 'CAND-PHD-MED-2026-01',
                'thesis_title' => 'Institutional Governance Models and Learner Achievement in TVET Colleges',
                'dispatch_date' => '18-08-2026',
                'due_date' => '08-09-2026',
                'report_status' => 'Dispatched / Awaiting Evaluation',
                'honorarium_status' => 'Pending Submission',
            ],
        ];

        return view('pg-research.examiner-dashboard', compact('stats', 'assignments'));
    }

    /**
     * 9. Graduate Level Viva Examination
     */
    public function vivaExamination(Request $request): View
    {
        $stats = [
            'scheduledVivas' => 24,
            'completedThisMonth' => 19,
            'passRate' => '94.7%',
            'pendingPanels' => 5,
        ];

        $vivas = [
            [
                'id' => 1,
                'candidate_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'degree' => 'PhD in Computer Science',
                'viva_date' => '04-09-2026 10:00 AM',
                'venue' => 'Senate Chamber / Virtual Zoom Room 1',
                'board_chair' => 'Prof. Patrick Ouma (Dean, SPGS)',
                'internal_examiner' => 'Dr. Amina Hassan',
                'external_examiner' => 'Prof. Timothy Wafula (UoN)',
                'status' => 'Panel Confirmed & Scheduled',
            ],
            [
                'id' => 2,
                'candidate_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'degree' => 'Master of Data Science',
                'viva_date' => '08-09-2026 02:00 PM',
                'venue' => 'Faculty Boardroom B402',
                'board_chair' => 'Dr. Daniel Otieno (Chairman)',
                'internal_examiner' => 'Prof. James Mwangi',
                'external_examiner' => 'Dr. Sarah Rotich (Kenyatta Univ)',
                'status' => 'Panel Confirmed & Scheduled',
            ],
            [
                'id' => 3,
                'candidate_name' => 'Grace Wanjiku Njuguna',
                'reg_no' => 'MED/2024/0052',
                'degree' => 'Master of Education',
                'viva_date' => '28-08-2026 09:30 AM',
                'venue' => 'School of Education Boardroom',
                'board_chair' => 'Prof. Patrick Ouma',
                'internal_examiner' => 'Dr. Grace Njeri',
                'external_examiner' => 'Prof. Agnes Kimani (Moi Univ)',
                'status' => 'Completed - Minor Corrections Awarded',
            ],
            [
                'id' => 4,
                'candidate_name' => 'Samuel Kibor Koech',
                'reg_no' => 'PHD-MED/2022/001',
                'degree' => 'PhD in Technology Education',
                'viva_date' => '11-09-2026 11:00 AM',
                'venue' => 'Senate Chamber',
                'board_chair' => 'Prof. Patrick Ouma',
                'internal_examiner' => 'Dr. Grace Njeri',
                'external_examiner' => 'Prof. David Ndetei (KU)',
                'status' => 'Awaiting External Examiner Confirmation',
            ],
        ];

        return view('pg-research.viva-examination', compact('stats', 'vivas'));
    }

    /**
     * 10. Thesis Marks Approval & Moderation (R7, R12)
     */
    public function thesisMarksApproval(Request $request): View
    {
        $stats = [
            'marksPendingRatification' => 22,
            'approvedBySenate' => 74,
            'distinctionsAwarded' => 15,
            'avgCompositeScore' => '76.4%',
        ];

        $marksList = [
            [
                'id' => 1,
                'student_name' => 'Harrison Kiprono',
                'reg_no' => 'PHD-CS/2021/002',
                'programme' => 'PhD in Computer Science',
                'internal_mark' => '84.0% (A)',
                'external_mark' => '81.5% (A)',
                'oral_viva_mark' => '86.0% (A)',
                'composite_score' => '83.8%',
                'final_grade' => 'Distinction / Pass',
                'senate_status' => 'Approved by Senate',
            ],
            [
                'id' => 2,
                'student_name' => 'Grace Wanjiku Njuguna',
                'reg_no' => 'MED/2024/0052',
                'programme' => 'Master of Education',
                'internal_mark' => '76.0% (B+)',
                'external_mark' => '74.0% (B+)',
                'oral_viva_mark' => '78.0% (B+)',
                'composite_score' => '76.0%',
                'final_grade' => 'Credit / Pass',
                'senate_status' => 'Pending Senate Ratification',
            ],
            [
                'id' => 3,
                'student_name' => 'Lorna Anyango',
                'reg_no' => 'MED/2023/0019',
                'programme' => 'Master of Education in Leadership',
                'internal_mark' => '79.0% (B+)',
                'external_mark' => '82.0% (A)',
                'oral_viva_mark' => '80.0% (A)',
                'composite_score' => '80.3%',
                'final_grade' => 'Distinction / Pass',
                'senate_status' => 'Pending Senate Ratification',
            ],
            [
                'id' => 4,
                'student_name' => 'Dennis Kioko Mutisya',
                'reg_no' => 'MBA/2023/0440',
                'programme' => 'Master of Business Administration',
                'internal_mark' => '71.0% (B)',
                'external_mark' => '68.0% (C+)',
                'oral_viva_mark' => '73.0% (B)',
                'composite_score' => '70.7%',
                'final_grade' => 'Credit / Pass',
                'senate_status' => 'Pending Senate Ratification',
            ],
            [
                'id' => 5,
                'student_name' => 'Esther Mwende',
                'reg_no' => 'PHD-ECO/2021/005',
                'programme' => 'PhD in Economics',
                'internal_mark' => '88.0% (A)',
                'external_mark' => '85.0% (A)',
                'oral_viva_mark' => '89.0% (A)',
                'composite_score' => '87.3%',
                'final_grade' => 'Distinction / Pass',
                'senate_status' => 'Approved by Senate',
            ],
        ];

        return view('pg-research.thesis-marks-approval', compact('stats', 'marksList'));
    }

    /**
     * 11. Final Thesis Resubmission Review
     */
    public function thesisResubmission(Request $request): View
    {
        $stats = [
            'totalResubmissions' => 38,
            'underReview' => 9,
            'approvedForBinding' => 26,
            'revisionsPending' => 3,
        ];

        $resubmissions = [
            [
                'id' => 1,
                'student_name' => 'Harrison Kiprono',
                'reg_no' => 'PHD-CS/2021/002',
                'programme' => 'PhD in Computer Science',
                'thesis_title' => 'High-Throughput Genomic Sequence Indexing on Distributed Edge Architectures',
                'viva_verdict' => 'Pass with Minor Corrections (30-day window)',
                'examiner_auditor' => 'Prof. David Kiplagat (Internal Examiner)',
                'corrections_matrix' => 'All 14 Examiner Comments Addressed & Tabulated',
                'hardbound_copies' => 'Submitted (5 Copies + PDF + CD)',
                'status' => 'Approved for Hardbound Binding',
                'resubmitted_at' => '20-08-2026',
            ],
            [
                'id' => 2,
                'student_name' => 'Lorna Anyango',
                'reg_no' => 'MED/2023/0019',
                'programme' => 'Master of Education in Leadership',
                'thesis_title' => 'Institutional Governance Models and Learner Achievement in TVET Colleges',
                'viva_verdict' => 'Pass with Minor Corrections (30-day window)',
                'examiner_auditor' => 'Dr. Grace Njeri (Examiner)',
                'corrections_matrix' => '8 of 8 Comments Verified & Certified by Supervisor',
                'hardbound_copies' => 'Pending Verification',
                'status' => 'Under Review',
                'resubmitted_at' => '25-08-2026',
            ],
            [
                'id' => 3,
                'student_name' => 'Dennis Kioko Mutisya',
                'reg_no' => 'MBA/2023/0440',
                'programme' => 'Master of Business Administration',
                'thesis_title' => 'Supply Chain Digitization and Operational Efficiency among Kenyan Manufacturing Firms',
                'viva_verdict' => 'Pass with Major Corrections (90-day window)',
                'examiner_auditor' => 'Dr. Daniel Otieno (Department Reader)',
                'corrections_matrix' => 'Chapter 4 Statistical Methodology Restructured',
                'hardbound_copies' => 'Under Review',
                'status' => 'Under Review',
                'resubmitted_at' => '21-08-2026',
            ],
            [
                'id' => 4,
                'student_name' => 'Esther Mwende',
                'reg_no' => 'PHD-ECO/2021/005',
                'programme' => 'PhD in Economics',
                'thesis_title' => 'Fiscal Decentralization and Sub-National Debt Sustainability in Eastern Africa',
                'viva_verdict' => 'Pass with Minor Corrections',
                'examiner_auditor' => 'Prof. James Mwangi (Lead Examiner)',
                'corrections_matrix' => 'Signed Certificate of Corrections Uploaded',
                'hardbound_copies' => 'Cleared & Stamped by University Librarian',
                'status' => 'Approved for Hardbound Binding',
                'resubmitted_at' => '17-08-2026',
            ],
        ];

        return view('pg-research.thesis-resubmission', compact('stats', 'resubmissions'));
    }

    /**
     * 12. Research Publications Review (R4)
     */
    public function publicationsReview(Request $request): View
    {
        $stats = [
            'totalArticlesLogged' => 112,
            'verifiedPeerReviewed' => 84,
            'pendingIndexingCheck' => 21,
            'rejectedNonCUE' => 7,
        ];

        $publications = [
            [
                'id' => 1,
                'author_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'programme' => 'PhD in Computer Science',
                'article_title' => 'Privacy-Preserving Federated Diagnostics in Bandwidth-Constrained Health Networks',
                'journal_name' => 'IEEE Transactions on Network and Service Management',
                'indexing' => 'Scopus Q1 / Web of Science',
                'doi_link' => '10.1109/TNSM.2026.319022',
                'cue_requirement' => 'Article 1 of 2 (Doctoral Compulsory Standard)',
                'status' => 'Verified & Approved',
            ],
            [
                'id' => 2,
                'author_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'programme' => 'PhD in Computer Science',
                'article_title' => 'Edge-Assisted Telemedicine Anomaly Detection via Quantized Transformers',
                'journal_name' => 'Elsevier Journal of Systems Architecture',
                'indexing' => 'Scopus Q2 / AJOL Indexed',
                'doi_link' => '10.1016/j.sysarc.2026.102844',
                'cue_requirement' => 'Article 2 of 2 (Doctoral Compulsory Standard)',
                'status' => 'Verified & Approved',
            ],
            [
                'id' => 3,
                'author_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'programme' => 'Master of Data Science',
                'article_title' => 'Extreme Weather Forecasting using Spatio-Temporal Graph Neural Networks in Kenya',
                'journal_name' => 'East African Journal of Science, Technology and Innovation (EAJSTI)',
                'indexing' => 'AJOL / Google Scholar Peer Reviewed',
                'doi_link' => '10.4314/eajsti.v7i2.11',
                'cue_requirement' => 'Article 1 of 1 (Master\'s Encouraged Benchmark)',
                'status' => 'Verified & Approved',
            ],
            [
                'id' => 4,
                'author_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'programme' => 'PhD in Economics',
                'article_title' => 'Cross-Border Digital Payments Interoperability and Financial Inclusion in the East African Community',
                'journal_name' => 'African Development Review',
                'indexing' => 'Wiley / Scopus Q2',
                'doi_link' => '10.1111/1467-8268.12702',
                'cue_requirement' => 'Article 1 of 2 (Doctoral Compulsory Standard)',
                'status' => 'Pending Indexing Verification',
            ],
        ];

        return view('pg-research.publications-review', compact('stats', 'publications'));
    }

    /**
     * 13. Legacy Projects & Interim Data Migration (R10, R18)
     */
    public function legacyMigration(Request $request): View
    {
        $stats = [
            'totalLegacyDossiers' => 82,
            'migratedFromDSC800' => 48,
            'interimFormsMigrated' => 26,
            'pendingDataValidation' => 8,
        ];

        $migrations = [
            [
                'id' => 1,
                'student_name' => 'Collins Kiprop',
                'reg_no' => 'DSC-800/2023/042',
                'source_module' => 'DSC800 (Module 12) Project Management',
                'programme' => 'MSc in Information Systems',
                'migrated_artifacts' => 'Approved Proposal PDF, 3 Progress Forms, Supervisor Logbook',
                'target_stage' => 'Draft Thesis Review',
                'validation_status' => 'Migrated & Verified (100%)',
            ],
            [
                'id' => 2,
                'student_name' => 'Jackline Cherotich',
                'reg_no' => 'SST-PG/2024/008',
                'source_module' => 'Interim Google Forms & Email Repository',
                'programme' => 'PhD in Data Science',
                'migrated_artifacts' => 'Interim Concept Paper, Turnitin Scan 11.2%, HOD Endorsement',
                'target_stage' => 'Proposal Reader Review',
                'validation_status' => 'Migrated & Verified (100%)',
            ],
            [
                'id' => 3,
                'student_name' => 'Peter Mwaniki',
                'reg_no' => 'MED-PG/2023/014',
                'source_module' => 'DSC800 (Module 12) Project Management',
                'programme' => 'Master of Education',
                'migrated_artifacts' => 'Proposal Defence Rubrics (Panel Passed)',
                'target_stage' => 'Progress Reporting Form B',
                'validation_status' => 'Awaiting Supervisor Re-Confirmation',
            ],
        ];

        return view('pg-research.legacy-migration', compact('stats', 'migrations'));
    }

    /**
     * 14. PG Appeal Category
     */
    public function appealCategory(Request $request): View
    {
        $categories = [
            [
                'id' => 1,
                'code' => 'AC-DEF-01',
                'name' => 'Thesis Defense & Viva Voce Outcome Appeal',
                'scope' => 'Viva Voce & Final Defense',
                'tier' => 'Senate Post-Graduate Committee',
                'sla_days' => 14,
                'status' => 'Active',
                'description' => 'Appeals lodged against oral defense failure, major revisions verdict, or grading disputes.',
            ],
            [
                'id' => 2,
                'code' => 'AC-PRG-02',
                'name' => 'Annual Research Progress Discontinuation',
                'scope' => 'Milestone Review',
                'tier' => 'Faculty Academic Board',
                'sla_days' => 21,
                'status' => 'Active',
                'description' => 'Appeals contesting termination of candidature due to unsatisfactory annual progress review.',
            ],
            [
                'id' => 3,
                'code' => 'AC-SUP-03',
                'name' => 'Supervisor Allocation & Academic Impasse',
                'scope' => 'Supervision Grievance',
                'tier' => 'Directorate of Post-Graduate Studies',
                'sla_days' => 10,
                'status' => 'Active',
                'description' => 'Grievances regarding supervisor unavailability, conflict of interest, or academic deadlock.',
            ],
            [
                'id' => 4,
                'code' => 'AC-PLG-04',
                'name' => 'Plagiarism & Turnitin Similarity Sanction',
                'scope' => 'Research Integrity Board',
                'tier' => 'Research Integrity & Ethics Board',
                'sla_days' => 30,
                'status' => 'Active',
                'description' => 'Appeals regarding thesis similarity index threshold violations and disciplinary penalties.',
            ],
            [
                'id' => 5,
                'code' => 'AC-EXT-05',
                'name' => 'Extenuating Candidature Extension Denied',
                'scope' => 'Candidature Extension',
                'tier' => 'School Board of Examiners',
                'sla_days' => 7,
                'status' => 'Active',
                'description' => 'Appeals against rejection of medical or compassional study extension requests.',
            ],
            [
                'id' => 6,
                'code' => 'AC-REV-06',
                'name' => 'External Examiner Assessment Contest',
                'scope' => 'Examination Dispute',
                'tier' => 'Vice-Chancellor Special Panel',
                'sla_days' => 21,
                'status' => 'Inactive',
                'description' => 'Contested external reviewer evaluation reports requiring third-party blind arbiter.',
            ],
        ];

        return view('pg-research.appeal-category', compact('categories'));
    }

    /**
     * 15. PG Appeal Period Setup
     */
    public function appealPeriodSetup(Request $request): View
    {
        $periods = [
            [
                'id' => 1,
                'window_name' => '2026/2027 Semester 1 Postgraduate Defense Appeals',
                'academic_year' => '2026-2027',
                'cohort' => 'PhD & Master of Science Cycle 1',
                'start_date' => '01-09-2026',
                'end_date' => '30-09-2026',
                'hearing_date' => '15-10-2026',
                'status' => 'Open',
            ],
            [
                'id' => 2,
                'window_name' => '2025/2026 Special Research Progress Grievances',
                'academic_year' => '2025-2026',
                'cohort' => 'All Postgraduate Scholars',
                'start_date' => '15-10-2025',
                'end_date' => '15-11-2025',
                'hearing_date' => '05-12-2025',
                'status' => 'Closed',
            ],
            [
                'id' => 3,
                'window_name' => '2026/2027 Mid-Year Thesis Grading Review Window',
                'academic_year' => '2026-2027',
                'cohort' => 'Doctoral Candidates 2024-2026',
                'start_date' => '01-02-2027',
                'end_date' => '28-02-2027',
                'hearing_date' => '10-03-2027',
                'status' => 'Scheduled',
            ],
            [
                'id' => 4,
                'window_name' => '2024/2025 Late Dissertation Defense Appeals',
                'academic_year' => '2024-2025',
                'cohort' => 'Executive MBA & Master of Education',
                'start_date' => '10-05-2025',
                'end_date' => '31-05-2025',
                'hearing_date' => '14-06-2025',
                'status' => 'Closed',
            ],
            [
                'id' => 5,
                'window_name' => '2026/2027 Plagiarism Committee Adjudication Window',
                'academic_year' => '2026-2027',
                'cohort' => 'Final Year Postgraduate Submissions',
                'start_date' => '15-09-2026',
                'end_date' => '15-10-2026',
                'hearing_date' => '25-10-2026',
                'status' => 'Open',
            ],
        ];

        return view('pg-research.appeal-period-setup', compact('periods'));
    }

    /**
     * 16. Embedded Plagiarism & AI Similarity Index Scanner (R11)
     * Policy: Max Originality Similarity <= 15% (Turnitin) | Max AI Generated Text <= 20%
     */
    public function plagiarismChecker(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $stats = [
            'totalScansConducted' => 248,
            'fullyClearedDocs' => 196,
            'flaggedSimilarity' => 31, // > 15% Originality similarity
            'flaggedAiUsage' => 21,    // > 20% AI generated content
            'avgSimilarityIndex' => '10.8%',
            'avgAiDetectionIndex' => '7.4%',
        ];

        $scans = [
            [
                'id' => 1,
                'student_name' => 'Dr. Mercy Chepkemoi',
                'reg_no' => 'PHD-CS/2023/004',
                'programme' => 'PhD in Computer Science',
                'document_title' => 'Final_Thesis_Manuscript_v4.pdf',
                'document_stage' => 'Final Doctoral Thesis (Chapters 1–5)',
                'similarity_score' => 8.2, // Limit: <= 15%
                'ai_score' => 6.5,         // Limit: <= 20%
                'similarity_status' => 'Compliant (<= 15%)',
                'ai_status' => 'Compliant (<= 20%)',
                'matched_sources' => 'Internet: 4.1%, Publications: 3.2%, Student Papers: 0.9%',
                'ai_breakdown' => 'Human Authored: 93.5%, AI Assisted: 6.5%',
                'certificate_no' => 'MEMA-SIM-2026-089',
                'scan_date' => '24-08-2026 14:32',
                'verdict' => 'Cleared for Viva Voce',
            ],
            [
                'id' => 2,
                'student_name' => 'Geoffrey Mutua',
                'reg_no' => 'MDS/2024/0118',
                'programme' => 'Master of Data Science',
                'document_title' => 'MSc_Dissertation_Complete.pdf',
                'document_stage' => 'Master\'s Dissertation (Chapters 1–5)',
                'similarity_score' => 12.5,
                'ai_score' => 11.0,
                'similarity_status' => 'Compliant (<= 15%)',
                'ai_status' => 'Compliant (<= 20%)',
                'matched_sources' => 'Internet: 6.0%, Publications: 4.8%, Student Papers: 1.7%',
                'ai_breakdown' => 'Human Authored: 89.0%, AI Assisted: 11.0%',
                'certificate_no' => 'MEMA-SIM-2026-074',
                'scan_date' => '22-08-2026 11:15',
                'verdict' => 'Cleared for Viva Voce',
            ],
            [
                'id' => 3,
                'student_name' => 'Boniface Ouma K\'Onyango',
                'reg_no' => 'PHD-ECO/2022/008',
                'programme' => 'PhD in Economics',
                'document_title' => 'Doctoral_Proposal_Macroeconomics.pdf',
                'document_stage' => 'Proposal Chapters 1–3',
                'similarity_score' => 19.4, // FLAGGED: > 15%
                'ai_score' => 8.0,
                'similarity_status' => 'Exceeded Limit (> 15%)',
                'ai_status' => 'Compliant (<= 20%)',
                'matched_sources' => 'Internet: 11.2%, Publications: 6.4%, Student Papers: 1.8%',
                'ai_breakdown' => 'Human Authored: 92.0%, AI Assisted: 8.0%',
                'certificate_no' => 'Pending Re-Write',
                'scan_date' => '18-08-2026 09:40',
                'verdict' => 'Flagged - Revisions Required',
            ],
            [
                'id' => 4,
                'student_name' => 'Dennis Kioko Mutisya',
                'reg_no' => 'MBA/2023/0440',
                'programme' => 'Master of Business Administration',
                'document_title' => 'Supply_Chain_Literature_Draft.pdf',
                'document_stage' => 'Draft Thesis Chapter 2 Literature',
                'similarity_score' => 11.0,
                'ai_score' => 28.5, // FLAGGED: > 20% AI usage
                'similarity_status' => 'Compliant (<= 15%)',
                'ai_status' => 'AI Limit Exceeded (> 20%)',
                'matched_sources' => 'Internet: 5.5%, Publications: 4.0%, Student Papers: 1.5%',
                'ai_breakdown' => 'Human Authored: 71.5%, AI Generated: 28.5%',
                'certificate_no' => 'Pending Re-Write',
                'scan_date' => '21-08-2026 16:05',
                'verdict' => 'Flagged - Excessive AI Generation',
            ],
            [
                'id' => 5,
                'student_name' => 'Grace Wanjiku Njuguna',
                'reg_no' => 'MED/2024/0052',
                'programme' => 'Master of Education in Learning Design',
                'document_title' => 'MED_Final_Thesis_Hardbound_Draft.pdf',
                'document_stage' => 'Post-Viva Corrected Thesis',
                'similarity_score' => 9.5,
                'ai_score' => 4.2,
                'similarity_status' => 'Compliant (<= 15%)',
                'ai_status' => 'Compliant (<= 20%)',
                'matched_sources' => 'Internet: 4.8%, Publications: 3.5%, Student Papers: 1.2%',
                'ai_breakdown' => 'Human Authored: 95.8%, AI Assisted: 4.2%',
                'certificate_no' => 'MEMA-SIM-2026-092',
                'scan_date' => '26-08-2026 10:20',
                'verdict' => 'Cleared for Hardbound Binding',
            ],
        ];

        return view('pg-research.plagiarism-checker', compact('stats', 'scans', 'status', 'search'));
    }
}
