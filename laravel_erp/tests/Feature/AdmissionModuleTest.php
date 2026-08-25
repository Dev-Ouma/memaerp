<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Services\AdmissionWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class AdmissionModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_and_registration_create_a_draft_application(): void
    {
        $offering = $this->offering();

        $this->get(route('admissions.catalogue'))->assertOk()->assertSee('Computer Science');
        $response = $this->post(route('admissions.register', $offering), [
            'first_name' => 'Amina',
            'last_name' => 'Njeri',
            'email' => 'amina@example.test',
            'phone' => '0712345678',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('admissions.portal'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'amina@example.test', 'role' => 'applicant']);
        $this->assertDatabaseHas('admission_applications', ['programme_offering_id' => $offering->id, 'status' => 'DRAFT']);
    }

    public function test_submission_is_payment_gated_and_creates_one_immutable_snapshot(): void
    {
        [$user, $application] = $this->application();
        $application->update(['completion_percent' => 100, 'declarations_accepted' => true]);
        ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'certificate',
            'original_name' => 'kcse.pdf',
            'storage_path' => 'admissions/test/kcse.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 128,
            'sha256' => hash('sha256', 'certificate'),
        ]);

        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        app(AdmissionWorkflow::class)->submit($application);
    }

    public function test_paid_application_can_reach_a_verifiable_offer_and_be_accepted(): void
    {
        [$applicant, $application] = $this->application();
        $application->update(['completion_percent' => 100, 'declarations_accepted' => true, 'form_data' => ['education' => 'KCSE 2025']]);
        ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'certificate',
            'original_name' => 'kcse.pdf',
            'storage_path' => 'admissions/test/kcse.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 128,
            'sha256' => hash('sha256', 'certificate'),
        ]);
        ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'reference' => 'PAY-TEST-001',
            'channel' => 'mpesa',
            'amount' => 1000,
            'currency' => 'KES',
            'status' => 'PAID',
            'idempotency_key' => (string) Str::uuid(),
            'paid_at' => now(),
            'receipt_number' => 'MEMA-RCPT-TEST-001',
        ]);

        $workflow = app(AdmissionWorkflow::class);
        $this->actingAs($applicant);
        $workflow->submit($application);
        $workflow->submit($application->refresh());
        $this->assertDatabaseCount('application_versions', 1);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        foreach (['UNDER_REVIEW', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED'] as $status) {
            $application = $workflow->move($application->refresh(), $status, 'uat_approved', 'Evidence checked.');
        }

        $offer = $application->offer()->firstOrFail();
        $this->get(route('admissions.verify', $offer->verification_token))->assertOk()->assertSee($offer->offer_number);
        $this->actingAs($applicant)->post(route('admissions.application.respond', $application), ['response' => 'ACCEPTED'])->assertRedirect();
        $this->assertDatabaseHas('admission_applications', ['id' => $application->id, 'status' => 'ACCEPTED']);
        $this->assertDatabaseHas('admission_offers', ['id' => $offer->id, 'status' => 'ACCEPTED']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admission.status_changed', 'subject_id' => $application->id]);
    }

    public function test_applicant_cannot_open_the_staff_admission_queue(): void
    {
        [$applicant] = $this->application();

        $this->actingAs($applicant)->get(route('admissions.index'))->assertForbidden();
    }

    public function test_admission_sidebar_destinations_are_available_to_staff(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->application();

        $this->actingAs($admin)->get(route('admissions.index'))->assertOk()->assertSee('Admissions command centre');
        $this->get(route('admissions.analytics'))->assertOk()->assertSee('Admissions performance');
        $this->get(route('admissions.reports'))->assertOk()->assertSee('Admissions report centre');
        $this->get(route('admissions.reports.applications'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /** @return array{User, AdmissionApplication} */
    private function application(): array
    {
        $user = User::factory()->create(['role' => 'applicant', 'is_active' => true]);
        $profile = ApplicantProfile::create(['user_id' => $user->id, 'applicant_number' => 'MC/APP/2026/'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT), 'phone' => '0700000000', 'qr_token' => Str::random(48)]);
        $application = AdmissionApplication::create(['applicant_profile_id' => $profile->id, 'programme_offering_id' => $this->offering()->id, 'application_number' => 'MC/APL/2026/'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT), 'form_data' => []]);

        return [$user, $application];
    }

    private function offering(): ProgrammeOffering
    {
        $course = Course::firstOrCreate(['code' => 'CS'], ['name' => 'Computer Science']);
        $intake = AdmissionIntake::firstOrCreate(['code' => 'SEP-2026'], ['name' => 'September 2026 Intake', 'opens_at' => '2026-06-01', 'closes_at' => '2026-09-20', 'acceptance_deadline' => '2026-09-30', 'is_published' => true]);

        return ProgrammeOffering::firstOrCreate(
            ['course_id' => $course->id, 'admission_intake_id' => $intake->id, 'campus' => 'Main Campus', 'study_mode' => 'Full-time'],
            ['capacity' => 60, 'application_fee' => 1000, 'is_published' => true],
        );
    }
}
