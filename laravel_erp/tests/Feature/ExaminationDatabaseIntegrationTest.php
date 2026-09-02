<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\ExamCenter;
use App\Models\ExamSession;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExaminationDatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_configuration_is_persisted_and_rendered_from_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('examination.exam-center.store'), [
            'center_code' => 'EXC-MAIN-01', 'name' => 'Main Hall', 'location' => 'Main Campus',
            'capacity' => 500, 'proctors_allocated' => 5, 'special_needs_access' => 'Ramp access', 'status' => 'OPERATIONAL',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('examination.exam-session.store'), [
            'session_code' => 'EXS-2027-T2', 'session_title' => 'Trimester II Examinations',
            'start_date' => '2027-04-05', 'end_date' => '2027-04-17', 'daily_slots' => 3,
            'moderation_deadline' => '2027-04-30', 'status' => 'SCHEDULED',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('examination.grades-config.store'), [
            'grade_letter' => 'A', 'min_marks' => 70, 'max_marks' => 100, 'gpa_points' => 4,
            'performance_descriptor' => 'Excellent',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get(route('examination.exam-center'))->assertOk()->assertSee('EXC-MAIN-01')->assertSee('500 seats');
        $this->get(route('examination.exam-session'))->assertOk()->assertSee('EXS-2027-T2');
        $this->get(route('examination.grades-config'))->assertOk()->assertSee('Excellent');
        $this->assertDatabaseHas('audit_logs', ['action' => 'examination.grade_scale_created']);
    }

    public function test_schedule_and_marks_capture_use_academic_master_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $lecturerUser = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $course = Course::create(['code' => 'CS', 'name' => 'Computer Science']);
        $staff = Staff::create(['user_id' => $lecturerUser->id, 'course_id' => $course->id]);
        $subject = Subject::create(['name' => 'Software Engineering', 'code' => 'CS301', 'staff_id' => $staff->id, 'course_id' => $course->id]);
        $academicSession = AcademicSession::create(['start_date' => '2026-09-01', 'end_date' => '2027-08-31']);
        $student = Student::create(['user_id' => $studentUser->id, 'course_id' => $course->id, 'academic_session_id' => $academicSession->id, 'admission_number' => 'CS/001/2026']);
        $center = ExamCenter::create(['center_code' => 'EXC-01', 'name' => 'Main Hall', 'location' => 'Main', 'capacity' => 200, 'proctors_allocated' => 2]);
        $session = ExamSession::create(['session_code' => 'EXS-01', 'session_title' => 'Main Exams', 'start_date' => '2027-04-01', 'end_date' => '2027-04-20', 'daily_slots' => 3, 'moderation_deadline' => '2027-05-01']);

        $this->actingAs($admin)->post(route('examination.exam-schedule.store'), [
            'exam_session_id' => $session->id, 'subject_id' => $subject->id, 'exam_center_id' => $center->id,
            'chief_invigilator_id' => $lecturerUser->id, 'exam_date' => '2027-04-10', 'slot' => '08:30 - 11:30',
            'candidate_count' => 1, 'status' => 'PUBLISHED',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($lecturerUser)->post(route('examination.marks-capture.store'), [
            'student_id' => $student->id, 'subject_id' => $subject->id, 'test_score' => 24, 'exam_score' => 58,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get(route('examination.exam-schedule'))->assertOk()->assertSee('CS301')->assertSee('Main Hall');
        $this->get(route('examination.marks-capture'))->assertOk()->assertSee('82.0%')->assertSee('Capture Completed');
        $this->assertDatabaseHas('student_results', ['student_id' => $student->id, 'subject_id' => $subject->id, 'test_score' => 24]);
    }

    public function test_unrelated_lecturer_cannot_capture_marks_for_another_subject(): void
    {
        $owner = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $outsider = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $course = Course::create(['code' => 'IT', 'name' => 'Information Technology']);
        $staff = Staff::create(['user_id' => $owner->id, 'course_id' => $course->id]);
        $subject = Subject::create(['name' => 'Networks', 'code' => 'IT201', 'staff_id' => $staff->id, 'course_id' => $course->id]);
        $student = Student::create(['user_id' => $studentUser->id, 'course_id' => $course->id, 'admission_number' => 'IT/001/2026']);

        $this->actingAs($outsider)->post(route('examination.marks-capture.store'), [
            'student_id' => $student->id, 'subject_id' => $subject->id, 'test_score' => 20, 'exam_score' => 50,
        ])->assertForbidden();
        $this->assertDatabaseCount('student_results', 0);
    }
}
