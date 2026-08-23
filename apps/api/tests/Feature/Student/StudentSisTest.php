<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Notifications\OfferIssuedNotification;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Intake;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Notifications\StudentNumberIssuedNotification;
use App\Platform\Support\Scope;
use Database\Seeders\AdmissionsAndFinanceSeeder;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class StudentSisTest extends TestCase
{
    use RefreshDatabase;

    private User $registrar;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seedReferenceData();
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);
        $this->seed(AdmissionsAndFinanceSeeder::class);
        Notification::fake();
        $this->registrar = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();
    }

    public function test_matriculates_accepted_application_and_issues_student_number(): void
    {
        $application = $this->acceptedApplication();

        $this->flushSession();
        Sanctum::actingAs($this->registrar, ['*']);

        $response = $this->postJson('/api/v1/students/matriculate', [
            'application_ids' => [$application->id],
            'pledge_signed' => true,
            'notes' => 'Original certificates verified at admissions desk.',
        ])->assertCreated();

        $studentNumber = $response->json('data.0.student_number');
        self::assertMatchesRegularExpression('/^BSC-CS\/\d{4}\/\d{5}$/', (string) $studentNumber);

        $student = Student::query()->where('application_id', $application->id)->firstOrFail();
        self::assertSame('ACTIVE', $student->status);
        self::assertSame('ACTIVE', $student->digital_id_status);
        self::assertNotNull($student->digital_id_token);

        $application->refresh();
        self::assertSame('MATRICULATED', $application->status);

        Notification::assertSentOnDemand(StudentNumberIssuedNotification::class);
    }

    public function test_matriculation_queue_lists_accepted_applications(): void
    {
        $application = $this->acceptedApplication();

        $this->actingAs($this->registrar)
            ->getJson('/api/v1/students/matriculation-queue')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $application->id);
    }

    public function test_registrar_can_update_student_status_with_audit_reason(): void
    {
        $student = $this->matriculatedStudent();

        $this->actingAs($this->registrar)
            ->patchJson("/api/v1/students/{$student->id}/status", [
                'status' => 'ON_LEAVE',
                'reason' => 'Medical deferment approved by registrar for one academic year.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ON_LEAVE');

        self::assertDatabaseHas('student.status_history', [
            'student_id' => $student->id,
            'from_status' => 'ACTIVE',
            'to_status' => 'ON_LEAVE',
        ]);
    }

    public function test_digital_id_pdf_download_and_public_verification(): void
    {
        $student = $this->matriculatedStudent();

        $pdf = $this->actingAs($this->registrar)
            ->get("/api/v1/students/{$student->id}/digital-id")
            ->assertOk()
            ->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $pdf->getContent());

        $this->getJson('/api/v1/students/verify-id/'.$student->digital_id_token)
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('data.student_number', $student->student_number);
    }

    public function test_student_master_directory_csv_report(): void
    {
        $this->matriculatedStudent();

        $this->actingAs($this->registrar)
            ->get('/api/v1/students/report?type=directory&format=csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_counts_active_students(): void
    {
        $this->matriculatedStudent();

        $this->actingAs($this->registrar)
            ->getJson('/api/v1/students/dashboard')
            ->assertOk()
            ->assertJsonPath('data.active', 1)
            ->assertJsonPath('data.total', 1);
    }

    public function test_admissions_officer_can_matriculate_but_not_change_status(): void
    {
        $application = $this->acceptedApplication();
        $officer = $this->userWithRole('admissions-officer', Scope::institution());

        $this->actingAs($officer)
            ->postJson('/api/v1/students/matriculate', [
                'application_ids' => [$application->id],
            ])
            ->assertCreated();

        $student = Student::query()->where('application_id', $application->id)->firstOrFail();

        $this->actingAs($officer)
            ->patchJson("/api/v1/students/{$student->id}/status", [
                'status' => 'SUSPENDED',
                'reason' => 'Attempting unauthorized status change from admissions officer.',
            ])
            ->assertForbidden();
    }

    private function acceptedApplication(): Application
    {
        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();
        $campus = Campus::query()->where('code', 'MAIN')->firstOrFail();
        $intake = Intake::query()->where('code', 'SEP-2026')->firstOrFail();

        $register = $this->postJson('/api/v1/admissions/register', [
            'given_name' => 'Brian',
            'family_name' => 'Ochieng',
            'email' => 'brian.ochieng.sis@example.com',
            'phone' => '+254712340001',
            'national_id' => '38111222',
            'password' => 'password123',
        ])->assertCreated()->json('data');

        $token = $register['token'];

        $applicationId = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/v1/admissions/applications', [
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'intake_id' => $intake->id,
            'secondary_school_name' => 'Alliance High School',
            'mean_grade' => 'A-',
            'kcse_index_number' => '12345678003/2025',
        ])->assertCreated()->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)->post(
            "/api/v1/admissions/applications/{$applicationId}/documents",
            ['document_type' => 'KCSE_CERTIFICATE', 'file' => UploadedFile::fake()->create('kcse.pdf', 120, 'application/pdf')],
        )->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$token)->postJson(
            "/api/v1/admissions/applications/{$applicationId}/pay",
            ['channel' => 'MPESA', 'phone' => '0712340001'],
        )->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)->postJson(
            "/api/v1/admissions/applications/{$applicationId}/submit",
        )->assertOk();

        $this->flushSession();
        Sanctum::actingAs($this->registrar, ['*']);

        $this->postJson("/api/v1/admissions/applications/{$applicationId}/verify", [
            'notes' => 'Documents verified.',
        ])->assertOk();

        $this->postJson("/api/v1/admissions/applications/{$applicationId}/decide", [
            'decision' => 'ADMIT',
            'reference' => 'ADM/COMM/2026/099',
        ])->assertOk();

        Notification::assertSentOnDemand(OfferIssuedNotification::class);

        $this->withHeader('Authorization', 'Bearer '.$token)->postJson(
            "/api/v1/admissions/applications/{$applicationId}/accept-offer",
        )->assertOk();

        return Application::query()->findOrFail($applicationId);
    }

    private function matriculatedStudent(): Student
    {
        $application = $this->acceptedApplication();

        $this->flushSession();
        Sanctum::actingAs($this->registrar, ['*']);

        $this->postJson('/api/v1/students/matriculate', [
            'application_ids' => [$application->id],
        ])->assertCreated();

        return Student::query()->where('application_id', $application->id)->firstOrFail();
    }
}
