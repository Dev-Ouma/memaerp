<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\LmsAssignment;
use App\Models\LmsCourseShell;
use App\Models\LmsDiscussionThread;
use App\Models\LmsEResource;
use App\Models\LmsGradebookSync;
use App\Models\LmsLecturerAssignment;
use App\Models\LmsLiveLecture;
use App\Models\LmsOnlineQuiz;
use App\Models\LmsStudentAnalytic;
use App\Support\SoftStatsBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LmsController extends Controller
{
    use AuthorizesCataloguePermission;

    public function courseShells(Request $request): View
    {
        $records = LmsCourseShell::query()->latest()->get();
        $shells = $records->map(fn (LmsCourseShell $row): array => [
            'shell_code' => $row->shell_code,
            'course_title' => $row->course_title,
            'faculty' => $row->faculty ?? '—',
            'instructor' => $row->instructor ?? '—',
            'intake_cohort' => $row->intake_cohort ?? '—',
            'delivery_mode' => $row->delivery_mode ?? '—',
            'enrolled_count' => (string) $row->enrolled_count,
            'modules_count' => (string) $row->modules_count,
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'activeCourseShells' => $records->count(),
            'publishedModules' => (int) $records->sum('modules_count'),
            'enrolledStudents' => (int) $records->sum('enrolled_count'),
            'lmsStorageUsed' => $records->count().' shells',
        ]);

        return view('lms.course-shells', compact('shells', 'stats'))->with(
            'operationalCreate',
            $this->form('Add course shell', 'Persists to lms_course_shells.', 'lms.course-shells.store', [
                ['name' => 'shell_code', 'label' => 'Shell code', 'required' => true],
                ['name' => 'course_title', 'label' => 'Course title', 'required' => true],
                ['name' => 'faculty', 'label' => 'Faculty'],
                ['name' => 'instructor', 'label' => 'Instructor'],
                ['name' => 'intake_cohort', 'label' => 'Intake cohort'],
                ['name' => 'delivery_mode', 'label' => 'Delivery mode'],
                ['name' => 'enrolled_count', 'label' => 'Enrolled count', 'type' => 'number'],
                ['name' => 'modules_count', 'label' => 'Modules count', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeCourseShells(Request $request): RedirectResponse
    {
        return $this->store($request, LmsCourseShell::class, [
            'shell_code' => ['required', 'string', 'max:80', 'unique:lms_course_shells,shell_code'],
            'course_title' => ['required', 'string', 'max:190'],
            'faculty' => ['nullable', 'string', 'max:190'],
            'instructor' => ['nullable', 'string', 'max:190'],
            'intake_cohort' => ['nullable', 'string', 'max:80'],
            'delivery_mode' => ['nullable', 'string', 'max:80'],
            'enrolled_count' => ['nullable', 'integer', 'min:0'],
            'modules_count' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active', 'enrolled_count' => 0, 'modules_count' => 0], 'Course shell saved.');
    }

    public function lecturerAssignments(Request $request): View
    {
        $records = LmsLecturerAssignment::query()->latest()->get();
        $assignments = $records->map(fn (LmsLecturerAssignment $row): array => [
            'assignment_ref' => $row->assignment_ref,
            'instructor_name' => $row->instructor_name,
            'course_shell' => $row->course_shell ?? '—',
            'department' => $row->department ?? '—',
            'role' => $row->role ?? '—',
            'access_level' => $row->access_level ?? '—',
            'teaching_assistant' => $row->teaching_assistant ?? '—',
            'office_hours' => $row->office_hours ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'assignedInstructors' => $records->count(),
            'teachingAssistants' => $records->filter(fn (LmsLecturerAssignment $r): bool => filled($r->teaching_assistant))->count(),
            'guestLecturers' => $records->filter(fn (LmsLecturerAssignment $r): bool => str_contains(strtolower((string) $r->role), 'guest'))->count(),
            'unassignedShells' => $records->filter(fn (LmsLecturerAssignment $r): bool => blank($r->course_shell))->count(),
        ]);

        return view('lms.lecturer-assignments', compact('assignments', 'stats'))->with(
            'operationalCreate',
            $this->form('Add lecturer assignment', 'Persists to lms_lecturer_assignments.', 'lms.lecturer-assignments.store', [
                ['name' => 'assignment_ref', 'label' => 'Assignment ref', 'required' => true],
                ['name' => 'instructor_name', 'label' => 'Instructor', 'required' => true],
                ['name' => 'course_shell', 'label' => 'Course shell'],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'role', 'label' => 'Role'],
                ['name' => 'access_level', 'label' => 'Access level'],
                ['name' => 'teaching_assistant', 'label' => 'Teaching assistant'],
                ['name' => 'office_hours', 'label' => 'Office hours'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeLecturerAssignments(Request $request): RedirectResponse
    {
        return $this->store($request, LmsLecturerAssignment::class, [
            'assignment_ref' => ['required', 'string', 'max:80', 'unique:lms_lecturer_assignments,assignment_ref'],
            'instructor_name' => ['required', 'string', 'max:190'],
            'course_shell' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'role' => ['nullable', 'string', 'max:80'],
            'access_level' => ['nullable', 'string', 'max:80'],
            'teaching_assistant' => ['nullable', 'string', 'max:190'],
            'office_hours' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Lecturer assignment saved.');
    }

    public function liveLectures(Request $request): View
    {
        $records = LmsLiveLecture::query()->latest()->get();
        $sessions = $records->map(fn (LmsLiveLecture $row): array => [
            'session_title' => $row->session_title,
            'course_code' => $row->course_code ?? '—',
            'instructor' => $row->instructor ?? '—',
            'platform' => $row->platform ?? '—',
            'scheduled_time' => $row->scheduled_time ?? '—',
            'attendance_mode' => $row->attendance_mode ?? '—',
            'recording_status' => $row->recording_status ?? '—',
            'session_status' => $row->session_status,
        ])->all();
        $stats = new SoftStatsBag([
            'liveSessionsThisWeek' => $records->count(),
            'avgAttendanceRate' => $records->count() ? 'Tracked' : '—',
            'recordedHoursArchived' => $records->filter(fn (LmsLiveLecture $r): bool => str_contains(strtolower((string) $r->recording_status), 'record'))->count().' hrs',
            'activeVideoPlatform' => $records->pluck('platform')->filter()->first() ?? '—',
        ]);

        return view('lms.live-lectures', compact('sessions', 'stats'))->with(
            'operationalCreate',
            $this->form('Add live lecture', 'Persists to lms_live_lectures.', 'lms.live-lectures.store', [
                ['name' => 'session_title', 'label' => 'Session title', 'required' => true],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'instructor', 'label' => 'Instructor'],
                ['name' => 'platform', 'label' => 'Platform'],
                ['name' => 'scheduled_time', 'label' => 'Scheduled time'],
                ['name' => 'attendance_mode', 'label' => 'Attendance mode'],
                ['name' => 'recording_status', 'label' => 'Recording status'],
                ['name' => 'session_status', 'label' => 'Session status'],
            ]),
        );
    }

    public function storeLiveLectures(Request $request): RedirectResponse
    {
        return $this->store($request, LmsLiveLecture::class, [
            'session_title' => ['required', 'string', 'max:190'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'instructor' => ['nullable', 'string', 'max:190'],
            'platform' => ['nullable', 'string', 'max:80'],
            'scheduled_time' => ['nullable', 'string', 'max:80'],
            'attendance_mode' => ['nullable', 'string', 'max:80'],
            'recording_status' => ['nullable', 'string', 'max:40'],
            'session_status' => ['nullable', 'string', 'max:40'],
        ], ['session_status' => 'Scheduled'], 'Live lecture saved.');
    }

    public function eResources(Request $request): View
    {
        $records = LmsEResource::query()->latest()->get();
        $resources = $records->map(fn (LmsEResource $row): array => [
            'asset_title' => $row->asset_title,
            'course_shell' => $row->course_shell ?? '—',
            'resource_type' => $row->resource_type ?? '—',
            'file_size' => $row->file_size ?? '—',
            'uploaded_by' => $row->uploaded_by ?? '—',
            'upload_date' => $row->upload_date ?? '—',
            'downloads_views' => $row->downloads_views ?? '—',
            'access_rule' => $row->access_rule ?? '—',
        ])->all();
        $stats = new SoftStatsBag([
            'totalLearningAssets' => $records->count(),
            'scormModules' => $records->filter(fn (LmsEResource $r): bool => str_contains(strtolower((string) $r->resource_type), 'scorm'))->count(),
            'eBookLibraryLinks' => $records->filter(fn (LmsEResource $r): bool => str_contains(strtolower((string) $r->resource_type), 'book'))->count(),
            'studentDownloadsThisMonth' => $records->count(),
        ]);

        return view('lms.e-resources', compact('resources', 'stats'))->with(
            'operationalCreate',
            $this->form('Add e-resource', 'Persists to lms_e_resources.', 'lms.e-resources.store', [
                ['name' => 'asset_title', 'label' => 'Asset title', 'required' => true],
                ['name' => 'course_shell', 'label' => 'Course shell'],
                ['name' => 'resource_type', 'label' => 'Resource type'],
                ['name' => 'file_size', 'label' => 'File size'],
                ['name' => 'uploaded_by', 'label' => 'Uploaded by'],
                ['name' => 'upload_date', 'label' => 'Upload date'],
                ['name' => 'downloads_views', 'label' => 'Downloads / views'],
                ['name' => 'access_rule', 'label' => 'Access rule'],
            ]),
        );
    }

    public function storeEResources(Request $request): RedirectResponse
    {
        return $this->store($request, LmsEResource::class, [
            'asset_title' => ['required', 'string', 'max:190'],
            'course_shell' => ['nullable', 'string', 'max:190'],
            'resource_type' => ['nullable', 'string', 'max:80'],
            'file_size' => ['nullable', 'string', 'max:40'],
            'uploaded_by' => ['nullable', 'string', 'max:120'],
            'upload_date' => ['nullable', 'string', 'max:40'],
            'downloads_views' => ['nullable', 'string', 'max:80'],
            'access_rule' => ['nullable', 'string', 'max:120'],
        ], [], 'E-resource saved.');
    }

    public function assignments(Request $request): View
    {
        $records = LmsAssignment::query()->latest()->get();
        $assignments = $records->map(fn (LmsAssignment $row): array => [
            'assignment_title' => $row->assignment_title,
            'course_code' => $row->course_code ?? '—',
            'weight' => $row->weight ?? '—',
            'submission_deadline' => $row->submission_deadline ?? '—',
            'submissions_count' => (string) $row->submissions_count,
            'turnitin_status' => $row->turnitin_status ?? '—',
            'grading_status' => $row->grading_status,
        ])->all();
        $stats = new SoftStatsBag([
            'activeAssignments' => $records->count(),
            'submissionsGraded' => $records->filter(fn (LmsAssignment $r): bool => str_contains(strtolower($r->grading_status), 'grad'))->count(),
            'turnitinIntegrated' => $records->filter(fn (LmsAssignment $r): bool => filled($r->turnitin_status))->isNotEmpty() ? 'Enabled' : '—',
            'averageCatScore' => '—',
        ]);

        return view('lms.assignments', compact('assignments', 'stats'))->with(
            'operationalCreate',
            $this->form('Add assignment', 'Persists to lms_assignments.', 'lms.assignments.store', [
                ['name' => 'assignment_title', 'label' => 'Title', 'required' => true],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'weight', 'label' => 'Weight'],
                ['name' => 'submission_deadline', 'label' => 'Deadline'],
                ['name' => 'submissions_count', 'label' => 'Submissions', 'type' => 'number'],
                ['name' => 'turnitin_status', 'label' => 'Turnitin status'],
                ['name' => 'grading_status', 'label' => 'Grading status'],
            ]),
        );
    }

    public function storeAssignments(Request $request): RedirectResponse
    {
        return $this->store($request, LmsAssignment::class, [
            'assignment_title' => ['required', 'string', 'max:190'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'string', 'max:40'],
            'submission_deadline' => ['nullable', 'string', 'max:40'],
            'submissions_count' => ['nullable', 'integer', 'min:0'],
            'turnitin_status' => ['nullable', 'string', 'max:40'],
            'grading_status' => ['nullable', 'string', 'max:40'],
        ], ['grading_status' => 'Open', 'submissions_count' => 0], 'Assignment saved.');
    }

    public function studentAnalytics(Request $request): View
    {
        $records = LmsStudentAnalytic::query()->latest()->get();
        $analytics = $records->map(fn (LmsStudentAnalytic $row): array => [
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'programme' => $row->programme ?? '—',
            'engagement_score' => $row->engagement_score ?? '—',
            'total_logins_trimester' => (string) $row->total_logins_trimester,
            'video_watch_rate' => $row->video_watch_rate ?? '—',
            'cat_completion_rate' => $row->cat_completion_rate ?? '—',
            'risk_status' => $row->risk_status,
        ])->all();
        $atRisk = $records->filter(fn (LmsStudentAnalytic $r): bool => str_contains(strtolower($r->risk_status), 'risk'))->count();
        $stats = new SoftStatsBag([
            'activeDailyLearners' => $records->count(),
            'avgWeeklyEngagement' => $records->count() ? 'Tracked' : '—',
            'atRiskStudentsFlagged' => $atRisk,
            'retentionInterventionRate' => $records->count() ? round(($atRisk / max(1, $records->count())) * 100, 1).'%' : '0%',
        ]);

        return view('lms.student-analytics', compact('analytics', 'stats'))->with(
            'operationalCreate',
            $this->form('Add student analytic', 'Persists to lms_student_analytics.', 'lms.student-analytics.store', [
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'programme', 'label' => 'Programme'],
                ['name' => 'engagement_score', 'label' => 'Engagement score'],
                ['name' => 'total_logins_trimester', 'label' => 'Logins', 'type' => 'number'],
                ['name' => 'video_watch_rate', 'label' => 'Video watch rate'],
                ['name' => 'cat_completion_rate', 'label' => 'CAT completion'],
                ['name' => 'risk_status', 'label' => 'Risk status'],
            ]),
        );
    }

    public function storeStudentAnalytics(Request $request): RedirectResponse
    {
        return $this->store($request, LmsStudentAnalytic::class, [
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'programme' => ['nullable', 'string', 'max:190'],
            'engagement_score' => ['nullable', 'string', 'max:40'],
            'total_logins_trimester' => ['nullable', 'integer', 'min:0'],
            'video_watch_rate' => ['nullable', 'string', 'max:40'],
            'cat_completion_rate' => ['nullable', 'string', 'max:40'],
            'risk_status' => ['nullable', 'string', 'max:40'],
        ], ['risk_status' => 'On Track', 'total_logins_trimester' => 0], 'Student analytic saved.');
    }

    public function discussionForums(Request $request): View
    {
        $records = LmsDiscussionThread::query()->latest()->get();
        $threads = $records->map(fn (LmsDiscussionThread $row): array => [
            'thread_title' => $row->thread_title,
            'course_code' => $row->course_code ?? '—',
            'author' => $row->author ?? '—',
            'replies_count' => (string) $row->replies_count,
            'last_reply_by' => $row->last_reply_by ?? '—',
            'last_activity' => $row->last_activity ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'activeForumThreads' => $records->count(),
            'totalPostsThisMonth' => (int) $records->sum('replies_count'),
            'instructorResponseTime' => '—',
            'peerStudyGroups' => $records->filter(fn (LmsDiscussionThread $r): bool => str_contains(strtolower($r->thread_title), 'group'))->count(),
        ]);

        return view('lms.discussion-forums', compact('threads', 'stats'))->with(
            'operationalCreate',
            $this->form('Add discussion thread', 'Persists to lms_discussion_threads.', 'lms.discussion-forums.store', [
                ['name' => 'thread_title', 'label' => 'Thread title', 'required' => true],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'author', 'label' => 'Author'],
                ['name' => 'replies_count', 'label' => 'Replies', 'type' => 'number'],
                ['name' => 'last_reply_by', 'label' => 'Last reply by'],
                ['name' => 'last_activity', 'label' => 'Last activity'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeDiscussionForums(Request $request): RedirectResponse
    {
        return $this->store($request, LmsDiscussionThread::class, [
            'thread_title' => ['required', 'string', 'max:190'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'author' => ['nullable', 'string', 'max:190'],
            'replies_count' => ['nullable', 'integer', 'min:0'],
            'last_reply_by' => ['nullable', 'string', 'max:190'],
            'last_activity' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Open', 'replies_count' => 0], 'Discussion thread saved.');
    }

    public function onlineQuizzes(Request $request): View
    {
        $records = LmsOnlineQuiz::query()->latest()->get();
        $quizzes = $records->map(fn (LmsOnlineQuiz $row): array => [
            'quiz_title' => $row->quiz_title,
            'course_code' => $row->course_code ?? '—',
            'weight' => $row->weight ?? '—',
            'duration_minutes' => (string) $row->duration_minutes,
            'completed_attempts' => (string) $row->completed_attempts,
            'avg_score' => $row->avg_score ?? '—',
            'proctoring_mode' => $row->proctoring_mode ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'activeTimedQuizzes' => $records->count(),
            'randomizedQuestionBank' => (int) $records->sum('completed_attempts'),
            'aiProctoringActive' => $records->filter(fn (LmsOnlineQuiz $r): bool => str_contains(strtolower((string) $r->proctoring_mode), 'ai'))->isNotEmpty() ? 'Active' : '—',
            'instantGradeFeedback' => $records->filter(fn (LmsOnlineQuiz $r): bool => filled($r->avg_score))->count().' scored',
        ]);

        return view('lms.online-quizzes', compact('quizzes', 'stats'))->with(
            'operationalCreate',
            $this->form('Add online quiz', 'Persists to lms_online_quizzes.', 'lms.online-quizzes.store', [
                ['name' => 'quiz_title', 'label' => 'Quiz title', 'required' => true],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'weight', 'label' => 'Weight'],
                ['name' => 'duration_minutes', 'label' => 'Duration (minutes)', 'type' => 'number'],
                ['name' => 'completed_attempts', 'label' => 'Attempts', 'type' => 'number'],
                ['name' => 'avg_score', 'label' => 'Average score'],
                ['name' => 'proctoring_mode', 'label' => 'Proctoring mode'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeOnlineQuizzes(Request $request): RedirectResponse
    {
        return $this->store($request, LmsOnlineQuiz::class, [
            'quiz_title' => ['required', 'string', 'max:190'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'string', 'max:40'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'completed_attempts' => ['nullable', 'integer', 'min:0'],
            'avg_score' => ['nullable', 'string', 'max:40'],
            'proctoring_mode' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Draft', 'duration_minutes' => 0, 'completed_attempts' => 0], 'Online quiz saved.');
    }

    public function gradebookSync(Request $request): View
    {
        $records = LmsGradebookSync::query()->latest()->get();
        $syncLedgers = $records->map(fn (LmsGradebookSync $row): array => [
            'sync_ref' => $row->sync_ref,
            'course_code' => $row->course_code ?? '—',
            'cohort' => $row->cohort ?? '—',
            'enrolled_students' => (string) $row->enrolled_students,
            'total_cat_synced' => (string) $row->total_cat_synced,
            'cat1_weight' => $row->cat1_weight ?? '—',
            'cat2_weight' => $row->cat2_weight ?? '—',
            'erp_exam_engine_sync' => $row->erp_exam_engine_sync ?? '—',
            'sync_timestamp' => $row->sync_timestamp ?? '—',
            'status' => $row->status,
        ])->all();
        $ok = $records->filter(fn (LmsGradebookSync $r): bool => str_contains(strtolower($r->status), 'success') || str_contains(strtolower($r->status), 'ok'))->count();
        $stats = new SoftStatsBag([
            'totalSyncedGrades' => (int) $records->sum('total_cat_synced'),
            'catMarksWeight' => 'CAT sync',
            'erpSyncAuditStatus' => $ok ? 'Healthy' : 'Idle',
            'syncAccuracy' => $records->count() ? round(($ok / max(1, $records->count())) * 100, 1).'%' : '0%',
        ]);

        return view('lms.gradebook-sync', compact('syncLedgers', 'stats'))->with(
            'operationalCreate',
            $this->form('Add gradebook sync', 'Persists to lms_gradebook_syncs.', 'lms.gradebook-sync.store', [
                ['name' => 'sync_ref', 'label' => 'Sync reference', 'required' => true],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'cohort', 'label' => 'Cohort'],
                ['name' => 'enrolled_students', 'label' => 'Enrolled students', 'type' => 'number'],
                ['name' => 'total_cat_synced', 'label' => 'CAT synced', 'type' => 'number'],
                ['name' => 'cat1_weight', 'label' => 'CAT1 weight'],
                ['name' => 'cat2_weight', 'label' => 'CAT2 weight'],
                ['name' => 'erp_exam_engine_sync', 'label' => 'ERP exam sync'],
                ['name' => 'sync_timestamp', 'label' => 'Sync timestamp'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeGradebookSync(Request $request): RedirectResponse
    {
        return $this->store($request, LmsGradebookSync::class, [
            'sync_ref' => ['required', 'string', 'max:80', 'unique:lms_gradebook_syncs,sync_ref'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'cohort' => ['nullable', 'string', 'max:80'],
            'enrolled_students' => ['nullable', 'integer', 'min:0'],
            'total_cat_synced' => ['nullable', 'integer', 'min:0'],
            'cat1_weight' => ['nullable', 'string', 'max:40'],
            'cat2_weight' => ['nullable', 'string', 'max:40'],
            'erp_exam_engine_sync' => ['nullable', 'string', 'max:80'],
            'sync_timestamp' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending', 'enrolled_students' => 0, 'total_cat_synced' => 0], 'Gradebook sync saved.');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $defaults
     */
    private function store(Request $request, string $model, array $rules, array $defaults, string $message): RedirectResponse
    {
        $this->authorizePermission($request, 'lms.manage');
        $data = $request->validate($rules);
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                $data[$key] = $value;
            }
        }
        $model::query()->create($data);

        return back()->with('success', $message);
    }

    /**
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @return array{title: string, hint: string, action: string, fields: list<array{name: string, label: string, type?: string, required?: bool}>}
     */
    private function form(string $title, string $hint, string $route, array $fields): array
    {
        return [
            'title' => $title,
            'hint' => $hint,
            'action' => route($route),
            'fields' => $fields,
        ];
    }
}
