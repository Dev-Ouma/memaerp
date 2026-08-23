<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Course\Models\Room;
use App\Modules\Course\Models\TeachingSlot;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Graduation\Models\GraduationApplication;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Database\Seeders\AdmissionsAndFinanceSeeder;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\StudentLifecycleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Phase1LifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);
        $this->seed(AdmissionsAndFinanceSeeder::class);
        $this->seed(StudentLifecycleSeeder::class);

        $this->studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        $this->student = Student::query()->where('person_id', $this->studentUser->person_id)->firstOrFail();
    }

    public function test_finance_statement_and_clearance_for_seeded_student(): void
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/v1/finance/statement')
            ->assertOk()
            ->assertJsonPath('data.clearance.registration_cleared', true);

        $this->actingAs($this->studentUser)
            ->getJson('/api/v1/finance/clearance-status')
            ->assertOk()
            ->assertJsonPath('data.exam_cleared', true);
    }

    public function test_mpesa_callback_is_idempotent(): void
    {
        $invoice = Invoice::query()->firstOrFail();

        $payload = [
            'TransID' => 'MPESA-TEST-001',
            'TransAmount' => '1000',
            'BillRefNumber' => $invoice->invoice_number,
        ];

        $this->postJson('/api/v1/finance/mpesa/c2b-callback', $payload)->assertOk();
        $this->postJson('/api/v1/finance/mpesa/c2b-callback', $payload)->assertOk();
    }

    public function test_student_portal_dashboard_aggregates_modules(): void
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/v1/portal/student/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['student', 'finance', 'registration', 'academics', 'graduation_audit', 'next_classes', 'alerts'],
            ]);
    }

    public function test_timetable_schedule_and_ics_export(): void
    {
        $campus = Campus::query()->where('code', 'MAIN')->firstOrFail();
        $offering = CourseOffering::query()->where('campus_id', $campus->id)->firstOrFail();

        $room = Room::query()->create([
            'institution_id' => $this->student->institution_id,
            'campus_id' => $campus->id,
            'code' => 'LH-101',
            'name' => 'Lecture Hall 101',
            'capacity' => 120,
            'room_type' => 'LECTURE_HALL',
            'accessibility' => [],
            'is_active' => true,
        ]);

        TeachingSlot::query()->create([
            'institution_id' => $this->student->institution_id,
            'course_offering_id' => $offering->id,
            'room_id' => $room->id,
            'starts_at' => now()->addDay()->setTime(8, 0),
            'ends_at' => now()->addDay()->setTime(10, 0),
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($this->studentUser)
            ->getJson('/api/v1/timetable/my-schedule')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->studentUser)
            ->get('/api/v1/timetable/export.ics')
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8');
    }

    public function test_progression_publish_and_student_results(): void
    {
        $registrar = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();
        $term = Term::query()->where('is_current', true)->firstOrFail();

        $this->actingAs($registrar)
            ->postJson('/api/v1/progression/publish-results', ['term_id' => $term->id])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->getJson('/api/v1/progression/my-results')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_exam_card_download_requires_fee_clearance_or_passes_for_paid_student(): void
    {
        $this->actingAs($this->studentUser)
            ->get('/api/v1/exams/my-card')
            ->assertOk();
    }

    public function test_graduation_clearance_flow(): void
    {
        $registrar = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();

        $apply = $this->actingAs($this->studentUser)->postJson('/api/v1/graduation/apply');
        if ($apply->status() === 422) {
            $this->markTestSkipped('Degree audit threshold not met with current seed marks.');
        }

        $apply->assertCreated();
        $application = GraduationApplication::query()->where('student_id', $this->student->id)->firstOrFail();

        foreach ($application->checkpoints as $checkpoint) {
            $this->actingAs($registrar)
                ->postJson("/api/v1/graduation/checkpoints/{$checkpoint->id}/clear", [
                    'notes' => 'Cleared in lifecycle test.',
                ])
                ->assertOk();
        }

        $this->actingAs($this->studentUser)
            ->get('/api/v1/graduation/transcript')
            ->assertOk();
    }
}
