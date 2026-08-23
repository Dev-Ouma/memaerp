<?php

declare(strict_types=1);

namespace Tests\Feature\Course;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\Student;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use LogicException;
use Tests\TestCase;

final class CourseCatalogueTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);
        Notification::fake();
        $this->admin = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();
        $this->token = $this->admin->createToken('course-tests')->plainTextToken;
    }

    public function test_creates_maps_prerequisites_approves_and_offers_a_course(): void
    {
        $department = Department::query()->where('institution_id', $this->institution->id)->where('code', 'CS')->firstOrFail();
        $required = Course::query()->where('institution_id', $this->institution->id)->where('code', 'CSC 102')->firstOrFail();
        $created = $this->authorized()->postJson('/api/v1/courses', [
            'department_id' => $department->id,
            'code' => 'CSC 310',
            'title' => 'Operating Systems',
            'credits' => 4,
            'lecture_hours' => 3,
            'lab_hours' => 2,
            'learning_outcomes' => 'Explain process scheduling and memory management.',
            'syllabus_outline' => 'Processes, threads, virtual memory, file systems.',
        ])->assertCreated()->assertJsonPath('data.status', 'DRAFT')->json('data');

        $this->authorized()->postJson("/api/v1/courses/{$created['id']}/prerequisites", [
            'required_course_id' => $required->id,
            'requirement_type' => 'PREREQUISITE',
        ])->assertCreated();

        $this->authorized()->postJson("/api/v1/courses/{$created['id']}/submit")
            ->assertOk()->assertJsonPath('data.status', 'UNDER_REVIEW')
            ->assertJsonCount(2, 'data.reviews');

        $this->authorized()->postJson("/api/v1/courses/{$created['id']}/approve", [
            'stage' => 'DEPARTMENT_BOARD',
            'reference' => 'DEPT/2026/014',
        ])->assertOk();
        $this->authorized()->postJson("/api/v1/courses/{$created['id']}/approve", [
            'stage' => 'SCHOOL_BOARD',
            'reference' => 'SCH/2026/014',
        ])->assertOk()->assertJsonPath('data.status', 'ACTIVE')->assertJsonPath('data.is_active', true);

        $term = Term::query()->where('institution_id', $this->institution->id)->where('is_current', true)->firstOrFail();
        $campus = Campus::query()->where('institution_id', $this->institution->id)->where('code', 'MAIN')->firstOrFail();
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $offering = $this->authorized()->postJson('/api/v1/courses/offerings', [
            'course_id' => $created['id'],
            'term_id' => $term->id,
            'campus_id' => $campus->id,
            'section_code' => 'B',
            'max_capacity' => 50,
            'delivery_mode' => 'IN_PERSON',
            'workload_credits' => 4,
        ])->assertCreated()->assertJsonPath('data.section_code', 'B')->json('data');

        $this->authorized()->postJson("/api/v1/courses/offerings/{$offering['id']}/assign-lecturer", [
            'lecturer_id' => $lecturer->id,
            'role' => 'PRIMARY',
            'workload_credits' => 4,
        ])->assertOk()->assertJsonPath('data.lecturer_id', $lecturer->id);

        Notification::assertSentTo($lecturer, \App\Modules\Course\Notifications\LecturerAssignedNotification::class);

        $this->authorized()->getJson('/api/v1/courses/offerings/active')
            ->assertOk()->assertJsonFragment(['section_code' => 'B']);
    }

    public function test_rejects_a_cyclic_catalogue_prerequisite(): void
    {
        $first = Course::query()->where('code', 'CSC 101')->firstOrFail();
        $second = Course::query()->where('code', 'CSC 102')->firstOrFail();

        $this->authorized()->postJson("/api/v1/courses/{$first->id}/prerequisites", [
            'required_course_id' => $second->id,
            'requirement_type' => 'PREREQUISITE',
        ])->assertStatus(400)->assertJsonPath('error.code', 'ERR-CUR-CYCLE');
    }

    public function test_rejects_a_duplicate_course_code(): void
    {
        $department = Department::query()->where('code', 'CS')->firstOrFail();
        $this->authorized()->postJson('/api/v1/courses', [
            'department_id' => $department->id,
            'code' => 'CSC 101',
            'title' => 'Duplicate',
            'credits' => 3,
        ])->assertStatus(409)->assertJsonPath('error.code', 'ERR-CRS-001');
    }

    public function test_increments_enrolled_count_when_a_student_enrolls(): void
    {
        $offering = CourseOffering::query()->where('institution_id', $this->institution->id)->firstOrFail();
        $before = $offering->enrolled_count;
        $this->enroll($offering);
        self::assertSame($before + 1, $offering->fresh()?->enrolled_count);
    }

    public function test_waitlists_when_the_section_is_full(): void
    {
        $offering = CourseOffering::query()->where('institution_id', $this->institution->id)->firstOrFail();
        $offering->forceFill(['max_capacity' => 1, 'enrolled_count' => 1, 'is_open_for_enrollment' => true, 'status' => 'OFFERED'])->save();
        $student = $this->student('MEMA/WAIT/2026/001');

        $this->authorized()->postJson("/api/v1/courses/offerings/{$offering->id}/waitlist", [
            'student_id' => $student->id,
        ])->assertCreated()->assertJsonPath('data.status', 'WAITING');
        self::assertSame(1, $offering->fresh()?->waitlist_count);
    }

    public function test_exports_catalogue_and_section_reports(): void
    {
        $csv = $this->authorized()->get('/api/v1/courses/report?format=csv')->assertOk()->assertDownload();
        self::assertStringContainsString('code,title,credits', $csv->streamedContent());

        $pdf = $this->authorized()->get('/api/v1/courses/report?format=pdf')->assertOk()->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $pdf->getContent());

        $sections = $this->authorized()->get('/api/v1/courses/offerings/report?format=csv')->assertOk()->assertDownload();
        self::assertStringContainsString('course_code,title,section', $sections->streamedContent());

        $course = Course::query()->where('code', 'CSC 101')->firstOrFail();
        $syllabus = $this->authorized()->get("/api/v1/courses/{$course->id}/syllabus")->assertOk()->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $syllabus->getContent());
    }

    public function test_denies_catalogue_management_to_an_unprivileged_user(): void
    {
        $token = $this->userWithNoRoles()->createToken('denied')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/courses', [])->assertForbidden();
    }

    public function test_hod_can_draft_and_department_board_approve_within_scope(): void
    {
        $department = Department::query()->where('code', 'CS')->firstOrFail();
        $hod = User::query()->where('email', 'hod.cs@mema.ac.ke')->firstOrFail();
        $token = $hod->createToken('hod-course')->plainTextToken;
        $created = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/v1/courses', [
            'department_id' => $department->id,
            'code' => 'CSC 311',
            'title' => 'Compilers',
            'credits' => 3,
        ])->assertCreated()->json('data');
        $this->withHeader('Authorization', 'Bearer '.$token)->postJson("/api/v1/courses/{$created['id']}/submit")->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$token)->postJson("/api/v1/courses/{$created['id']}/approve", [
            'stage' => 'DEPARTMENT_BOARD',
            'reference' => 'CS/BOARD/2026/1',
        ])->assertOk();
    }

    private function authorized(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }

    private function enroll(CourseOffering $offering): CourseEnrollment
    {
        $student = $this->student('MEMA/ENR/2026/001');
        $registration = TermRegistration::query()->create([
            'institution_id' => $this->institution->id,
            'student_id' => $student->id,
            'term_id' => $offering->term_id,
            'year_level' => 1,
            'semester' => 1,
            'financial_clearance_status' => true,
            'status' => 'REGISTERED',
            'registered_at' => now(),
        ]);

        return CourseEnrollment::query()->create([
            'institution_id' => $this->institution->id,
            'term_registration_id' => $registration->id,
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'status' => 'ENROLLED',
            'is_retake' => false,
            'enrolled_at' => now(),
        ]);
    }

    private function student(string $number): Student
    {
        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();
        $department = $programme->department;
        if (! $department instanceof Department) {
            throw new LogicException('The seeded programme requires a department.');
        }
        $year = AcademicYear::query()->where('institution_id', $this->institution->id)->current()->firstOrFail();
        $campus = Campus::query()->where('institution_id', $this->institution->id)->firstOrFail();
        $person = Person::query()->create([
            'institution_id' => $this->institution->id,
            'given_name' => 'Offering',
            'family_name' => 'Student',
            'primary_email' => strtolower($number).'@students.mema.ac.ke',
        ]);

        return Student::query()->create([
            'institution_id' => $this->institution->id,
            'person_id' => $person->id,
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'department_id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'admission_year_id' => $year->id,
            'student_number' => $number,
            'current_year_level' => 1,
            'current_semester' => 1,
            'academic_standing' => 'GOOD_STANDING',
            'status' => 'ACTIVE',
            'matriculated_on' => now()->toDateString(),
        ]);
    }
}
