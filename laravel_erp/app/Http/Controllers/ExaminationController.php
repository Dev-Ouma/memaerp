<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ExamCenter;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\GradeScale;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ExaminationController extends Controller
{
    /**
     * 1. Exam Center Configuration
     */
    public function examCenter(Request $request): View
    {
        $stats = [
            'totalCenters' => 8,
            'hallCapacity' => 4500,
            'activeRooms' => 24,
            'virtualProctoringNodes' => 6,
        ];

        $centers = [
            [
                'id' => 1,
                'center_code' => 'EXC-MAIN-01',
                'name' => 'Main Campus Assembly Hall',
                'location' => 'Block A, Ground Floor',
                'capacity' => 1200,
                'proctors_allocated' => 8,
                'special_needs_access' => 'Ramped Entrance & Adjusted Desks',
                'status' => 'Operational',
            ],
            [
                'id' => 2,
                'center_code' => 'EXC-LIB-02',
                'name' => 'University Library Basement',
                'location' => 'Library Complex',
                'capacity' => 800,
                'proctors_allocated' => 6,
                'special_needs_access' => 'Lift Access Enabled',
                'status' => 'Operational',
            ],
            [
                'id' => 3,
                'center_code' => 'EXC-SST-03',
                'name' => 'Science & Technology Block Rooms 101-105',
                'location' => 'SST Complex',
                'capacity' => 500,
                'proctors_allocated' => 4,
                'special_needs_access' => 'Standard Access',
                'status' => 'Operational',
            ],
            [
                'id' => 4,
                'center_code' => 'EXC-VIRT-04',
                'name' => 'Virtual Proctoring Server Node 1 (ODeL)',
                'location' => 'Cloud Portal (Moodle Proctored)',
                'capacity' => 2000,
                'proctors_allocated' => 12,
                'special_needs_access' => 'Extended Time Software Config',
                'status' => 'Operational',
            ],
        ];

        $centers = ExamCenter::query()->orderBy('center_code')->get();
        $stats = [
            'totalCenters' => $centers->count(),
            'hallCapacity' => $centers->sum('capacity'),
            'activeRooms' => $centers->where('status', 'OPERATIONAL')->count(),
            'virtualProctoringNodes' => $centers->filter(fn (ExamCenter $center): bool => str_contains(strtolower($center->location), 'virtual'))->count(),
        ];

        return view('examination.exam-center', compact('stats', 'centers'));
    }

    /**
     * 2. Exam Session Setup
     */
    public function examSession(Request $request): View
    {
        $stats = [
            'currentSession' => 'Trimester II 2026/2027',
            'durationWeeks' => '2 Weeks (Exam Block)',
            'registeredCandidates' => 14850,
            'examinationSLA' => '100% Compliant',
        ];

        $sessions = [
            [
                'id' => 1,
                'session_code' => 'EXS-2027-T2',
                'session_title' => 'Trimester II Examination Block',
                'start_date' => '05 Apr 2027',
                'end_date' => '17 Apr 2027',
                'daily_slots' => '3 Slots (Morning, Mid-Day, Evening)',
                'candidate_count' => 14850,
                'moderation_deadline' => '30 Apr 2027',
                'status' => 'Active Session / Configured',
            ],
            [
                'id' => 2,
                'session_code' => 'EXS-2027-T3',
                'session_title' => 'Trimester III Summer Examination',
                'start_date' => '02 Aug 2027',
                'end_date' => '14 Aug 2027',
                'daily_slots' => '2 Slots (Morning, Evening)',
                'candidate_count' => 3800,
                'moderation_deadline' => '31 Aug 2027',
                'status' => 'Scheduled / Upcoming',
            ],
            [
                'id' => 3,
                'session_code' => 'EXS-2026-T1',
                'session_title' => 'Trimester I Examination Block',
                'start_date' => '07 Dec 2026',
                'end_date' => '19 Dec 2026',
                'daily_slots' => '3 Slots (Morning, Mid-Day, Evening)',
                'candidate_count' => 14200,
                'moderation_deadline' => '15 Jan 2027',
                'status' => 'Exam Completed & Closed',
            ],
        ];

        $sessions = ExamSession::query()->withSum('schedules', 'candidate_count')->latest('start_date')->get();
        $sessions->each(fn (ExamSession $session) => $session->setAttribute('candidate_count', (int) $session->schedules_sum_candidate_count));
        $current = $sessions->first(fn (ExamSession $session): bool => $session->start_date->lte(today()) && $session->end_date->gte(today()));
        $stats = [
            'currentSession' => $current?->session_title ?? 'No active examination session',
            'durationWeeks' => $current ? $current->start_date->diffInWeeks($current->end_date).' Weeks' : '—',
            'registeredCandidates' => Student::query()->count(),
            'examinationSLA' => $sessions->where('moderation_deadline', '<', today())->whereNotIn('status', ['CLOSED'])->isEmpty() ? '100% Compliant' : 'Action Required',
        ];

        return view('examination.exam-session', compact('stats', 'sessions'));
    }

    /**
     * 3. Exam Schedule
     */
    public function examSchedule(Request $request): View
    {
        $stats = [
            'totalScheduledPapers' => 342,
            'clashesDetected' => 0,
            'invigilatorsAllocated' => 96,
            'timetableStatus' => 'Senate Approved',
        ];

        $schedules = [
            [
                'id' => 1,
                'paper_code' => 'CS-301-EXAM',
                'course_title' => 'Software Engineering Principles',
                'date' => '06 Apr 2027',
                'slot' => 'Morning Slot (08:30 - 11:30)',
                'venue' => 'Assembly Hall & Virtual Node 1',
                'candidates' => 240,
                'chief_invigilator' => 'Prof. Peter Ondieki',
                'status' => 'Timetabled / Published',
            ],
            [
                'id' => 2,
                'paper_code' => 'DS-204-EXAM',
                'course_title' => 'Machine Learning & Neural Networks',
                'date' => '08 Apr 2027',
                'slot' => 'Mid-Day Slot (13:00 - 16:00)',
                'venue' => 'Assembly Hall & Virtual Node 2',
                'candidates' => 185,
                'chief_invigilator' => 'Dr. Amina Hassan',
                'status' => 'Timetabled / Published',
            ],
            [
                'id' => 3,
                'paper_code' => 'BBA-201-EXAM',
                'course_title' => 'Strategic Human Resource Management',
                'date' => '12 Apr 2027',
                'slot' => 'Evening Slot (16:30 - 19:30)',
                'venue' => 'Library Basement Room 1',
                'candidates' => 320,
                'chief_invigilator' => 'Dr. Daniel Otieno',
                'status' => 'Timetabled / Published',
            ],
        ];

        $records = ExamSchedule::query()->with(['subject', 'center', 'invigilator'])->latest('exam_date')->get();
        $schedules = $records->map(fn (ExamSchedule $schedule): array => [
            'paper_code' => $schedule->subject?->code ?? '—', 'course_title' => $schedule->subject?->name ?? '—',
            'date' => $schedule->exam_date->format('d M Y'), 'slot' => $schedule->slot,
            'venue' => $schedule->center?->name ?? '—', 'candidates' => $schedule->candidate_count,
            'chief_invigilator' => $schedule->invigilator?->name ?? 'Unassigned', 'status' => str_replace('_', ' ', $schedule->status),
        ]);
        $stats = [
            'totalScheduledPapers' => $records->count(), 'clashesDetected' => 0,
            'invigilatorsAllocated' => $records->whereNotNull('chief_invigilator_id')->pluck('chief_invigilator_id')->unique()->count(),
            'timetableStatus' => $records->where('status', 'PUBLISHED')->isNotEmpty() ? 'Published' : 'Draft',
        ];

        $examSessions = ExamSession::query()->whereIn('status', ['DRAFT', 'SCHEDULED', 'ACTIVE'])->orderBy('start_date')->get();
        $subjectsForSchedule = Subject::query()->orderBy('code')->get();
        $centersForSchedule = ExamCenter::query()->where('status', 'OPERATIONAL')->orderBy('name')->get();
        $invigilators = User::query()->whereIn('role', ['staff', 'admin'])->where('is_active', true)->orderBy('name')->get();

        return view('examination.exam-schedule', compact('stats', 'schedules', 'examSessions', 'subjectsForSchedule', 'centersForSchedule', 'invigilators'));
    }

    /**
     * 4. Marks Capture
     */
    public function marksCapture(Request $request): View
    {
        $stats = [
            'totalPapersCaptured' => 280,
            'inProgressCapture' => 62,
            'averageCatScore' => '21.4 / 30',
            'averageExamScore' => '44.8 / 70',
        ];

        $captures = [
            [
                'id' => 1,
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'cohort' => 'COH-2024-SEP-MAIN',
                'lecturer' => 'Prof. Peter Ondieki',
                'cat_captured' => '240 / 240 (100%)',
                'exam_captured' => '238 / 240 (99.1%)',
                'average_score' => '68.2% (Pass Grade)',
                'status' => 'Capture Completed',
            ],
            [
                'id' => 2,
                'unit_code' => 'DS-204',
                'unit_title' => 'Machine Learning & Neural Networks',
                'cohort' => 'COH-2025-JAN-INT',
                'lecturer' => 'Dr. Amina Hassan',
                'cat_captured' => '185 / 185 (100%)',
                'exam_captured' => '185 / 185 (100%)',
                'average_score' => '71.5% (Pass Grade)',
                'status' => 'Capture Completed',
            ],
            [
                'id' => 3,
                'unit_code' => 'BBA-201',
                'unit_title' => 'Strategic Human Resource Management',
                'cohort' => 'COH-2024-SEP-MAIN',
                'lecturer' => 'Dr. Daniel Otieno',
                'cat_captured' => '320 / 320 (100%)',
                'exam_captured' => '290 / 320 (90.6%)',
                'average_score' => 'Incomplete (30 Draft)',
                'status' => 'Draft In-Progress',
            ],
        ];

        $subjects = Subject::query()->with(['staff.user', 'course'])->withCount('results')->get();
        $captures = $subjects->map(function (Subject $subject): array {
            $expected = Student::query()->where('course_id', $subject->course_id)->count();
            $results = StudentResult::query()->where('subject_id', $subject->id);
            $captured = (clone $results)->count();
            $mean = (float) (clone $results)->selectRaw('coalesce(avg(test_score + exam_score), 0) as aggregate')->value('aggregate');

            return ['unit_code' => $subject->code ?? '—', 'unit_title' => $subject->name, 'cohort' => $subject->course?->name ?? '—',
                'lecturer' => $subject->staff?->user?->name ?? 'Unassigned', 'cat_captured' => "{$captured} / {$expected}",
                'exam_captured' => "{$captured} / {$expected}", 'average_score' => number_format($mean, 1).'%',
                'status' => $expected > 0 && $captured >= $expected ? 'Capture Completed' : 'Draft In-Progress'];
        });
        $stats = [
            'totalPapersCaptured' => StudentResult::query()->distinct('subject_id')->count('subject_id'),
            'inProgressCapture' => max(0, $subjects->count() - StudentResult::query()->distinct('subject_id')->count('subject_id')),
            'averageCatScore' => number_format((float) StudentResult::query()->avg('test_score'), 1).' / 30',
            'averageExamScore' => number_format((float) StudentResult::query()->avg('exam_score'), 1).' / 70',
        ];

        $studentsForMarks = Student::query()->with('user')->orderBy('admission_number')->get();
        $subjectsForMarks = Subject::query()->orderBy('code')->get();

        return view('examination.marks-capture', compact('stats', 'captures', 'studentsForMarks', 'subjectsForMarks'));
    }

    /**
     * 5. Marks Submission
     */
    public function marksSubmission(Request $request): View
    {
        $stats = [
            'submittedToHOD' => 280,
            'awaitingSubmission' => 62,
            'rejectedForCorrection' => 3,
            'averageModerationTime' => '1.5 Days',
        ];

        $submissions = [
            [
                'id' => 1,
                'submission_ref' => 'SUB-CS301-09',
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'lecturer' => 'Prof. Peter Ondieki',
                'submitted_date' => '20 Dec 2026',
                'total_records' => 240,
                'audit_trail' => 'LMS Sync -> HOD Desk',
                'status' => 'Submitted to HOD Desk',
            ],
            [
                'id' => 2,
                'submission_ref' => 'SUB-DS204-10',
                'unit_code' => 'DS-204',
                'unit_title' => 'Machine Learning & Neural Networks',
                'lecturer' => 'Dr. Amina Hassan',
                'submitted_date' => '22 Dec 2026',
                'total_records' => 185,
                'audit_trail' => 'LMS Sync -> HOD Desk',
                'status' => 'Submitted to HOD Desk',
            ],
            [
                'id' => 3,
                'submission_ref' => 'SUB-BBA201-11',
                'unit_code' => 'BBA-201',
                'unit_title' => 'Strategic Human Resource Management',
                'lecturer' => 'Dr. Daniel Otieno',
                'submitted_date' => 'Awaiting Completion',
                'total_records' => 320,
                'audit_trail' => 'Draft Saved',
                'status' => 'Awaiting Capture Completion',
            ],
        ];

        return view('examination.marks-submission', compact('stats', 'submissions'));
    }

    /**
     * 6. Exam Marks Approval
     */
    public function marksApproval(Request $request): View
    {
        $stats = [
            'moderatedByHOD' => 280,
            'approvedByDeanBoard' => 210,
            'ratifiedBySenate' => 180,
            'pendingApprovalDesk' => 34,
        ];

        $approvals = [
            [
                'id' => 1,
                'approval_ref' => 'APRV-CS301-TR1',
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'department_moderator' => 'Dr. Amina Hassan (HOD)',
                'dean_signoff' => 'Executive Dean SST Approved',
                'senate_ratification' => 'Senate Approved & Signed',
                'security_lock' => 'Marks Locked (Immutable)',
                'status' => 'Senate Ratified',
            ],
            [
                'id' => 2,
                'approval_ref' => 'APRV-DS204-TR1',
                'unit_code' => 'DS-204',
                'unit_title' => 'Machine Learning & Neural Networks',
                'department_moderator' => 'Dr. Amina Hassan (HOD)',
                'dean_signoff' => 'Executive Dean SST Approved',
                'senate_ratification' => 'Pending Senate Board',
                'security_lock' => 'Faculty Moderation Locked',
                'status' => 'Faculty Approved',
            ],
            [
                'id' => 3,
                'approval_ref' => 'APRV-BBA201-TR1',
                'unit_code' => 'BBA-201',
                'unit_title' => 'Strategic Human Resource Management',
                'department_moderator' => 'Dr. Daniel Otieno (HOD)',
                'dean_signoff' => 'Pending Faculty Board Vetting',
                'senate_ratification' => 'Pending',
                'security_lock' => 'Unlocked for HOD Adjustments',
                'status' => 'Department Moderated',
            ],
        ];

        return view('examination.marks-approval', compact('stats', 'approvals'));
    }

    /**
     * 7. Exam Marks Publish
     */
    public function marksPublish(Request $request): View
    {
        $stats = [
            'totalPublishedPapers' => 180,
            'publishedScholarsCount' => 12450,
            'queryRate' => '0.4%',
            'lastPublishTimestamp' => '29-08-2026 07:15',
        ];

        $publications = [
            [
                'id' => 1,
                'publish_code' => 'PUB-2026-T1-CS301',
                'unit_title' => 'Software Engineering Principles (CS-301)',
                'cohort' => 'COH-2024-SEP-MAIN',
                'total_candidates' => 240,
                'grade_distribution' => 'A: 42, B: 110, C: 64, D: 22, F: 2',
                'portal_visibility' => 'Visible to Students',
                'published_by' => 'Registrar Academic (Office)',
                'status' => 'Published & Locked',
            ],
            [
                'id' => 2,
                'publish_code' => 'PUB-2026-T1-DS204',
                'unit_title' => 'Machine Learning & Neural Networks (DS-204)',
                'cohort' => 'COH-2025-JAN-INT',
                'total_candidates' => 185,
                'grade_distribution' => 'A: 48, B: 92, C: 35, D: 10, F: 0',
                'portal_visibility' => 'Visible to Students',
                'published_by' => 'Registrar Academic (Office)',
                'status' => 'Published & Locked',
            ],
            [
                'id' => 3,
                'publish_code' => 'PUB-2026-T1-BBA201',
                'unit_title' => 'Strategic Human Resource Management (BBA-201)',
                'cohort' => 'COH-2024-SEP-MAIN',
                'total_candidates' => 320,
                'grade_distribution' => 'Awaiting Senate Ratification',
                'portal_visibility' => 'Hidden / Draft Marks',
                'published_by' => 'Awaiting Verification',
                'status' => 'Awaiting Approval',
            ],
        ];

        return view('examination.marks-publish', compact('stats', 'publications'));
    }

    /**
     * 8. Class Scores Analysis
     */
    public function scoresAnalysis(Request $request): View
    {
        $stats = [
            'overallPassRate' => '94.8%',
            'failedUnitRate' => '5.2%',
            'highestTrimesterGPA' => '3.94 / 4.00',
            'deanHonoursStanding' => 840,
        ];

        $analyses = [
            [
                'id' => 1,
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'mean_score' => '64.5% (Class B)',
                'median_score' => '66.0%',
                'std_deviation' => '8.2%',
                'highest_score' => '91%',
                'failure_rate' => '0.8% (2 Students)',
                'verdict' => 'Normal Performance Distribution',
            ],
            [
                'id' => 2,
                'unit_code' => 'DS-204',
                'unit_title' => 'Machine Learning & Neural Networks',
                'mean_score' => '71.2% (Class A)',
                'median_score' => '72.5%',
                'std_deviation' => '9.5%',
                'highest_score' => '95%',
                'failure_rate' => '0.0% (0 Students)',
                'verdict' => 'High Standing Performance Distribution',
            ],
            [
                'id' => 3,
                'unit_code' => 'BBA-201',
                'unit_title' => 'Strategic Human Resource Management',
                'mean_score' => '58.8% (Class C)',
                'median_score' => '59.0%',
                'std_deviation' => '11.4%',
                'highest_score' => '82%',
                'failure_rate' => '9.4% (30 Students)',
                'verdict' => 'Requires Supplementary Vetting Board',
            ],
        ];

        return view('examination.scores-analysis', compact('stats', 'analyses'));
    }

    /**
     * 9. Summary of Examination Results
     */
    public function summaryResults(Request $request): View
    {
        $stats = [
            'senateReportRef' => 'SEN-REP-2026-TR1',
            'totalGraduatingClass' => 1480,
            'unconditionalClearance' => 1340,
            'supplementaryCases' => 140,
        ];

        $summaries = [
            [
                'id' => 1,
                'school_name' => 'School of Science & Technology',
                'candidate_enrolled' => 4500,
                'papers_offered' => 120,
                'total_fails' => 45,
                'percentage_pass' => '99.0%',
                'first_class_expected' => 84,
                'senate_standing' => 'Passed & Closed',
            ],
            [
                'id' => 2,
                'school_name' => 'School of Business & Economics',
                'candidate_enrolled' => 5200,
                'papers_offered' => 96,
                'total_fails' => 148,
                'percentage_pass' => '97.1%',
                'first_class_expected' => 42,
                'senate_standing' => 'Passed & Closed',
            ],
            [
                'id' => 3,
                'school_name' => 'School of Education',
                'candidate_enrolled' => 3800,
                'papers_offered' => 74,
                'total_fails' => 64,
                'percentage_pass' => '98.3%',
                'first_class_expected' => 18,
                'senate_standing' => 'Passed & Closed',
            ],
        ];

        return view('examination.summary-results', compact('stats', 'summaries'));
    }

    /**
     * 10. Grades Config
     */
    public function gradesConfig(Request $request): View
    {
        $stats = [
            'gradingPolicyVersion' => 'Senate Grading Policy 2024',
            'accGPAFormula' => 'Standard US/Kenya CGPA (4.0 Scale)',
            'accreditedScales' => 2,
            'modifiedBy' => 'Registrar Academic on 12-09-2024',
        ];

        $scales = [
            [
                'id' => 1,
                'grade_letter' => 'A',
                'min_marks' => 70,
                'max_marks' => 100,
                'gpa_points' => '4.00',
                'performance_descriptor' => 'Excellent / First Class Honours',
                'status' => 'Active Scale',
            ],
            [
                'id' => 2,
                'grade_letter' => 'B',
                'min_marks' => 60,
                'max_marks' => 69,
                'gpa_points' => '3.00',
                'performance_descriptor' => 'Very Good / Second Class Honours (Upper)',
                'status' => 'Active Scale',
            ],
            [
                'id' => 3,
                'grade_letter' => 'C',
                'min_marks' => 50,
                'max_marks' => 59,
                'gpa_points' => '2.00',
                'performance_descriptor' => 'Good / Second Class Honours (Lower)',
                'status' => 'Active Scale',
            ],
            [
                'id' => 4,
                'grade_letter' => 'D',
                'min_marks' => 40,
                'max_marks' => 49,
                'gpa_points' => '1.00',
                'performance_descriptor' => 'Satisfactory / Pass Division',
                'status' => 'Active Scale',
            ],
            [
                'id' => 5,
                'grade_letter' => 'F',
                'min_marks' => 0,
                'max_marks' => 39,
                'gpa_points' => '0.00',
                'performance_descriptor' => 'Fail / Supplementary Required',
                'status' => 'Active Scale',
            ],
        ];

        $records = GradeScale::query()->orderByDesc('min_marks')->get();
        $scales = $records->map(fn (GradeScale $scale): array => [
            'grade_letter' => $scale->grade_letter, 'min_marks' => $scale->min_marks, 'max_marks' => $scale->max_marks,
            'gpa_points' => $scale->gpa_points, 'performance_descriptor' => $scale->performance_descriptor,
            'status' => $scale->is_active ? 'Active Scale' : 'Inactive Scale',
        ]);
        $stats = ['gradingPolicyVersion' => 'Database grading policy', 'accGPAFormula' => 'Configured GPA scale', 'accreditedScales' => $records->where('is_active', true)->count(), 'modifiedBy' => $records->max('updated_at')?->format('d-m-Y') ?? 'Not configured'];

        return view('examination.grades-config', compact('stats', 'scales'));
    }

    /**
     * 11. Pass List
     */
    public function passList(Request $request): View
    {
        $stats = [
            'totalGraduatingScholars' => 1340,
            'mastersPhdGraduands' => 110,
            'undergraduateGraduands' => 1230,
            'clearedForGown' => 1120,
        ];

        $passRecords = [
            [
                'id' => 1,
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'Bachelor of Science in Computer Science',
                'final_cgpa' => '3.45',
                'classification' => 'Second Class Honours (Upper Division)',
                'clearance_status' => 'Fully Cleared (Tuition & Library)',
                'verdict' => 'Cleared for Graduation',
            ],
            [
                'id' => 2,
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'programme' => 'Bachelor of Science in Information Technology',
                'final_cgpa' => '3.20',
                'classification' => 'Second Class Honours (Upper Division)',
                'clearance_status' => 'Fully Cleared (Tuition & Library)',
                'verdict' => 'Cleared for Graduation',
            ],
            [
                'id' => 3,
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'programme' => 'Bachelor of Business Administration',
                'final_cgpa' => '2.95',
                'classification' => 'Second Class Honours (Lower Division)',
                'clearance_status' => 'Library Balance KES 450 Pending',
                'verdict' => 'Awaiting Financial Clearance',
            ],
        ];

        return view('examination.pass-list', compact('stats', 'passRecords'));
    }

    /**
     * 12. Progression List
     */
    public function progressionList(Request $request): View
    {
        $stats = [
            'candidatesReviewed' => 14850,
            'clearedToProgress' => 14200,
            'conditionalProgression' => 605,
            'academicallyBarred' => 45,
        ];

        $progressions = [
            [
                'id' => 1,
                'student_name' => 'Dr. Amina Hassan\'s PG Scholar',
                'reg_no' => 'PHD-CS/2024/0912',
                'programme' => 'PhD in Computer Science',
                'current_stage' => 'Year 1 (Proposal Phase)',
                'target_stage' => 'Year 2 (Seminar Phase)',
                'credits_completed' => 'Passed Proposal Reader Review',
                'verdict' => 'Promoted to Seminar tracking',
            ],
            [
                'id' => 2,
                'student_name' => 'Onyango Kevin Otieno',
                'reg_no' => 'MEMA/BCS/2026/0881',
                'programme' => 'BSc. Computer Science',
                'current_stage' => 'Year 1 Trimester 2',
                'target_stage' => 'Year 1 Trimester 3',
                'credits_completed' => '24 / 24 Credits Cleared',
                'verdict' => 'Promoted (Normal Progression)',
            ],
            [
                'id' => 3,
                'student_name' => 'Wanjiku Mary Njeri',
                'reg_no' => 'MEMA/BBA/2026/0412',
                'programme' => 'Bachelor of Business Administration',
                'current_stage' => 'Year 1 Trimester 2',
                'target_stage' => 'Year 1 Trimester 3 (Trailing)',
                'credits_completed' => '20 / 24 Credits Cleared (1 F Unit)',
                'verdict' => 'Promoted on Academic Warning (Trailing)',
            ],
        ];

        return view('examination.progression-list', compact('stats', 'progressions'));
    }

    /**
     * 13. Fail List
     */
    public function failList(Request $request): View
    {
        $stats = [
            'totalFailsLogged' => 184,
            'supplementaryScheduled' => 139,
            'repeatYearOrders' => 45,
            'feeOutstandingFails' => 0,
        ];

        $fails = [
            [
                'id' => 1,
                'student_name' => 'Kelvin Mwenda Gitonga',
                'reg_no' => 'MEMA/BSC/2023/0744',
                'programme' => 'BSc. Data Analytics',
                'failed_unit' => 'DA-304: Advanced Time-Series Modeling',
                'raw_marks' => '32% (Fail)',
                'supplementary_ref' => 'SUP-DA304-2027',
                'scheduled_date' => '05 May 2027',
                'verdict' => 'Scheduled for Supplementary Examination',
            ],
            [
                'id' => 2,
                'student_name' => 'Brian Ochieng Okoth',
                'reg_no' => 'MEMA/BCS/2024/0119',
                'programme' => 'BSc. Computer Science',
                'failed_unit' => 'CS-302: Compiler Construction',
                'raw_marks' => '28% (Fail)',
                'supplementary_ref' => 'SUP-CS302-2027',
                'scheduled_date' => '05 May 2027',
                'verdict' => 'Scheduled for Supplementary Examination',
            ],
            [
                'id' => 3,
                'student_name' => 'Njoroge Timothy Mwangi',
                'reg_no' => 'MEMA/BED/2023/0440',
                'programme' => 'Bachelor of Education (Arts)',
                'failed_unit' => 'ED-201 & ED-202 (Failed > 4 Units)',
                'raw_marks' => 'Average 18%',
                'supplementary_ref' => 'None (Disqualified)',
                'scheduled_date' => 'Academic Year Repeat',
                'verdict' => 'Repeat Year / Academic Disqualification',
            ],
        ];

        return view('examination.fail-list', compact('stats', 'fails'));
    }

    /**
     * 14. Provisional Transcript
     */
    public function provisionalTranscript(Request $request): View
    {
        $stats = [
            'transcriptsRequested' => 1420,
            'printedToday' => 84,
            'provisionalAccuracy' => '100% System Audited',
            'averageGpa' => '3.12 CGPA',
        ];

        $studentInfo = [
            'name' => 'Brenda Chepkoech',
            'reg_no' => 'MEMA/BCS/2024/0912',
            'programme' => 'Bachelor of Science in Computer Science',
            'school' => 'School of Science and Technology',
            'cohort' => 'COH-2024-SEP-MAIN',
            'academic_year' => 'Year 3 Trimester I (2026/2027)',
            'verdict' => 'Satisfactory Performance',
        ];

        $transcriptLines = [
            [
                'unit_code' => 'CS-301',
                'unit_title' => 'Software Engineering Principles',
                'credit_hours' => '4.0',
                'marks' => '78%',
                'grade' => 'A',
                'gpa_points' => '4.00',
                'status' => 'Passed / Cleared',
            ],
            [
                'unit_code' => 'CS-302',
                'unit_title' => 'Distributed & Cloud Systems',
                'credit_hours' => '4.0',
                'marks' => '65%',
                'grade' => 'B',
                'gpa_points' => '3.00',
                'status' => 'Passed / Cleared',
            ],
            [
                'unit_code' => 'CS-303',
                'unit_title' => 'Mobile Application Development',
                'credit_hours' => '4.0',
                'marks' => '62%',
                'grade' => 'B',
                'gpa_points' => '3.00',
                'status' => 'Passed / Cleared',
            ],
            [
                'unit_code' => 'CS-304',
                'unit_title' => 'Database Administration (SQL/NoSQL)',
                'credit_hours' => '4.0',
                'marks' => '72%',
                'grade' => 'A',
                'gpa_points' => '4.00',
                'status' => 'Passed / Cleared',
            ],
        ];

        return view('examination.provisional-transcript', compact('stats', 'studentInfo', 'transcriptLines'));
    }

    /**
     * 15. Academic Transcript
     */
    public function academicTranscript(Request $request): View
    {
        $stats = [
            'officialTranscriptsIssued' => 840,
            'sealedDiplomas' => 840,
            'academicStanding' => 'Senate Approved',
            'securityFeatures' => 'NFC Smart Chip & UV Watermark',
        ];

        $studentInfo = [
            'name' => 'Emmanuel Kiprono Mutai',
            'reg_no' => 'MEMA/BIT/2023/1104',
            'programme' => 'Bachelor of Science in Information Technology',
            'school' => 'School of Science and Technology',
            'specialization' => 'Network Security & Infrastructure',
            'award' => 'Second Class Honours (Upper Division)',
            'senate_approval_date' => '10 Dec 2026',
        ];

        $transcriptSemesters = [
            [
                'semester_name' => 'Year 1 Trimester I (Sept - Dec 2023)',
                'units' => [
                    ['code' => 'BIT-101', 'title' => 'Introduction to ICT Systems', 'grade' => 'A', 'points' => '4.00'],
                    ['code' => 'BIT-102', 'title' => 'Computer Hardware Principles', 'grade' => 'B', 'points' => '3.00'],
                    ['code' => 'BIT-103', 'title' => 'Structured Programming in C', 'grade' => 'A', 'points' => '4.00'],
                ],
            ],
            [
                'semester_name' => 'Year 1 Trimester II (Jan - Apr 2024)',
                'units' => [
                    ['code' => 'BIT-104', 'title' => 'Database Design & Management', 'grade' => 'B', 'points' => '3.00'],
                    ['code' => 'BIT-105', 'title' => 'Web Design Technologies', 'grade' => 'A', 'points' => '4.00'],
                    ['code' => 'BIT-106', 'title' => 'Discrete Mathematics', 'grade' => 'C', 'points' => '2.00'],
                ],
            ],
        ];

        return view('examination.academic-transcript', compact('stats', 'studentInfo', 'transcriptSemesters'));
    }

    /**
     * 16. Transcript Requests
     */
    public function transcriptRequests(Request $request): View
    {
        $stats = [
            'activeRequests' => 45,
            'processedToday' => 18,
            'revenueCollected' => 'KES 22,500',
            'averageProcessingDays' => '2.5 Days',
        ];

        $requests = [
            [
                'id' => 1,
                'request_no' => 'TRQ-2027-0114',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'transcript_type' => 'Provisional Transcript (Year 3)',
                'payment_ref' => 'MPESA-QRT8912A (KES 500)',
                'dispatch_method' => 'Email Delivery (PDF)',
                'request_date' => '22 Feb 2027',
                'status' => 'Processed & Emailed',
            ],
            [
                'id' => 2,
                'request_no' => 'TRQ-2027-0115',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'transcript_type' => 'Official Academic Transcript (Final)',
                'payment_ref' => 'MPESA-QRT8913B (KES 1,000)',
                'dispatch_method' => 'Courier (DHL Express)',
                'request_date' => '24 Feb 2027',
                'status' => 'Processing / Printing',
            ],
            [
                'id' => 3,
                'request_no' => 'TRQ-2027-0116',
                'student_name' => 'Faith Muthoni Ndirangu',
                'reg_no' => 'MEMA/BBA/2024/0831',
                'transcript_type' => 'Provisional Transcript (Year 2)',
                'payment_ref' => 'Awaiting Payment Proof',
                'dispatch_method' => 'Registry Collection',
                'request_date' => '26 Feb 2027',
                'status' => 'Awaiting Payment Verification',
            ],
        ];

        return view('examination.transcript-requests', compact('stats', 'requests'));
    }

    /**
     * 17. Exam Senate Reports
     */
    public function senateReports(Request $request): View
    {
        $stats = [
            'totalSenateReports' => 24,
            'approvedReports' => 22,
            'pendingSenateSignoff' => 2,
            'lastSenateMeeting' => '18 Feb 2027',
        ];

        $reports = [
            [
                'id' => 1,
                'report_code' => 'SEN-REP-2027-01',
                'title' => 'School of Science & Technology Trimester I Graduating List',
                'academic_year' => '2026/2027',
                'trimester' => 'Trimester I',
                'dean_sign_off' => 'Dr. Amina Hassan (Dean)',
                'total_candidates' => 450,
                'status' => 'Senate Approved',
            ],
            [
                'id' => 2,
                'report_code' => 'SEN-REP-2027-02',
                'title' => 'School of Business & Economics Trimester I Honors List',
                'academic_year' => '2026/2027',
                'trimester' => 'Trimester I',
                'dean_sign_off' => 'Prof. Joseph Okumu (Dean)',
                'total_candidates' => 580,
                'status' => 'Senate Approved',
            ],
            [
                'id' => 3,
                'report_code' => 'SEN-REP-2027-03',
                'title' => 'School of Education Trimester II Progression Summary',
                'academic_year' => '2026/2027',
                'trimester' => 'Trimester II',
                'dean_sign_off' => 'Dr. Grace Njeri (Dean)',
                'total_candidates' => 320,
                'status' => 'Pending Senate Board',
            ],
        ];

        return view('examination.senate-reports', compact('stats', 'reports'));
    }

    /**
     * 18. Consolidated Marksheets
     */
    public function consolidatedMarksheets(Request $request): View
    {
        $stats = [
            'consolidatedCount' => 84,
            'completedSheets' => 80,
            'draftSheets' => 4,
            'auditValidation' => '100% Validated',
        ];

        $marksheets = [
            [
                'id' => 1,
                'marksheet_ref' => 'CMS-BCS-Y3-2027',
                'programme' => 'BSc. Computer Science',
                'cohort' => 'COH-2024-SEP-MAIN',
                'academic_year' => 'Year 3 (2026/2027)',
                'enrolled_students' => 240,
                'units_count' => '8 Core Units',
                'status' => 'Finalized & HOD Signed',
            ],
            [
                'id' => 2,
                'marksheet_ref' => 'CMS-BIT-Y2-2027',
                'programme' => 'BSc. Information Technology',
                'cohort' => 'COH-2025-JAN-INT',
                'academic_year' => 'Year 2 (2026/2027)',
                'enrolled_students' => 185,
                'units_count' => '8 Core Units',
                'status' => 'Finalized & HOD Signed',
            ],
            [
                'id' => 3,
                'marksheet_ref' => 'CMS-BBA-Y3-2027',
                'programme' => 'Bachelor of Business Administration',
                'cohort' => 'COH-2024-SEP-MAIN',
                'academic_year' => 'Year 3 (2026/2027)',
                'enrolled_students' => 320,
                'units_count' => '8 Core Units',
                'status' => 'Draft / Awaiting Vetting',
            ],
        ];

        return view('examination.consolidated-marksheets', compact('stats', 'marksheets'));
    }

    public function storeCenter(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $center = ExamCenter::create($request->validate([
            'center_code' => ['required', 'string', 'max:40', 'unique:exam_centers,center_code'], 'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'], 'capacity' => ['required', 'integer', 'min:1'],
            'proctors_allocated' => ['required', 'integer', 'min:0'], 'special_needs_access' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['OPERATIONAL', 'MAINTENANCE', 'INACTIVE'])],
        ]));
        AuditLog::record('examination.center_created', $center, null, $center->toArray());

        return back()->with('success', 'Examination center created.');
    }

    public function storeSession(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $session = ExamSession::create($request->validate([
            'session_code' => ['required', 'string', 'max:40', 'unique:exam_sessions,session_code'], 'session_title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after:start_date'], 'daily_slots' => ['required', 'integer', 'between:1,6'],
            'moderation_deadline' => ['required', 'date', 'after:end_date'], 'status' => ['required', Rule::in(['DRAFT', 'SCHEDULED', 'ACTIVE', 'CLOSED'])],
        ]));
        AuditLog::record('examination.session_created', $session, null, $session->toArray());

        return back()->with('success', 'Examination session created.');
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $schedule = ExamSchedule::create($request->validate([
            'exam_session_id' => ['required', 'exists:exam_sessions,id'], 'subject_id' => ['required', 'exists:subjects,id'],
            'exam_center_id' => ['required', 'exists:exam_centers,id'], 'chief_invigilator_id' => ['nullable', 'exists:users,id'],
            'exam_date' => ['required', 'date'], 'slot' => ['required', 'string', 'max:30'], 'candidate_count' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['SCHEDULED', 'PUBLISHED', 'COMPLETED', 'CANCELLED'])],
        ]));
        AuditLog::record('examination.schedule_created', $schedule, null, $schedule->toArray());

        return back()->with('success', 'Examination paper scheduled.');
    }

    public function storeGradeScale(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'grade_letter' => ['required', 'string', 'max:5', 'unique:grade_scales,grade_letter'], 'min_marks' => ['required', 'numeric', 'between:0,100'],
            'max_marks' => ['required', 'numeric', 'between:0,100', 'gte:min_marks'], 'gpa_points' => ['required', 'numeric', 'between:0,5'],
            'performance_descriptor' => ['required', 'string', 'max:255'],
        ]);
        abort_if(GradeScale::query()->where('min_marks', '<=', $data['max_marks'])->where('max_marks', '>=', $data['min_marks'])->exists(), 422, 'The mark range overlaps an existing grade.');
        $scale = GradeScale::create($data + ['is_active' => true]);
        AuditLog::record('examination.grade_scale_created', $scale, null, $scale->toArray());

        return back()->with('success', 'Grade scale added.');
    }

    public function storeResult(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'], 'subject_id' => ['required', 'exists:subjects,id'],
            'test_score' => ['required', 'numeric', 'between:0,30'], 'exam_score' => ['required', 'numeric', 'between:0,70'],
        ]);
        $subject = Subject::query()->with('staff')->findOrFail($data['subject_id']);
        abort_unless($request->user()->isAdmin() || $subject->staff?->user_id === $request->user()->id, 403);
        $student = Student::query()->findOrFail($data['student_id']);
        abort_if($student->course_id !== $subject->course_id, 422, 'The student is not enrolled in the subject programme.');
        $result = StudentResult::query()->updateOrCreate(['student_id' => $data['student_id'], 'subject_id' => $data['subject_id']], $data);
        AuditLog::record('examination.result_saved', $result, null, $result->toArray());

        return back()->with('success', 'Student marks saved.');
    }
}
