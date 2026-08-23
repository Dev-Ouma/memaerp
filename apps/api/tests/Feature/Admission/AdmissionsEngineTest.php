<?php

declare(strict_types=1);

namespace Tests\Feature\Admission;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Notifications\OfferIssuedNotification;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Intake;
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

final class AdmissionsEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $registrar;

    private string $token;

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
        $this->token = $this->registrar->createToken('admissions-tests')->plainTextToken;
    }

    public function test_registrar_has_admissions_review_permission(): void
    {
        self::assertNotSame([], $this->registrar->scopesFor('admission.application.review'));
        self::assertNotSame([], $this->registrar->scopesFor('admission.application.decide'));
    }

    public function test_registers_applicant_and_completes_application_to_offer_acceptance(): void
    {
        $catalogue = $this->getJson('/api/v1/admissions/catalogue')
            ->assertOk()
            ->assertJsonPath('data.application_fee.amount', 1500)
            ->json('data');

        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();
        $campus = Campus::query()->where('code', 'MAIN')->firstOrFail();
        $intake = Intake::query()->where('code', 'SEP-2026')->firstOrFail();

        $register = $this->postJson('/api/v1/admissions/register', [
            'given_name' => 'Grace',
            'family_name' => 'Mutiso',
            'email' => 'grace.mutiso.adm@example.com',
            'phone' => '+254712345678',
            'national_id' => '38123456',
            'password' => 'password123',
        ])->assertCreated()->json('data');

        $applicantToken = $register['token'];

        $application = $this->withHeader('Authorization', 'Bearer '.$applicantToken)->postJson('/api/v1/admissions/applications', [
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'intake_id' => $intake->id,
            'secondary_school_name' => 'Kenya High School',
            'mean_grade' => 'A-',
            'kcse_index_number' => '12345678001/2025',
        ])->assertCreated()->assertJsonPath('data.status', 'DRAFT')->json('data');

        $this->withHeader('Authorization', 'Bearer '.$applicantToken)->post(
            "/api/v1/admissions/applications/{$application['id']}/documents",
            [
                'document_type' => 'KCSE_CERTIFICATE',
                'file' => UploadedFile::fake()->create('kcse.pdf', 120, 'application/pdf'),
            ],
        )->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$applicantToken)->postJson(
            "/api/v1/admissions/applications/{$application['id']}/pay",
            ['channel' => 'MPESA', 'phone' => '0712345678'],
        )->assertOk()->assertJsonPath('data.application.is_fee_paid', true);

        $this->withHeader('Authorization', 'Bearer '.$applicantToken)->postJson(
            "/api/v1/admissions/applications/{$application['id']}/submit",
        )->assertOk()->assertJsonPath('data.status', 'SUBMITTED');

        $this->flushSession();
        Sanctum::actingAs($this->registrar, ['*']);

        $this->postJson("/api/v1/admissions/applications/{$application['id']}/verify", [
            'notes' => 'Documents verified and score meets cut-off.',
        ])->assertOk()->assertJsonPath('data.status', 'SHORTLISTED');

        $this->postJson("/api/v1/admissions/applications/{$application['id']}/decide", [
            'decision' => 'ADMIT',
            'reference' => 'ADM/COMM/2026/014',
            'notes' => 'Committee approved direct entry.',
        ])->assertOk()->assertJsonPath('data.status', 'ADMITTED');

        Notification::assertSentOnDemand(OfferIssuedNotification::class);

        $offerPdf = $this->withHeader('Authorization', 'Bearer '.$applicantToken)
            ->get("/api/v1/admissions/applications/{$application['id']}/offer-letter")
            ->assertOk()
            ->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $offerPdf->getContent());

        $this->withHeader('Authorization', 'Bearer '.$applicantToken)->postJson(
            "/api/v1/admissions/applications/{$application['id']}/accept-offer",
        )->assertOk()->assertJsonPath('data.status', 'ACCEPTED');
    }

    public function test_rejects_submission_without_fee_payment(): void
    {
        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();
        $campus = Campus::query()->where('code', 'MAIN')->firstOrFail();
        $intake = Intake::query()->where('code', 'SEP-2026')->firstOrFail();

        $register = $this->postJson('/api/v1/admissions/register', [
            'given_name' => 'No',
            'family_name' => 'Payment',
            'email' => 'no.payment@example.com',
            'phone' => '+254700000001',
            'national_id' => '39001122',
            'password' => 'password123',
        ])->assertCreated()->json('data');

        $application = $this->withHeader('Authorization', 'Bearer '.$register['token'])->postJson('/api/v1/admissions/applications', [
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'intake_id' => $intake->id,
            'secondary_school_name' => 'Test School',
            'mean_grade' => 'B+',
            'kcse_index_number' => '99887766002/2025',
        ])->assertCreated()->json('data');

        $this->withHeader('Authorization', 'Bearer '.$register['token'])->postJson(
            "/api/v1/admissions/applications/{$application['id']}/submit",
        )->assertStatus(402)->assertJsonPath('error.code', 'ERR-ADM-004');
    }

    public function test_imports_kuccps_placements(): void
    {
        $result = $this->authorized()->postJson('/api/v1/admissions/kuccps/import', [
            'rows' => [[
                'kuccps_index' => '99887766001/2025',
                'applicant_name' => 'Peter Kamau',
                'programme_code' => 'BSC-CS',
                'mean_grade' => 'B+',
                'aggregate_points' => 72.5,
            ]],
        ])->assertCreated()->json('data');

        self::assertSame(1, $result['imported']);
        self::assertSame(1, $result['applications_created']);
    }

    public function test_exports_admissions_reports(): void
    {
        $csv = $this->authorized()->get('/api/v1/admissions/report?format=csv')->assertOk()->assertDownload();
        self::assertStringContainsString('application_number,applicant', $csv->streamedContent());

        $pdf = $this->authorized()->get('/api/v1/admissions/report?format=pdf')->assertOk()->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $pdf->getContent());

        $fees = $this->authorized()->get('/api/v1/admissions/fee-report?format=csv')->assertOk()->assertDownload();
        self::assertStringContainsString('receipt_number,application_number', $fees->streamedContent());
    }

    public function test_dashboard_returns_funnel_counts(): void
    {
        $this->authorized()->getJson('/api/v1/admissions/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total', 'draft', 'submitted', 'under_review', 'shortlisted',
                    'admitted', 'accepted', 'rejected', 'fee_paid', 'prospects',
                ],
            ]);
    }

    public function test_denies_decision_to_admissions_officer(): void
    {
        $officer = $this->userWithRole('admissions-officer', Scope::institution());
        $application = Application::query()->where('status', 'SHORTLISTED')->first();
        if (! $application instanceof Application) {
            $application = Application::query()->firstOrFail();
            $application->forceFill(['status' => 'SHORTLISTED'])->save();
        }
        $token = $officer->createToken('officer')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)->postJson(
            "/api/v1/admissions/applications/{$application->id}/decide",
            ['decision' => 'ADMIT', 'reference' => 'X'],
        )->assertForbidden();
    }

    private function authorized(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }
}
