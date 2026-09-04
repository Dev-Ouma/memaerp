<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Course;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ExaminationController extends Controller
{
    public function examCenter(Request $request): View
    {
        $centers = ExamCenter::query()->orderBy('center_code')->get();
        $stats = [
            'totalCenters' => $centers->count(),
            'hallCapacity' => (int) $centers->sum('capacity'),
            'activeRooms' => $centers->where('status', 'OPERATIONAL')->count(),
            'virtualProctoringNodes' => $centers->filter(
                fn (ExamCenter $center): bool => str_contains(strtolower((string) $center->location), 'virtual')
            )->count(),
        ];

        return view('examination.exam-center', compact('stats', 'centers'));
    }

    public function examSession(Request $request): View
    {
        $records = ExamSession::query()->withSum('schedules', 'candidate_count')->latest('start_date')->get();
        $sessions = $records->map(fn (ExamSession $session): array => [
            'session_code' => $session->session_code,
            'session_title' => $session->session_title,
            'start_date' => $session->start_date->format('d M Y'),
            'end_date' => $session->end_date->format('d M Y'),
            'daily_slots' => $session->daily_slots,
            'candidate_count' => (int) $session->schedules_sum_candidate_count,
            'moderation_deadline' => $session->moderation_deadline?->format('d M Y') ?? '—',
            'status' => match ($session->status) {
                'ACTIVE' => 'Active Session / Configured',
                'SCHEDULED' => 'Scheduled / Upcoming',
                'CLOSED' => 'Exam Completed & Closed',
                default => str_replace('_', ' ', (string) $session->status),
            },
        ]);
        $current = $records->first(
            fn (ExamSession $session): bool => $session->start_date->lte(today()) && $session->end_date->gte(today())
        );
        $stats = [
            'currentSession' => $current?->session_title ?? 'No active examination session',
            'durationWeeks' => $current ? $current->start_date->diffInWeeks($current->end_date).' Weeks' : '—',
            'registeredCandidates' => Student::query()->count(),
            'examinationSLA' => $records->where('moderation_deadline', '<', today())->whereNotIn('status', ['CLOSED'])->isEmpty()
                ? '100% Compliant'
                : 'Action Required',
        ];

        return view('examination.exam-session', compact('stats', 'sessions'));
    }

    public function examSchedule(Request $request): View
    {
        $records = ExamSchedule::query()->with(['subject', 'center', 'invigilator'])->latest('exam_date')->get();
        $schedules = $records->map(fn (ExamSchedule $schedule): array => [
            'paper_code' => $schedule->subject?->code ?? '—',
            'course_title' => $schedule->subject?->name ?? '—',
            'date' => $schedule->exam_date->format('d M Y'),
            'slot' => $schedule->slot,
            'venue' => $schedule->center?->name ?? '—',
            'candidates' => $schedule->candidate_count,
            'chief_invigilator' => $schedule->invigilator?->name ?? 'Unassigned',
            'status' => str_replace('_', ' ', $schedule->status),
        ]);
        $stats = [
            'totalScheduledPapers' => $records->count(),
            'clashesDetected' => 0,
            'invigilatorsAllocated' => $records->whereNotNull('chief_invigilator_id')->pluck('chief_invigilator_id')->unique()->count(),
            'timetableStatus' => $records->where('status', 'PUBLISHED')->isNotEmpty() ? 'Published' : 'Draft',
        ];

        $examSessions = ExamSession::query()->whereIn('status', ['DRAFT', 'SCHEDULED', 'ACTIVE'])->orderBy('start_date')->get();
        $subjectsForSchedule = Subject::query()->orderBy('code')->get();
        $centersForSchedule = ExamCenter::query()->where('status', 'OPERATIONAL')->orderBy('name')->get();
        $invigilators = User::query()->whereIn('role', ['staff', 'admin'])->where('is_active', true)->orderBy('name')->get();

        return view('examination.exam-schedule', compact('stats', 'schedules', 'examSessions', 'subjectsForSchedule', 'centersForSchedule', 'invigilators'));
    }

    public function marksCapture(Request $request): View
    {
        $subjects = Subject::query()->with(['staff.user', 'course'])->get();
        $captures = $subjects->map(fn (Subject $subject): array => $this->subjectCaptureRow($subject));
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

    public function marksSubmission(Request $request): View
    {
        $subjects = Subject::query()->with(['staff.user', 'course'])->get();
        $submissions = $subjects->map(function (Subject $subject): array {
            $row = $this->subjectCaptureRow($subject);
            $captured = (int) StudentResult::query()->where('subject_id', $subject->id)->count();

            return [
                'submission_ref' => 'SUB-'.strtoupper((string) $subject->code).'-'.str_pad((string) $subject->id, 3, '0', STR_PAD_LEFT),
                'unit_code' => $row['unit_code'],
                'unit_title' => $row['unit_title'],
                'lecturer' => $row['lecturer'],
                'submitted_date' => $captured > 0
                    ? Carbon::parse(
                        (string) StudentResult::query()->where('subject_id', $subject->id)->max('updated_at')
                    )->format('d M Y')
                    : 'Awaiting Completion',
                'total_records' => $captured,
                'audit_trail' => $captured > 0 ? 'Marks Capture -> HOD Desk' : 'Draft Saved',
                'status' => $row['status'] === 'Capture Completed' ? 'Submitted to HOD Desk' : 'Awaiting Capture Completion',
            ];
        });
        $submitted = $submissions->filter(fn (array $row): bool => str_contains($row['status'], 'Submitted'))->count();
        $stats = [
            'submittedToHOD' => $submitted,
            'awaitingSubmission' => max(0, $submissions->count() - $submitted),
            'rejectedForCorrection' => 0,
            'averageModerationTime' => '—',
        ];

        return view('examination.marks-submission', compact('stats', 'submissions'));
    }

    public function marksApproval(Request $request): View
    {
        $subjects = Subject::query()->with(['staff.user', 'course'])->get();
        $approvals = $subjects->map(function (Subject $subject): array {
            $row = $this->subjectCaptureRow($subject);
            $complete = $row['status'] === 'Capture Completed';

            return [
                'approval_ref' => 'APRV-'.strtoupper((string) $subject->code),
                'unit_code' => $row['unit_code'],
                'unit_title' => $row['unit_title'],
                'department_moderator' => $row['lecturer'],
                'dean_signoff' => $complete ? 'Ready for faculty board' : 'Pending capture completion',
                'senate_ratification' => $complete ? 'Pending Senate Board' : 'Pending',
                'security_lock' => $complete ? 'Faculty Moderation Locked' : 'Unlocked for HOD Adjustments',
                'status' => $complete ? 'Faculty Approved' : 'Department Moderated',
            ];
        });
        $complete = $approvals->where('status', 'Faculty Approved')->count();
        $stats = [
            'moderatedByHOD' => $complete,
            'approvedByDeanBoard' => 0,
            'ratifiedBySenate' => 0,
            'pendingApprovalDesk' => max(0, $approvals->count() - $complete),
        ];

        return view('examination.marks-approval', compact('stats', 'approvals'));
    }

    public function marksPublish(Request $request): View
    {
        $subjects = Subject::query()->with('course')->get();
        $publications = $subjects->map(function (Subject $subject): array {
            $results = StudentResult::query()->where('subject_id', $subject->id)->get();
            $distribution = $this->gradeDistribution($results);

            return [
                'publish_code' => 'PUB-'.$subject->code,
                'unit_title' => $subject->name.' ('.$subject->code.')',
                'cohort' => $subject->course?->name ?? '—',
                'total_candidates' => $results->count(),
                'grade_distribution' => $distribution === [] ? 'No marks captured' : implode(', ', $distribution),
                'portal_visibility' => $results->isNotEmpty() ? 'Visible to Students' : 'Hidden / Draft Marks',
                'published_by' => $results->isNotEmpty() ? 'Registrar Academic (Office)' : 'Awaiting Verification',
                'status' => $results->isNotEmpty() ? 'Published & Locked' : 'Awaiting Approval',
            ];
        });
        $published = $publications->where('status', 'Published & Locked');
        $stats = [
            'totalPublishedPapers' => $published->count(),
            'publishedScholarsCount' => StudentResult::query()->distinct('student_id')->count('student_id'),
            'queryRate' => '0%',
            'lastPublishTimestamp' => StudentResult::query()->max('updated_at') ?? 'Not published',
        ];

        return view('examination.marks-publish', compact('stats', 'publications'));
    }

    public function scoresAnalysis(Request $request): View
    {
        $subjects = Subject::query()->get();
        $analyses = $subjects->map(function (Subject $subject): array {
            $totals = StudentResult::query()
                ->where('subject_id', $subject->id)
                ->get()
                ->map(fn (StudentResult $result): float => $result->total);
            $mean = $totals->avg() ?? 0.0;
            $failures = $totals->filter(fn (float $total): bool => $total < 40)->count();

            return [
                'unit_code' => $subject->code ?? '—',
                'unit_title' => $subject->name,
                'mean_score' => number_format($mean, 1).'%',
                'median_score' => number_format($this->median($totals), 1).'%',
                'std_deviation' => number_format($this->stdDev($totals), 1).'%',
                'highest_score' => number_format((float) ($totals->max() ?? 0), 0).'%',
                'failure_rate' => $totals->count() === 0
                    ? '0% (0 Students)'
                    : number_format(($failures / max(1, $totals->count())) * 100, 1).'% ('.$failures.' Students)',
                'verdict' => $totals->isEmpty()
                    ? 'No marks captured'
                    : ($failures > 0 ? 'Requires Supplementary Vetting Board' : 'Normal Performance Distribution'),
            ];
        });
        $allTotals = StudentResult::query()->get()->map(fn (StudentResult $result): float => $result->total);
        $passRate = $allTotals->isEmpty()
            ? 0.0
            : (($allTotals->count() - $allTotals->filter(fn (float $total): bool => $total < 40)->count()) / $allTotals->count()) * 100;
        $stats = [
            'overallPassRate' => number_format($passRate, 1).'%',
            'failedUnitRate' => number_format(100 - $passRate, 1).'%',
            'highestTrimesterGPA' => number_format(min(4.0, ($allTotals->max() ?? 0) / 25), 2).' / 4.00',
            'deanHonoursStanding' => $allTotals->filter(fn (float $total): bool => $total >= 70)->count(),
        ];

        return view('examination.scores-analysis', compact('stats', 'analyses'));
    }

    public function summaryResults(Request $request): View
    {
        $summaries = Course::query()->withCount(['students', 'subjects'])->get()->map(function (Course $course): array {
            $subjectIds = $course->subjects()->pluck('id');
            $results = StudentResult::query()->whereIn('subject_id', $subjectIds)->get();
            $fails = $results->filter(fn (StudentResult $result): bool => $result->total < 40)->count();
            $passPct = $results->isEmpty()
                ? 0.0
                : (($results->count() - $fails) / $results->count()) * 100;

            return [
                'school_name' => $course->name,
                'candidate_enrolled' => $course->students_count,
                'papers_offered' => $course->subjects_count,
                'total_fails' => $fails,
                'percentage_pass' => number_format($passPct, 1).'%',
                'first_class_expected' => $results->filter(fn (StudentResult $result): bool => $result->total >= 70)->count(),
                'senate_standing' => $results->isEmpty() ? 'Awaiting Marks' : 'Passed & Closed',
            ];
        });
        $stats = [
            'senateReportRef' => 'SEN-REP-DB',
            'totalGraduatingClass' => Student::query()->count(),
            'unconditionalClearance' => StudentResult::query()->distinct('student_id')->count('student_id'),
            'supplementaryCases' => StudentResult::query()->get()->filter(fn (StudentResult $result): bool => $result->total < 40)->count(),
        ];

        return view('examination.summary-results', compact('stats', 'summaries'));
    }

    public function gradesConfig(Request $request): View
    {
        $records = GradeScale::query()->orderByDesc('min_marks')->get();
        $scales = $records->map(fn (GradeScale $scale): array => [
            'grade_letter' => $scale->grade_letter,
            'min_marks' => $scale->min_marks,
            'max_marks' => $scale->max_marks,
            'gpa_points' => $scale->gpa_points,
            'performance_descriptor' => $scale->performance_descriptor,
            'status' => $scale->is_active ? 'Active Scale' : 'Inactive Scale',
        ]);
        $stats = [
            'gradingPolicyVersion' => 'Database grading policy',
            'accGPAFormula' => 'Configured GPA scale',
            'accreditedScales' => $records->where('is_active', true)->count(),
            'modifiedBy' => $records->max('updated_at')?->format('d-m-Y') ?? 'Not configured',
        ];

        return view('examination.grades-config', compact('stats', 'scales'));
    }

    public function passList(Request $request): View
    {
        $passRecords = Student::query()->with(['user', 'course'])->orderBy('admission_number')->get()->map(function (Student $student): array {
            $results = StudentResult::query()->where('student_id', $student->id)->get();
            $mean = (float) ($results->avg(fn (StudentResult $result): float => $result->total) ?? 0);
            $failed = $results->contains(fn (StudentResult $result): bool => $result->total < 40);

            return [
                'student_name' => $student->user?->name ?? '—',
                'reg_no' => $student->admission_number,
                'programme' => $student->course?->name ?? '—',
                'final_cgpa' => number_format(min(4.0, $mean / 25), 2),
                'classification' => $this->classification($mean),
                'clearance_status' => $results->isEmpty() ? 'No marks captured' : 'Academic record on file',
                'verdict' => $results->isEmpty()
                    ? 'Awaiting Results'
                    : ($failed ? 'Awaiting Financial Clearance' : 'Cleared for Graduation'),
            ];
        })->filter(fn (array $row): bool => $row['verdict'] === 'Cleared for Graduation')->values();

        $stats = [
            'totalGraduatingScholars' => $passRecords->count(),
            'mastersPhdGraduands' => 0,
            'undergraduateGraduands' => $passRecords->count(),
            'clearedForGown' => $passRecords->count(),
        ];

        return view('examination.pass-list', compact('stats', 'passRecords'));
    }

    public function progressionList(Request $request): View
    {
        $progressions = Student::query()->with(['user', 'course'])->orderBy('admission_number')->get()->map(function (Student $student): array {
            $results = StudentResult::query()->where('student_id', $student->id)->get();
            $failed = $results->filter(fn (StudentResult $result): bool => $result->total < 40)->count();

            return [
                'student_name' => $student->user?->name ?? '—',
                'reg_no' => $student->admission_number,
                'programme' => $student->course?->name ?? '—',
                'current_stage' => 'Enrolled',
                'target_stage' => $failed > 0 ? 'Progress with trailing unit(s)' : 'Next academic stage',
                'credits_completed' => $results->count().' unit result(s) on file',
                'verdict' => $failed > 0
                    ? 'Promoted on Academic Warning (Trailing)'
                    : ($results->isEmpty() ? 'Awaiting Marks' : 'Promoted (Normal Progression)'),
            ];
        });
        $stats = [
            'candidatesReviewed' => $progressions->count(),
            'clearedToProgress' => $progressions->where('verdict', 'Promoted (Normal Progression)')->count(),
            'conditionalProgression' => $progressions->filter(fn (array $row): bool => str_contains($row['verdict'], 'Warning'))->count(),
            'academicallyBarred' => 0,
        ];

        return view('examination.progression-list', compact('stats', 'progressions'));
    }

    public function failList(Request $request): View
    {
        $fails = StudentResult::query()->with(['student.user', 'student.course', 'subject'])->get()
            ->filter(fn (StudentResult $result): bool => $result->total < 40)
            ->map(fn (StudentResult $result): array => [
                'student_name' => $result->student?->user?->name ?? '—',
                'reg_no' => $result->student?->admission_number ?? '—',
                'programme' => $result->student?->course?->name ?? '—',
                'failed_unit' => ($result->subject?->code ?? '—').': '.($result->subject?->name ?? '—'),
                'raw_marks' => number_format($result->total, 0).'% (Fail)',
                'supplementary_ref' => 'SUP-'.strtoupper((string) ($result->subject?->code ?? 'UNIT')),
                'scheduled_date' => 'Pending schedule',
                'verdict' => 'Scheduled for Supplementary Examination',
            ])->values();
        $stats = [
            'totalFailsLogged' => $fails->count(),
            'supplementaryScheduled' => $fails->count(),
            'repeatYearOrders' => 0,
            'feeOutstandingFails' => 0,
        ];

        return view('examination.fail-list', compact('stats', 'fails'));
    }

    public function provisionalTranscript(Request $request): View
    {
        [$studentInfo, $transcriptLines, $stats] = $this->transcriptPayload(provisional: true);

        return view('examination.provisional-transcript', compact('stats', 'studentInfo', 'transcriptLines'));
    }

    public function academicTranscript(Request $request): View
    {
        [$studentInfo, $transcriptLines] = $this->transcriptPayload(provisional: false);
        $transcriptSemesters = [[
            'semester_name' => 'Recorded unit results',
            'units' => $transcriptLines->map(fn (array $line): array => [
                'code' => $line['unit_code'],
                'title' => $line['unit_title'],
                'grade' => $line['grade'],
                'points' => $line['gpa_points'],
            ])->all(),
        ]];
        if ($transcriptLines->isEmpty()) {
            $transcriptSemesters = [];
        }
        $stats = [
            'officialTranscriptsIssued' => StudentResult::query()->distinct('student_id')->count('student_id'),
            'sealedDiplomas' => 0,
            'academicStanding' => $transcriptLines->isEmpty() ? 'Awaiting Results' : 'On file',
            'securityFeatures' => 'Registry-issued document',
        ];

        return view('examination.academic-transcript', compact('stats', 'studentInfo', 'transcriptSemesters'));
    }

    public function transcriptRequests(Request $request): View
    {
        $requests = collect();
        $stats = [
            'activeRequests' => 0,
            'processedToday' => 0,
            'revenueCollected' => 'KES 0',
            'averageProcessingDays' => '—',
        ];

        return view('examination.transcript-requests', compact('stats', 'requests'));
    }

    public function senateReports(Request $request): View
    {
        $reports = Course::query()->withCount('students')->orderBy('name')->get()->map(fn (Course $course): array => [
            'report_code' => 'SEN-'.strtoupper((string) $course->code),
            'title' => $course->name.' examination summary',
            'academic_year' => now()->format('Y').'/'.now()->addYear()->format('Y'),
            'trimester' => 'Current',
            'dean_sign_off' => 'Pending',
            'total_candidates' => $course->students_count,
            'status' => 'Pending Senate Board',
        ]);
        $stats = [
            'totalSenateReports' => $reports->count(),
            'approvedReports' => 0,
            'pendingSenateSignoff' => $reports->count(),
            'lastSenateMeeting' => 'Not scheduled',
        ];

        return view('examination.senate-reports', compact('stats', 'reports'));
    }

    public function consolidatedMarksheets(Request $request): View
    {
        $marksheets = Course::query()->withCount(['students', 'subjects'])->orderBy('code')->get()->map(fn (Course $course): array => [
            'marksheet_ref' => 'CMS-'.strtoupper((string) $course->code),
            'programme' => $course->name,
            'cohort' => $course->code,
            'academic_year' => now()->format('Y').'/'.now()->addYear()->format('Y'),
            'enrolled_students' => $course->students_count,
            'units_count' => $course->subjects_count.' Core Units',
            'status' => StudentResult::query()->whereIn('subject_id', $course->subjects()->pluck('id'))->exists()
                ? 'Finalized & HOD Signed'
                : 'Draft / Awaiting Vetting',
        ]);
        $completed = $marksheets->filter(fn (array $row): bool => str_contains($row['status'], 'Finalized'))->count();
        $stats = [
            'consolidatedCount' => $marksheets->count(),
            'completedSheets' => $completed,
            'draftSheets' => max(0, $marksheets->count() - $completed),
            'auditValidation' => $marksheets->isEmpty() ? 'No sheets' : 'Database-backed',
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

    /** @return array{unit_code: string, unit_title: string, cohort: string, lecturer: string, cat_captured: string, exam_captured: string, average_score: string, status: string} */
    private function subjectCaptureRow(Subject $subject): array
    {
        $expected = Student::query()->where('course_id', $subject->course_id)->count();
        $results = StudentResult::query()->where('subject_id', $subject->id);
        $captured = (clone $results)->count();
        $mean = (float) (clone $results)->selectRaw('coalesce(avg(test_score + exam_score), 0) as aggregate')->value('aggregate');

        return [
            'unit_code' => $subject->code ?? '—',
            'unit_title' => $subject->name,
            'cohort' => $subject->course?->name ?? '—',
            'lecturer' => $subject->staff?->user?->name ?? 'Unassigned',
            'cat_captured' => "{$captured} / {$expected}",
            'exam_captured' => "{$captured} / {$expected}",
            'average_score' => number_format($mean, 1).'%',
            'status' => $expected > 0 && $captured >= $expected ? 'Capture Completed' : 'Draft In-Progress',
        ];
    }

    /** @return array{0: array<string, string>, 1: Collection<int, array<string, string>>, 2: array<string, string|int>} */
    private function transcriptPayload(bool $provisional): array
    {
        $student = Student::query()->with(['user', 'course'])->whereHas('results')->orderBy('admission_number')->first()
            ?? Student::query()->with(['user', 'course'])->orderBy('admission_number')->first();

        $results = $student
            ? StudentResult::query()->with('subject')->where('student_id', $student->id)->get()
            : collect();

        $lines = $results->map(function (StudentResult $result): array {
            $total = $result->total;
            $grade = $this->letterFor($total);

            return [
                'unit_code' => $result->subject?->code ?? '—',
                'unit_title' => $result->subject?->name ?? '—',
                'credit_hours' => '—',
                'marks' => number_format($total, 0).'%',
                'grade' => $grade['letter'],
                'gpa_points' => $grade['points'],
                'status' => $total >= 40 ? 'Passed / Cleared' : 'Fail / Supplementary',
            ];
        });

        $mean = (float) ($results->avg(fn (StudentResult $result): float => $result->total) ?? 0);
        $studentInfo = [
            'name' => $student?->user?->name ?? 'No student selected',
            'reg_no' => $student?->admission_number ?? '—',
            'programme' => $student?->course?->name ?? '—',
            'school' => $student?->course?->name ?? '—',
            'cohort' => $student?->course?->code ?? '—',
            'academic_year' => $provisional ? 'Current enrolment' : 'Programme record',
            'verdict' => $lines->isEmpty() ? 'Awaiting Results' : $this->classification($mean),
            'specialization' => $student?->course?->name ?? '—',
            'award' => $this->classification($mean),
            'senate_approval_date' => $lines->isEmpty() ? 'Pending' : now()->format('d M Y'),
        ];
        $stats = [
            'transcriptsRequested' => StudentResult::query()->distinct('student_id')->count('student_id'),
            'printedToday' => 0,
            'provisionalAccuracy' => 'Database-backed',
            'averageGpa' => number_format(min(4.0, $mean / 25), 2).' CGPA',
        ];

        return [$studentInfo, $lines, $stats];
    }

    /** @param Collection<int, StudentResult> $results @return list<string> */
    private function gradeDistribution(Collection $results): array
    {
        $buckets = [];
        foreach ($results as $result) {
            $letter = $this->letterFor($result->total)['letter'];
            $buckets[$letter] = ($buckets[$letter] ?? 0) + 1;
        }
        ksort($buckets);

        return collect($buckets)->map(fn (int $count, string $letter): string => "{$letter}: {$count}")->values()->all();
    }

    /** @return array{letter: string, points: string} */
    private function letterFor(float $total): array
    {
        $scale = GradeScale::query()
            ->where('is_active', true)
            ->where('min_marks', '<=', $total)
            ->where('max_marks', '>=', $total)
            ->orderByDesc('min_marks')
            ->first();

        if ($scale !== null) {
            return ['letter' => $scale->grade_letter, 'points' => number_format((float) $scale->gpa_points, 2)];
        }

        return match (true) {
            $total >= 70 => ['letter' => 'A', 'points' => '4.00'],
            $total >= 60 => ['letter' => 'B', 'points' => '3.00'],
            $total >= 50 => ['letter' => 'C', 'points' => '2.00'],
            $total >= 40 => ['letter' => 'D', 'points' => '1.00'],
            default => ['letter' => 'F', 'points' => '0.00'],
        };
    }

    private function classification(float $mean): string
    {
        return match (true) {
            $mean >= 70 => 'First Class Honours',
            $mean >= 60 => 'Second Class Honours (Upper Division)',
            $mean >= 50 => 'Second Class Honours (Lower Division)',
            $mean >= 40 => 'Pass Division',
            default => 'Fail / Incomplete',
        };
    }

    /** @param Collection<int, float> $values */
    private function median(Collection $values): float
    {
        if ($values->isEmpty()) {
            return 0.0;
        }
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        $mid = intdiv($count, 2);

        return $count % 2 === 0
            ? ((float) $sorted[$mid - 1] + (float) $sorted[$mid]) / 2
            : (float) $sorted[$mid];
    }

    /** @param Collection<int, float> $values */
    private function stdDev(Collection $values): float
    {
        if ($values->count() < 2) {
            return 0.0;
        }
        $mean = (float) $values->avg();
        $variance = $values->map(fn (float $value): float => ($value - $mean) ** 2)->avg() ?? 0.0;

        return sqrt((float) $variance);
    }
}
