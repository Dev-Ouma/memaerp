<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OperationalRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LmsController extends Controller
{
    public function __construct(private readonly OperationalRecordService $records) {}

    public function courseShells(Request $request): View
    {
        return $this->records->screen($request, 'lms.course-shells', 'lms', 'course_shell', 'shells', [
            ['key' => 'totalShells', 'op' => 'count'],
            ['key' => 'published', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Published'],
            ['key' => 'draft', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Draft'],
            ['key' => 'archived', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Archived'],
        ], [
            ['name' => 'shell_code', 'label' => 'Shell code', 'required' => true],
            ['name' => 'name', 'label' => 'Course shell name', 'required' => true],
            ['name' => 'programme', 'label' => 'Programme'],
            ['name' => 'lecturer', 'label' => 'Lead lecturer'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function lecturerAssignments(Request $request): View
    {
        return $this->records->screen($request, 'lms.lecturer-assignments', 'lms', 'lecturer_assignment', 'assignments', [
            ['key' => 'assignments', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'completed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Completed'],
        ], [
            ['name' => 'lecturer', 'label' => 'Lecturer', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit', 'required' => true],
            ['name' => 'cohort', 'label' => 'Cohort'],
            ['name' => 'workload_hours', 'label' => 'Workload hours'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function liveLectures(Request $request): View
    {
        return $this->records->screen($request, 'lms.live-lectures', 'lms', 'live_lecture', 'sessions', [
            ['key' => 'scheduled', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Scheduled'],
            ['key' => 'live', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Live'],
            ['key' => 'completed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Completed'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'session_code', 'label' => 'Session code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'lecturer', 'label' => 'Lecturer'],
            ['name' => 'platform', 'label' => 'Platform'],
            ['name' => 'start_date', 'label' => 'Start', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function eResources(Request $request): View
    {
        return $this->records->screen($request, 'lms.e-resources', 'lms', 'e_resource', 'resources', [
            ['key' => 'resources', 'op' => 'count'],
            ['key' => 'published', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Published'],
            ['key' => 'draft', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Draft'],
            ['key' => 'restricted', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Restricted'],
        ], [
            ['name' => 'resource_code', 'label' => 'Resource code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'resource_type', 'label' => 'Type'],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function assignments(Request $request): View
    {
        return $this->records->screen($request, 'lms.assignments', 'lms', 'assignment', 'assignments', [
            ['key' => 'open', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'closed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Closed'],
            ['key' => 'grading', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Grading'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'assignment_code', 'label' => 'Assignment code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'due_date', 'label' => 'Due date', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function studentAnalytics(Request $request): View
    {
        return $this->records->screen($request, 'lms.student-analytics', 'lms', 'student_analytic', 'analytics', [
            ['key' => 'trackedStudents', 'op' => 'count'],
            ['key' => 'atRisk', 'op' => 'count_match', 'field' => 'status', 'needle' => 'At Risk'],
            ['key' => 'onTrack', 'op' => 'count_match', 'field' => 'status', 'needle' => 'On Track'],
            ['key' => 'inactive', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Inactive'],
        ], [
            ['name' => 'student_name', 'label' => 'Student', 'required' => true],
            ['name' => 'reg_no', 'label' => 'Registration number'],
            ['name' => 'engagement_score', 'label' => 'Engagement score'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function discussionForums(Request $request): View
    {
        return $this->records->screen($request, 'lms.discussion-forums', 'lms', 'discussion_forum', 'threads', [
            ['key' => 'threads', 'op' => 'count'],
            ['key' => 'open', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'locked', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Locked'],
            ['key' => 'moderated', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Moderated'],
        ], [
            ['name' => 'thread_code', 'label' => 'Thread code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'author', 'label' => 'Author'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function onlineQuizzes(Request $request): View
    {
        return $this->records->screen($request, 'lms.online-quizzes', 'lms', 'online_quiz', 'quizzes', [
            ['key' => 'quizzes', 'op' => 'count'],
            ['key' => 'published', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Published'],
            ['key' => 'draft', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Draft'],
            ['key' => 'closed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Closed'],
        ], [
            ['name' => 'quiz_code', 'label' => 'Quiz code', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'duration_count', 'label' => 'Questions'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function gradebookSync(Request $request): View
    {
        return $this->records->screen($request, 'lms.gradebook-sync', 'lms', 'gradebook_sync', 'syncLedgers', [
            ['key' => 'syncJobs', 'op' => 'count'],
            ['key' => 'successful', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Success'],
            ['key' => 'failed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Failed'],
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
        ], [
            ['name' => 'sync_ref', 'label' => 'Sync reference', 'required' => true],
            ['name' => 'course_unit', 'label' => 'Course unit'],
            ['name' => 'source_system', 'label' => 'Source system'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }
}
