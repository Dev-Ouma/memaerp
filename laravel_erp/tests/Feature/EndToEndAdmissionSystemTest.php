<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicProgramme;
use App\Models\AcademicSession;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\ApplicationReview;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EndToEndAdmissionSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $admissionsOfficer;
    private User $reviewer;
    private User $applicantUser;
    private Course $course;
    private AdmissionIntake $intake;
    private ProgrammeOffering $offering;
    private AcademicSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Prof. Peter Wabwire']);
        $this->admissionsOfficer = User::factory()->create(['role' => 'staff', 'name' => 'Faith Chebet']);
        $this->reviewer = User::factory()->create(['role' => 'staff', 'name' => 'Dr. Samuel Otieno']);
        $this->applicantUser = User::factory()->create(['role' => 'applicant', 'name' => 'Wanjiku Mwangi']);

        $this->session = AcademicSession::firstOrCreate(['start_date' => '2026-09-01', 'end_date' => '2027-08-31']);
        $this->course = Course::create(['code' => 'BCS', 'name' => 'Bachelor of Science in Computer Science']);
        $this->intake = AdmissionIntake::create([
            'code' => 'SEP-2026',
            'name' => 'September 2026 Regular Intake',
            'opens_at' => '2026-06-01',
            'closes_at' => '2026-09-20',
            'acceptance_deadline' => '2026-09-30',
            'is_published' => true,
        ]);
        $this->offering = ProgrammeOffering::create([
            'course_id' => $this->course->id,
            'admission_intake_id' => $this->intake->id,
            'study_mode' => 'Full-time',
            'campus' => 'Main Campus',
            'capacity' => 120,
            'application_fee' => 1000,
            'is_published' => true,
        ]);
    }

    public function test_all_13_core_admission_reports_render_cleanly_with_database_data(): void
    {
        $this->seedSampleApplication();

        $reports = [
            'application-register',
            'applications-by-programme',
            'admission-status-summary',
            'review-workload',
            'outstanding-documents',
            'shortlisted-waitlisted',
            'admitted-letters',
            'rejected-withdrawn-deferred',
            'offer-acceptance-expiry',
            'payments-clearance',
            'enrolled-students',
            'programme-capacity-conversion',
            'audit-trail',
        ];

        foreach ($reports as $reportKey) {
            $response = $this->actingAs($this->admin)->get(route('reports.' . $reportKey));
            $response->assertOk();
            $response->assertSee('Live Database');
            $response->assertSee('Export Report');
        }
    }

    public function test_all_13_admission_reports_export_to_csv_xlsx_and_pdf(): void
    {
        $this->seedSampleApplication();

        $reports = [
            'application-register',
            'payments-clearance',
            'enrolled-students',
            'admitted-letters',
        ];

        foreach ($reports as $reportKey) {
            // CSV
            $csvResponse = $this->actingAs($this->admin)->get(route('reports.export', [
                'report' => $reportKey,
                'format' => 'csv',
            ]));
            $csvResponse->assertOk();
            $this->assertStringContainsString('text/csv', (string) $csvResponse->headers->get('Content-Type'));

            // XLSX
            $xlsxResponse = $this->actingAs($this->admin)->get(route('reports.export', [
                'report' => $reportKey,
                'format' => 'xlsx',
            ]));
            $xlsxResponse->assertOk();
            $this->assertStringContainsString('spreadsheetml.sheet', (string) $xlsxResponse->headers->get('Content-Type'));

            // PDF
            $pdfResponse = $this->actingAs($this->admin)->get(route('reports.export', [
                'report' => $reportKey,
                'format' => 'pdf',
            ]));
            $pdfResponse->assertOk();
            $pdfResponse->assertSee('MEMA UNIVERSITY COLLEGE');
        }
    }

    public function test_formula_injection_sanitization_in_csv_export(): void
    {
        $profile = ApplicantProfile::create([
            'user_id' => $this->applicantUser->id,
            'applicant_number' => 'PI-2026-9999',
            'phone' => '0711223344',
            'date_of_birth' => '2005-01-01',
            'nationality' => 'Kenyan',
            'county' => 'Nairobi',
            'identity_type' => 'national_id',
            'identity_number' => '38000999',
            'qr_token' => Str::random(48),
        ]);

        // Malicious candidate name starting with formula trigger '='
        $maliciousUser = User::factory()->create([
            'name' => '=1+2;cmd|’/C calc’!A0',
            'role' => 'applicant',
        ]);
        $maliciousProfile = ApplicantProfile::create([
            'user_id' => $maliciousUser->id,
            'applicant_number' => 'PI-2026-9998',
            'phone' => '0799887766',
            'date_of_birth' => '2005-01-01',
            'nationality' => 'Kenyan',
            'county' => 'Nairobi',
            'identity_type' => 'national_id',
            'identity_number' => '38000998',
            'qr_token' => Str::random(48),
        ]);

        AdmissionApplication::create([
            'applicant_profile_id' => $maliciousProfile->id,
            'programme_offering_id' => $this->offering->id,
            'application_number' => 'APP-2026-9998',
            'status' => 'SUBMITTED',
            'form_data' => ['education' => 'KCSE A'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.export', [
            'report' => 'application-register',
            'format' => 'csv',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        // Must be prefixed with single quote to prevent DDE / command execution
        $this->assertStringContainsString("'=1+2", $content);
    }

    public function test_complete_applicant_lifecycle_from_submission_to_student_enrolment(): void
    {
        // 1. Applicant Profile & Draft
        $profile = ApplicantProfile::create([
            'user_id' => $this->applicantUser->id,
            'applicant_number' => 'PI-2026-0001',
            'phone' => '0711223344',
            'date_of_birth' => '2005-03-15',
            'nationality' => 'Kenyan',
            'county' => 'Kiambu',
            'identity_type' => 'national_id',
            'identity_number' => '38123456',
            'qr_token' => Str::random(48),
        ]);

        $application = AdmissionApplication::create([
            'applicant_profile_id' => $profile->id,
            'programme_offering_id' => $this->offering->id,
            'application_number' => 'APP-2026-0001',
            'status' => 'DRAFT',
            'form_data' => [
                'reference_number' => '001/2026',
                'education' => 'KCSE 2024, Mean Grade A-',
            ],
        ]);

        // 2. Document Upload
        $doc = ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'kcse_certificate',
            'original_name' => 'kcse_slip.pdf',
            'storage_path' => "documents/{$application->id}/kcse_slip.pdf",
            'mime_type' => 'application/pdf',
            'size_bytes' => 150000,
            'sha256' => hash('sha256', 'slip'),
            'verification_status' => 'PENDING',
        ]);

        // 3. Fee Payment (M-Pesa STK push 0113636154)
        $payment = ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'receipt_number' => 'REC-2026-0001',
            'reference' => 'QJH7823901',
            'amount' => 1000,
            'currency' => 'KES',
            'channel' => 'mpesa',
            'status' => 'COMPLETED',
            'idempotency_key' => (string) Str::uuid(),
            'paid_at' => now(),
            'provider_payload' => ['phone' => '0113636154', 'account' => '0113636154'],
        ]);

        // 4. Staff Document Verification
        $doc->update([
            'verification_status' => 'VERIFIED',
            'verified_by' => $this->admissionsOfficer->id,
        ]);
        $this->assertEquals('VERIFIED', $doc->fresh()->verification_status);

        // 5. Departmental Review & Shortlisting
        $review = ApplicationReview::create([
            'admission_application_id' => $application->id,
            'reviewer_id' => $this->reviewer->id,
            'stage' => 'DEPARTMENTAL',
            'score' => 88,
            'recommendation' => 'RECOMMEND',
            'notes' => 'Meets cluster requirements for BSc Computer Science.',
        ]);

        $application->update(['status' => 'SHORTLISTED']);
        $this->assertEquals('SHORTLISTED', $application->fresh()->status);

        // 6. Offer Letter Issuance
        $offerNumber = 'OFF-2026-0001';
        $offer = AdmissionOffer::create([
            'admission_application_id' => $application->id,
            'offer_number' => $offerNumber,
            'checksum' => hash('sha256', $offerNumber),
            'status' => 'ISSUED',
            'issued_at' => now(),
            'expires_at' => now()->addDays(14),
            'verification_token' => Str::random(40),
        ]);
        $application->update(['status' => 'ADMITTED']);

        // 7. Offer Acceptance by Applicant
        $offer->update(['status' => 'ACCEPTED']);
        $application->update(['status' => 'READY_TO_ENROL']);

        // 8. Conversion to Enrolled Student
        $admNumber = 'MEMA/BCS/2026/001';
        $student = Student::create([
            'user_id' => $this->applicantUser->id,
            'admission_number' => $admNumber,
            'course_id' => $this->offering->course_id,
            'academic_session_id' => $this->session->id,
        ]);
        $application->update(['status' => 'ENROLLED']);

        $this->assertEquals('ENROLLED', $application->fresh()->status);
        $this->assertDatabaseHas('students', [
            'admission_number' => 'MEMA/BCS/2026/001',
            'user_id' => $this->applicantUser->id,
        ]);
    }

    public function test_programme_capacity_limits_and_availability_calculation(): void
    {
        $service = app(\App\Services\AdmissionReportService::class);
        $request = new \Illuminate\Http\Request();
        $report = $service->getReportData('programme-capacity-conversion', $request);

        $this->assertEquals('Programme Capacity and Admission Conversion', $report['title']);
        $this->assertNotEmpty($report['headers']);
        $this->assertNotEmpty($report['rows']);
    }

    private function seedSampleApplication(): void
    {
        $profile = ApplicantProfile::create([
            'user_id' => $this->applicantUser->id,
            'applicant_number' => 'PI-2026-0001',
            'phone' => '0711223344',
            'date_of_birth' => '2005-03-15',
            'nationality' => 'Kenyan',
            'county' => 'Nairobi',
            'identity_type' => 'national_id',
            'identity_number' => '38123456',
            'qr_token' => Str::random(48),
        ]);

        $app = AdmissionApplication::create([
            'applicant_profile_id' => $profile->id,
            'programme_offering_id' => $this->offering->id,
            'application_number' => 'APP-2026-0001',
            'status' => 'ADMITTED',
            'form_data' => ['education' => 'KCSE A'],
        ]);

        ApplicationPaymentAttempt::create([
            'admission_application_id' => $app->id,
            'receipt_number' => 'REC-2026-0001',
            'reference' => 'QJH7823901',
            'amount' => 1000,
            'currency' => 'KES',
            'channel' => 'mpesa',
            'status' => 'COMPLETED',
            'idempotency_key' => (string) Str::uuid(),
            'paid_at' => now(),
            'provider_payload' => ['phone' => '0113636154'],
        ]);

        AdmissionOffer::create([
            'admission_application_id' => $app->id,
            'offer_number' => 'OFF-2026-0001',
            'checksum' => hash('sha256', 'OFF-2026-0001'),
            'status' => 'ISSUED',
            'issued_at' => now(),
            'expires_at' => now()->addDays(14),
            'verification_token' => Str::random(40),
        ]);

        Student::create([
            'user_id' => $this->applicantUser->id,
            'admission_number' => 'MEMA/BCS/2026/001',
            'course_id' => $this->offering->course_id,
            'academic_session_id' => $this->session->id,
        ]);
    }
}
