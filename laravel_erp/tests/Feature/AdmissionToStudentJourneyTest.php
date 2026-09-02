<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admission\StudentConversion;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\Student;
use App\Models\User;
use App\Services\AdmissionWorkflow;
use App\Services\StudentConversionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Walks the whole public admission funnel through the HTTP layer — no model
 * short-cuts — and asserts the applicant ends up as a student in the database.
 */
final class AdmissionToStudentJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_apply_pay_submit_accept_and_enrol_as_a_student(): void
    {
        Storage::fake('local');
        $offering = $this->offering();

        // 1. Public catalogue and programme page are reachable without an account.
        $this->get(route('admissions.catalogue'))->assertOk()->assertSee('Computer Science');
        $this->get(route('admissions.apply', $offering))->assertOk();

        // 2. Self-registration creates the account, profile and draft application.
        $this->post(route('admissions.register', $offering), [
            'first_name' => 'Wanjiru',
            'last_name' => 'Kamau',
            'email' => 'wanjiru.kamau@example.test',
            'phone' => '0722000111',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'terms' => '1',
        ])->assertRedirect(route('admissions.portal'));

        $applicant = User::where('email', 'wanjiru.kamau@example.test')->firstOrFail();
        $this->assertSame('applicant', $applicant->role);
        $application = AdmissionApplication::where('applicant_profile_id', $applicant->applicantProfile->id)->firstOrFail();
        $this->assertSame('DRAFT', $application->status);

        // 3. The portal renders for the freshly-registered applicant.
        $this->actingAs($applicant)->get(route('admissions.portal'))->assertOk()->assertSee($application->application_number);

        // 4. Save the draft sections.
        $this->actingAs($applicant)->put(route('admissions.application.update', $application), [
            'date_of_birth' => '2006-04-11',
            'nationality' => 'Kenyan',
            'county' => 'Nakuru',
            'identity_type' => 'national_id',
            'identity_number' => '39004411',
            'gender' => 'F',
            'source_channel' => 'Website',
            'education' => 'KCSE 2025, mean grade B+.',
            'declarations_accepted' => '1',
            'lock_version' => $application->lock_version,
        ])->assertRedirect();
        $this->assertSame(80, $application->refresh()->completion_percent);

        // 5. Upload evidence — the file is actually persisted.
        $this->actingAs($applicant)->post(route('admissions.application.documents', $application), [
            'document_type' => 'certificate',
            'document' => UploadedFile::fake()->create('kcse.pdf', 64, 'application/pdf'),
        ])->assertRedirect();
        $document = $application->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertSame(100, $application->refresh()->completion_percent);

        // 6. Pay the mandatory fee, then submit.
        $this->actingAs($applicant)->post(route('admissions.application.payment', $application), ['channel' => 'mpesa'])->assertRedirect();
        $this->assertTrue($application->refresh()->isPaid());

        $this->actingAs($applicant)->post(route('admissions.application.submit', $application))->assertRedirect();
        $this->assertSame('SUBMITTED', $application->refresh()->status);
        $this->assertDatabaseCount('application_versions', 1);

        // 7. Staff walk the application to an offer through the HTTP transition endpoint.
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        foreach (['UNDER_REVIEW', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED'] as $status) {
            $this->actingAs($admin)->post(route('admissions.transition', $application), [
                'status' => $status,
                'reason' => 'evidence_checked',
                'note' => 'Reviewed against entry requirements.',
            ])->assertRedirect();
        }

        $offer = $application->refresh()->offer()->firstOrFail();
        $this->get(route('admissions.verify', $offer->verification_token))->assertOk()->assertSee($offer->offer_number);

        // 8. The applicant accepts; the staging transition is taken automatically.
        $this->actingAs($applicant)->post(route('admissions.application.respond', $application), ['response' => 'ACCEPTED'])->assertRedirect();
        $this->assertSame('READY_TO_ENROL', $application->refresh()->status);
        $this->actingAs($applicant)->get(route('admissions.portal'))->assertOk()->assertSee('Complete your enrolment');

        // 9. Enrolment materialises the student record.
        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application), [
            'enrolment_declaration' => '1',
        ])->assertRedirect();

        $this->assertSame('ENROLLED', $application->refresh()->status);

        $conversion = StudentConversion::where('admission_application_id', $application->id)->firstOrFail();
        $this->assertSame('COMPLETED', $conversion->status);
        $this->assertNotNull($conversion->converted_at);
        $this->assertNull($conversion->failure_code);

        $student = Student::findOrFail($conversion->student_id);
        // The applicant's own account is promoted — no second identity for one person.
        $this->assertSame($applicant->id, $student->user_id);
        $this->assertSame($offering->course_id, $student->course_id);
        $this->assertNotNull($student->academic_session_id);
        $this->assertSame($student->admission_number, $conversion->student_number);
        $this->assertSame('student', $applicant->refresh()->role);

        // 10. The portal confirms the outcome to the applicant.
        $this->actingAs($applicant)->get(route('admissions.portal'))->assertOk()
            ->assertSee('You are enrolled')
            ->assertSee($student->admission_number);
    }

    public function test_conversion_is_idempotent_and_never_creates_a_second_student(): void
    {
        [$applicant, $application] = $this->admittedApplication();
        $service = app(StudentConversionService::class);

        $first = $service->convert($application);
        $second = $service->convert($application->refresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame($first->student_id, $second->student_id);
        $this->assertDatabaseCount('student_conversions', 1);
        $this->assertSame(1, Student::where('user_id', $applicant->id)->count());
    }

    public function test_repeating_the_enrolment_request_does_not_duplicate_the_student(): void
    {
        [$applicant, $application] = $this->admittedApplication();

        $this->actingAs($applicant)->post(route('admissions.application.respond', $application), ['response' => 'ACCEPTED'])->assertRedirect();
        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application->refresh()), ['enrolment_declaration' => '1'])->assertRedirect();

        // A second submission of the same form is rejected: the application has moved on.
        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application->refresh()), ['enrolment_declaration' => '1'])->assertStatus(409);

        $this->assertDatabaseCount('student_conversions', 1);
        $this->assertSame(1, Student::where('user_id', $applicant->id)->count());
    }

    public function test_a_failed_conversion_records_why_and_leaves_no_student_behind(): void
    {
        [$applicant, $application] = $this->admittedApplication();

        // Squat on the registration number the conversion is about to allocate,
        // so the student INSERT hits the unique constraint on admission_number.
        $course = $application->offering->course;
        Student::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'course_id' => $course->id,
            'admission_number' => sprintf('%s/%03d/%s', strtoupper((string) $course->code), $course->next_student_serial, now()->format('Y')),
        ]);

        try {
            app(StudentConversionService::class)->convert($application->refresh());
            $this->fail('Conversion should not succeed when the registration number collides.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('admission_number', $exception->getMessage());
        }

        $conversion = StudentConversion::where('admission_application_id', $application->id)->firstOrFail();
        $this->assertSame('FAILED', $conversion->status);
        $this->assertSame('UniqueConstraintViolationException', $conversion->failure_code);
        $this->assertNotNull($conversion->failure_reason);
        $this->assertNull($conversion->student_id);
        $this->assertSame(0, Student::where('user_id', $applicant->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admission.student_conversion_failed']);
    }

    public function test_enrolment_is_refused_before_the_offer_is_accepted(): void
    {
        [$applicant, $application] = $this->admittedApplication();

        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application), [
            'enrolment_declaration' => '1',
        ])->assertStatus(409);

        $this->assertDatabaseCount('student_conversions', 0);
    }

    public function test_the_state_machine_still_refuses_an_out_of_order_enrolment(): void
    {
        [, $application] = $this->admittedApplication();

        $this->expectException(ValidationException::class);
        app(AdmissionWorkflow::class)->enrol($application);
    }

    public function test_staff_can_read_the_conversion_ledger(): void
    {
        [$applicant, $application] = $this->admittedApplication();
        $this->actingAs($applicant)->post(route('admissions.application.respond', $application), ['response' => 'ACCEPTED']);
        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application->refresh()), ['enrolment_declaration' => '1']);

        $student = Student::where('user_id', $applicant->id)->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admissions.conversions'))->assertOk()
            ->assertSee('Student conversion ledger')
            ->assertSee($student->admission_number)
            ->assertSee($application->application_number);
    }

    public function test_applicants_cannot_read_the_conversion_ledger(): void
    {
        [$applicant] = $this->admittedApplication();

        $this->actingAs($applicant)->get(route('admissions.conversions'))->assertForbidden();
    }

    public function test_an_admin_can_retry_a_failed_conversion_once_the_cause_is_cleared(): void
    {
        [$applicant, $application] = $this->admittedApplication();
        $course = $application->offering->course;
        $blocker = Student::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'course_id' => $course->id,
            'admission_number' => sprintf('%s/%03d/%s', strtoupper((string) $course->code), $course->next_student_serial, now()->format('Y')),
        ]);

        try {
            app(StudentConversionService::class)->convert($application->refresh());
        } catch (QueryException) {
            // Expected: the registration number is taken.
        }

        $conversion = StudentConversion::where('admission_application_id', $application->id)->firstOrFail();
        $this->assertSame('FAILED', $conversion->status);

        // Clear the collision, then retry from the ledger.
        $blocker->delete();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admissions.conversions.retry', $conversion))->assertRedirect();

        $conversion->refresh();
        $this->assertSame('COMPLETED', $conversion->status);
        $this->assertSame($admin->id, $conversion->converted_by);
        $this->assertSame(1, Student::where('user_id', $applicant->id)->count());
        $this->assertDatabaseCount('student_conversions', 1);
    }

    public function test_a_completed_conversion_cannot_be_retried(): void
    {
        [$applicant, $application] = $this->admittedApplication();
        $this->actingAs($applicant)->post(route('admissions.application.respond', $application), ['response' => 'ACCEPTED']);
        $this->actingAs($applicant)->post(route('admissions.application.enrol', $application->refresh()), ['enrolment_declaration' => '1']);

        $conversion = StudentConversion::where('admission_application_id', $application->id)->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admissions.conversions.retry', $conversion))->assertStatus(409);
    }

    /**
     * An applicant holding an issued offer, reached through the real workflow.
     *
     * @return array{User, AdmissionApplication}
     */
    private function admittedApplication(): array
    {
        Storage::fake('local');
        $offering = $this->offering();

        $this->post(route('admissions.register', $offering), [
            'first_name' => 'Brian',
            'last_name' => 'Otieno',
            'email' => 'brian.otieno@example.test',
            'phone' => '0733444555',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'terms' => '1',
        ]);
        $applicant = User::where('email', 'brian.otieno@example.test')->firstOrFail();
        $application = AdmissionApplication::where('applicant_profile_id', $applicant->applicantProfile->id)->firstOrFail();

        $this->actingAs($applicant)->put(route('admissions.application.update', $application), [
            'date_of_birth' => '2005-01-20',
            'nationality' => 'Kenyan',
            'county' => 'Kisumu',
            'identity_type' => 'national_id',
            'identity_number' => '38112233',
            'gender' => 'M',
            'source_channel' => 'School visit',
            'education' => 'KCSE 2024, mean grade B.',
            'declarations_accepted' => '1',
            'lock_version' => $application->lock_version,
        ]);
        $this->actingAs($applicant)->post(route('admissions.application.documents', $application), [
            'document_type' => 'certificate',
            'document' => UploadedFile::fake()->create('kcse.pdf', 64, 'application/pdf'),
        ]);
        $this->actingAs($applicant)->post(route('admissions.application.payment', $application), ['channel' => 'mpesa']);
        $this->actingAs($applicant)->post(route('admissions.application.submit', $application));

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $workflow = app(AdmissionWorkflow::class);
        $this->actingAs($admin);
        foreach (['UNDER_REVIEW', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED'] as $status) {
            $application = $workflow->move($application->refresh(), $status, 'evidence_checked', 'Reviewed.');
        }

        return [$applicant, $application->refresh()];
    }

    private function offering(): ProgrammeOffering
    {
        $course = Course::firstOrCreate(['code' => 'CS'], ['name' => 'Computer Science']);
        $intake = AdmissionIntake::firstOrCreate(['code' => 'SEP-2026'], [
            'name' => 'September 2026 Intake',
            'opens_at' => '2026-06-01',
            'closes_at' => '2026-09-20',
            'acceptance_deadline' => '2026-09-30',
            'is_published' => true,
        ]);

        return ProgrammeOffering::firstOrCreate(
            ['course_id' => $course->id, 'admission_intake_id' => $intake->id, 'campus' => 'Main Campus', 'study_mode' => 'Full-time'],
            ['capacity' => 60, 'application_fee' => 1000, 'is_published' => true],
        );
    }
}
