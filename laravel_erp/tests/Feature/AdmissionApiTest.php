<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_and_registration_publish_a_stable_contract(): void
    {
        $offering = $this->offering();

        $this->getJson('/api/v1/public/programme-offerings?q=Computer')
            ->assertOk()->assertHeader('cache-control')
            ->assertJsonPath('data.0.id', (string) $offering->id)
            ->assertJsonPath('data.0.application_fee.amount', 1000);

        $response = $this->postJson('/api/v1/auth/register', $this->registration($offering));
        $response->assertCreated()->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.email_verification_required', true);
        $this->assertStringStartsWith('mema_at_', $response->json('data.access_token'));
        $this->assertDatabaseHas('consent_records', ['policy_type' => 'terms', 'accepted' => true]);
        $this->assertDatabaseHas('outbox_events', ['event_name' => 'applicant.created']);
    }

    public function test_submission_is_blocked_until_payment_is_authoritatively_confirmed_and_replays_safely(): void
    {
        $registration = $this->postJson('/api/v1/auth/register', $this->registration($this->offering()))->assertCreated();
        $token = $registration->json('data.access_token');
        $application = AdmissionApplication::query()->firstOrFail();
        $application->forceFill(['completion_percent' => 100, 'declarations_accepted' => true, 'form_data' => ['education' => ['award' => 'KCSE']]])->save();
        ApplicationDocument::create(['admission_application_id' => $application->id, 'document_type' => 'certificate', 'original_name' => 'kcse.pdf',
            'storage_path' => 'private/test.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 100, 'sha256' => hash('sha256', 'test')]);

        $headers = ['Authorization' => 'Bearer '.$token, 'Idempotency-Key' => (string) Str::uuid()];
        $this->postJson("/api/v1/applications/{$application->id}/submit", [], $headers)
            ->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_FAILED');

        ApplicationPaymentAttempt::create(['admission_application_id' => $application->id, 'reference' => 'PAY-API-001', 'channel' => 'MPESA_STK',
            'amount' => 1000, 'currency' => 'KES', 'status' => 'PAID', 'idempotency_key' => (string) Str::uuid(), 'paid_at' => now(), 'receipt_number' => 'MC/RCP/2026/000001']);
        $key = (string) Str::uuid();
        $submitHeaders = ['Authorization' => 'Bearer '.$token, 'Idempotency-Key' => $key];
        $first = $this->postJson("/api/v1/applications/{$application->id}/submit", [], $submitHeaders)->assertOk();
        $second = $this->postJson("/api/v1/applications/{$application->id}/submit", [], $submitHeaders)->assertOk()->assertHeader('Idempotent-Replay', 'true');

        $this->assertSame($first->json('data.submission_receipt_number'), $second->json('data.submission_receipt_number'));
        $this->assertDatabaseCount('application_versions', 1);
        $this->assertDatabaseHas('outbox_events', ['event_name' => 'application.submitted']);
    }

    /** @return array<string, mixed> */
    private function registration(ProgrammeOffering $offering): array
    {
        return ['email' => 'amina@example.test', 'password' => 'SecurePass2026', 'first_name' => 'Amina', 'last_name' => 'Njeri',
            'phone' => '0712345678', 'programme_offering_id' => $offering->id, 'terms_version' => '2026-01', 'privacy_version' => '2026-01',
            'cookie_version' => '2026-01', 'acknowledgement_accepted' => true, 'terms_accepted' => true, 'privacy_accepted' => true];
    }

    private function offering(): ProgrammeOffering
    {
        $course = Course::firstOrCreate(['code' => 'CS'], ['name' => 'Computer Science']);
        $intake = AdmissionIntake::firstOrCreate(['code' => 'SEP-2026'], ['name' => 'September 2026 Intake', 'opens_at' => '2026-06-01',
            'closes_at' => '2026-09-20', 'acceptance_deadline' => '2026-09-30', 'is_published' => true]);

        return ProgrammeOffering::firstOrCreate(['course_id' => $course->id, 'admission_intake_id' => $intake->id, 'campus' => 'Main Campus', 'study_mode' => 'Full-time'],
            ['capacity' => 60, 'application_fee' => 1000, 'is_published' => true]);
    }
}
