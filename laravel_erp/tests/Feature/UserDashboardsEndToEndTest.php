<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserDashboardsEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_renders_with_real_academic_and_attendance_data(): void
    {
        $course = Course::create(['code' => 'BIT', 'name' => 'Bachelor of Information Technology']);
        $session = AcademicSession::create(['start_date' => '2025-09-01', 'end_date' => '2026-08-31']);
        $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Alex Mwangi', 'first_name' => 'Alex', 'last_name' => 'Mwangi', 'is_active' => true]);
        $student = Student::create(['user_id' => $studentUser->id, 'course_id' => $course->id, 'academic_session_id' => $session->id, 'admission_number' => 'BIT/001/2026']);
        $teacherUser = User::factory()->create(['role' => 'staff', 'first_name' => 'Prof', 'last_name' => 'Oduor', 'is_active' => true]);
        $staff = Staff::create(['user_id' => $teacherUser->id, 'course_id' => $course->id]);
        $subject = Subject::create(['course_id' => $course->id, 'code' => 'CS-101', 'name' => 'Data Structures', 'staff_id' => $staff->id]);

        StudentResult::create(['student_id' => $student->id, 'subject_id' => $subject->id, 'test_score' => 32, 'exam_score' => 54]);

        $attendance = Attendance::create(['subject_id' => $subject->id, 'academic_session_id' => $session->id, 'date' => now()->toDateString()]);
        AttendanceRecord::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'present' => true]);

        $response = $this->actingAs($studentUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Student portal')
            ->assertSee('Welcome back, Alex.')
            ->assertSee('Your course progress')
            ->assertSee('BIT/001/2026')
            ->assertSee('Bachelor of Information Technology')
            ->assertSee('Data Structures')
            ->assertSee('CS-101')
            ->assertSee('86%') // 32 + 54
            ->assertSee('100%'); // attendance
    }

    public function test_teacher_dashboard_renders_with_teaching_allocation_and_roster(): void
    {
        $course = Course::create(['code' => 'BBA', 'name' => 'Bachelor of Business Admin']);
        $teacherUser = User::factory()->create(['role' => 'staff', 'first_name' => 'Dr', 'last_name' => 'Jane', 'is_active' => true]);
        $staff = Staff::create(['user_id' => $teacherUser->id, 'course_id' => $course->id]);
        $subject = Subject::create(['course_id' => $course->id, 'code' => 'BBA-201', 'name' => 'Financial Accounting', 'staff_id' => $staff->id]);

        $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Grace Koech', 'first_name' => 'Grace', 'last_name' => 'Koech', 'is_active' => true]);
        $student = Student::create(['user_id' => $studentUser->id, 'course_id' => $course->id, 'admission_number' => 'BBA/010/2026']);

        StudentResult::create(['student_id' => $student->id, 'subject_id' => $subject->id, 'test_score' => 28, 'exam_score' => 45]);

        $response = $this->actingAs($teacherUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Faculty and Lecturer Workspace')
            ->assertSee('Welcome back, Dr.')
            ->assertSee('Your teaching allocation')
            ->assertSee('Financial Accounting')
            ->assertSee('BBA-201')
            ->assertSee('Grace Koech')
            ->assertSee('73%');
    }

    public function test_parent_dashboard_renders_linked_learners_and_grades(): void
    {
        $course = Course::create(['code' => 'ENG', 'name' => 'Diploma in Engineering']);
        $session = AcademicSession::create(['start_date' => '2025-09-01', 'end_date' => '2026-08-31']);
        $teacherUser = User::factory()->create(['role' => 'staff', 'first_name' => 'Eng', 'last_name' => 'Barasa', 'is_active' => true]);
        $staff = Staff::create(['user_id' => $teacherUser->id, 'course_id' => $course->id]);
        $parentUser = User::factory()->create(['role' => 'parent', 'name' => 'Sarah Kimani', 'first_name' => 'Sarah', 'last_name' => 'Kimani', 'is_active' => true]);
        $childUser = User::factory()->create(['role' => 'student', 'name' => 'Brian Kimani', 'first_name' => 'Brian', 'last_name' => 'Kimani', 'is_active' => true]);
        $child = Student::create(['user_id' => $childUser->id, 'course_id' => $course->id, 'academic_session_id' => $session->id, 'admission_number' => 'ENG/005/2026']);

        $parentUser->children()->syncWithoutDetaching([
            $child->id => ['relationship' => 'Mother', 'is_primary' => true],
        ]);

        $subject = Subject::create(['course_id' => $course->id, 'code' => 'ENG-101', 'name' => 'Circuit Analysis', 'staff_id' => $staff->id]);
        StudentResult::create(['student_id' => $child->id, 'subject_id' => $subject->id, 'test_score' => 30, 'exam_score' => 50]);

        $attendance = Attendance::create(['subject_id' => $subject->id, 'academic_session_id' => $session->id, 'date' => now()->toDateString()]);
        AttendanceRecord::create(['attendance_id' => $attendance->id, 'student_id' => $child->id, 'present' => true]);

        $response = $this->actingAs($parentUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Parent and Guardian Portal')
            ->assertSee('Welcome back, Sarah.')
            ->assertSee('linked learner')
            ->assertSee('Brian Kimani')
            ->assertSee('ENG/005/2026')
            ->assertSee('Circuit Analysis')
            ->assertSee('80%')
            ->assertSee('Relationship: Mother');
    }

    public function test_applicant_dashboard_renders_stages_and_documents(): void
    {
        $course = Course::create(['code' => 'NURS', 'name' => 'Bachelor of Science in Nursing']);
        $intake = AdmissionIntake::create([
            'name' => 'September 2026',
            'code' => 'SEP2026',
            'opens_at' => now()->startOfYear(),
            'closes_at' => now()->addMonths(6),
            'acceptance_deadline' => now()->addMonths(7),
            'is_published' => true,
        ]);

        $offering = ProgrammeOffering::create([
            'course_id' => $course->id,
            'admission_intake_id' => $intake->id,
            'application_fee' => 2000.00,
            'capacity' => 50,
            'status' => 'OPEN',
        ]);

        $applicantUser = User::factory()->create(['role' => 'applicant', 'first_name' => 'Mercy', 'last_name' => 'Chebet', 'is_active' => true]);
        $profile = ApplicantProfile::create(['user_id' => $applicantUser->id, 'applicant_number' => 'APP-PROF-001', 'county' => 'Nairobi', 'qr_token' => 'qr-test-123']);

        $application = AdmissionApplication::create([
            'applicant_profile_id' => $profile->id,
            'programme_offering_id' => $offering->id,
            'application_number' => 'APP-2026-NURS-001',
            'status' => 'ADMITTED',
            'study_mode' => 'Full-Time',
        ]);

        ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'NATIONAL_ID',
            'original_name' => 'id_card.pdf',
            'storage_path' => 'documents/id_card.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10240,
            'sha256' => hash('sha256', 'id_card_test_content'),
            'verification_status' => 'VERIFIED',
        ]);

        ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'reference' => 'PAY-QA12345678',
            'idempotency_key' => 'idemp-12345678',
            'channel' => 'MPESA_EXPRESS',
            'amount' => 2000.00,
            'currency' => 'KES',
            'status' => 'PAID',
        ]);

        AdmissionOffer::create([
            'admission_application_id' => $application->id,
            'offer_number' => 'OFF-2026-NURS-001',
            'verification_token' => 'verif-tok-123',
            'checksum' => hash('sha256', 'offer-test-123'),
            'status' => 'ISSUED',
            'issued_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $response = $this->actingAs($applicantUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Applicant admission workspace')
            ->assertSee('Your journey to MEMA starts here.')
            ->assertSee('APP-2026-NURS-001')
            ->assertSee('Bachelor of Science in Nursing')
            ->assertSee('Stage 1')
            ->assertSee('Stage 5')
            ->assertSee('Official admission offer issued!')
            ->assertSee('OFF-2026-NURS-001')
            ->assertSee('PAID AND VERIFIED');
    }

    public function test_all_dashboards_handle_empty_states_gracefully(): void
    {
        $studentUser = User::factory()->create(['role' => 'student', 'first_name' => 'EmptyStudent', 'is_active' => true]);
        $this->actingAs($studentUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No results have been published for this semester yet.');

        $teacherUser = User::factory()->create(['role' => 'staff', 'first_name' => 'EmptyTeacher', 'is_active' => true]);
        $this->actingAs($teacherUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No teaching units assigned yet.');

        $parentUser = User::factory()->create(['role' => 'parent', 'first_name' => 'EmptyParent', 'is_active' => true]);
        $this->actingAs($parentUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No student linked to this guardian account');

        $applicantUser = User::factory()->create(['role' => 'applicant', 'first_name' => 'EmptyApplicant', 'is_active' => true]);
        $this->actingAs($applicantUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Your journey to MEMA starts here.');
    }
}
