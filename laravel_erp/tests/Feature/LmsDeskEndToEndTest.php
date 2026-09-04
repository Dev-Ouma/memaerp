<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LmsAssignment;
use App\Models\LmsCourseShell;
use App\Models\LmsDiscussionThread;
use App\Models\LmsEResource;
use App\Models\LmsGradebookSync;
use App\Models\LmsLecturerAssignment;
use App\Models\LmsLiveLecture;
use App\Models\LmsOnlineQuiz;
use App\Models\LmsStudentAnalytic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LmsDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_lms_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'lms_manager');
        $this->actingAs($officer);

        $this->post(route('lms.course-shells.store'), [
            'shell_code' => 'SHELL-CS101',
            'course_title' => 'Intro Computing',
            'faculty' => 'Computing',
            'instructor' => 'Dr Lecturer',
            'intake_cohort' => '2026',
            'delivery_mode' => 'Blended',
            'enrolled_count' => 40,
            'modules_count' => 8,
            'status' => 'Published',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.lecturer-assignments.store'), [
            'assignment_ref' => 'LA-01',
            'instructor_name' => 'Dr Lecturer',
            'course_shell' => 'SHELL-CS101',
            'department' => 'CS',
            'role' => 'Lead',
            'access_level' => 'Full',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.live-lectures.store'), [
            'session_title' => 'Week 1 Live',
            'course_code' => 'CS101',
            'instructor' => 'Dr Lecturer',
            'platform' => 'Zoom',
            'scheduled_time' => '2026-09-10 10:00',
            'session_status' => 'Scheduled',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.e-resources.store'), [
            'asset_title' => 'Week1 Slides',
            'course_shell' => 'SHELL-CS101',
            'resource_type' => 'PDF',
            'uploaded_by' => 'Dr Lecturer',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.assignments.store'), [
            'assignment_title' => 'CAT 1',
            'course_code' => 'CS101',
            'weight' => '30%',
            'grading_status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.student-analytics.store'), [
            'student_name' => 'E2E LMS Scholar',
            'reg_no' => 'BCS/LMS/2026',
            'engagement_score' => '82',
            'risk_status' => 'On Track',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.discussion-forums.store'), [
            'thread_title' => 'Welcome Thread',
            'course_code' => 'CS101',
            'author' => 'Dr Lecturer',
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.online-quizzes.store'), [
            'quiz_title' => 'Unit Quiz 1',
            'course_code' => 'CS101',
            'duration_minutes' => 30,
            'status' => 'Published',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('lms.gradebook-sync.store'), [
            'sync_ref' => 'SYNC-01',
            'course_code' => 'CS101',
            'cohort' => '2026',
            'total_cat_synced' => 40,
            'status' => 'Success',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lms_course_shells', ['shell_code' => 'SHELL-CS101']);
        $this->assertDatabaseHas('lms_gradebook_syncs', ['sync_ref' => 'SYNC-01', 'status' => 'Success']);

        $this->get(route('lms.course-shells'))->assertOk()->assertSee('SHELL-CS101')->assertSee('Intro Computing');
        $this->get(route('lms.assignments'))->assertOk()->assertSee('CAT 1');
        $this->get(route('lms.gradebook-sync'))->assertOk()->assertSee('SYNC-01');
    }

    public function test_lms_screens_render_empty(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'lms_manager');

        foreach ([
            'lms.course-shells',
            'lms.lecturer-assignments',
            'lms.live-lectures',
            'lms.e-resources',
            'lms.assignments',
            'lms.student-analytics',
            'lms.discussion-forums',
            'lms.online-quizzes',
            'lms.gradebook-sync',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk();
        }

        $this->assertSame(0, LmsCourseShell::query()->count());
        $this->assertSame(0, LmsLecturerAssignment::query()->count());
        $this->assertSame(0, LmsLiveLecture::query()->count());
        $this->assertSame(0, LmsEResource::query()->count());
        $this->assertSame(0, LmsAssignment::query()->count());
        $this->assertSame(0, LmsStudentAnalytic::query()->count());
        $this->assertSame(0, LmsDiscussionThread::query()->count());
        $this->assertSame(0, LmsOnlineQuiz::query()->count());
        $this->assertSame(0, LmsGradebookSync::query()->count());
    }

    public function test_staff_without_lms_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('lms.course-shells.store'), [
            'shell_code' => 'DENIED',
            'course_title' => 'Denied',
        ])->assertForbidden();

        $this->assertDatabaseMissing('lms_course_shells', ['shell_code' => 'DENIED']);
    }
}
