<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admission\ApprovalStep;
use App\Models\Admission\PaymentReconciliation;
use App\Models\Admission\ReviewAssignment;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Modules\Admission\Services\AdmissionPipeline;
use App\Services\AdmissionWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The twelve staff workspaces read from the tables the admissions lifecycle
 * writes to. These tests hold both halves together: the screens render from
 * real queries, and every control on them changes real state.
 */
final class AdmissionWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private const WORKSPACES = [
        'admissions.workspace.work-queues',
        'admissions.workspace.document-verification',
        'admissions.workspace.reviews',
        'admissions.workspace.shortlists',
        'admissions.workspace.approvals',
        'admissions.workspace.offers',
        'admissions.workspace.waitlists',
        'admissions.workspace.admission-rolls',
        'admissions.workspace.payments',
        'admissions.workspace.payment-reconciliation',
        'admissions.workspace.audit',
        'admissions.workspace.dashboard',
    ];

    public function test_every_workspace_renders_for_staff_when_the_pipeline_is_empty(): void
    {
        $this->actingAs($this->staff());

        foreach (self::WORKSPACES as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_every_workspace_renders_once_applications_exist(): void
    {
        $this->submitted('Amina', 'Njeri');
        $this->actingAs($this->staff());

        foreach (self::WORKSPACES as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_an_applicant_cannot_reach_a_workspace(): void
    {
        [$user] = $this->applicant('Bilal', 'Omar');

        $this->actingAs($user)->get(route('admissions.workspace.work-queues'))->assertForbidden();
        $this->actingAs($user)->post(route('admissions.work-queues.auto-assign'))->assertForbidden();
    }

    public function test_submission_opens_a_triage_assignment_on_the_work_queue(): void
    {
        $this->staff();
        $application = $this->submitted('Cynthia', 'Wanjiru');

        $this->assertDatabaseHas('review_assignments', [
            'admission_application_id' => $application->id,
            'stage' => AdmissionPipeline::STAGE_TRIAGE,
            'status' => 'PENDING',
        ]);

        $this->actingAs($this->staff())
            ->get(route('admissions.workspace.work-queues'))
            ->assertOk()
            ->assertSee($application->application_number);
    }

    public function test_auto_assign_gives_unassigned_work_an_owner(): void
    {
        $staff = $this->staff();
        $application = $this->submitted('Dennis', 'Kiptoo');
        ReviewAssignment::query()->where('admission_application_id', $application->id)->delete();

        $this->actingAs($staff)
            ->post(route('admissions.work-queues.auto-assign'))
            ->assertRedirect();

        $this->assertDatabaseHas('review_assignments', [
            'admission_application_id' => $application->id,
            'stage' => AdmissionPipeline::STAGE_TRIAGE,
        ]);
        $this->assertNotNull(
            ReviewAssignment::query()->where('admission_application_id', $application->id)->value('assignee_id'),
        );
    }

    public function test_shortlist_advance_opens_the_ladder_and_two_signoffs_admit(): void
    {
        $officer = $this->admissionOfficer('admissions_officer');
        $registrar = $this->admissionOfficer('registrar');
        $application = $this->submitted('Esther', 'Muthoni');
        $this->actingAs($officer);
        $workflow = app(AdmissionWorkflow::class);
        $workflow->move($application, 'UNDER_REVIEW', 'triage');
        $workflow->move($application->refresh(), 'VERIFIED', 'documents_verified');
        $workflow->move($application->refresh(), 'SHORTLISTED', 'merit_shortlist');

        $this->post(route('admissions.shortlists.advance', $application))->assertRedirect();
        $this->assertSame('APPROVAL_PENDING', $application->refresh()->status);
        $this->assertSame(2, ApprovalStep::query()->where('admission_application_id', $application->id)->count());

        $this->actingAs($registrar)
            ->post(route('admissions.approvals.sign-off', $application), ['verdict' => 'APPROVED'])
            ->assertRedirect();
        $this->actingAs($registrar)
            ->post(route('admissions.approvals.sign-off', $application), ['verdict' => 'APPROVED'])
            ->assertRedirect();
        $this->assertSame(2, ApprovalStep::query()
            ->where('admission_application_id', $application->id)->where('status', 'APPROVED')->count());

        $this->actingAs($registrar)->post(route('admissions.approvals.authorize'))->assertRedirect();
        $this->assertSame('ADMITTED', $application->refresh()->status);
        $this->assertDatabaseHas('admission_offers', ['admission_application_id' => $application->id]);
        $this->assertDatabaseHas('decisions', ['admission_application_id' => $application->id, 'decision_type' => 'FINAL']);
    }

    public function test_waitlist_auto_promotion_never_exceeds_capacity(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff);
        $workflow = app(AdmissionWorkflow::class);
        $offering = $this->offering();
        $offering->forceFill(['capacity' => 1, 'confirmed_seats' => 1])->save();

        $application = $this->submitted('Faith', 'Chebet');
        $this->actingAs($staff);
        $workflow->move($application, 'UNDER_REVIEW', 'triage');
        $workflow->move($application->refresh(), 'VERIFIED', 'documents_verified');
        $workflow->move($application->refresh(), 'WAITLISTED', 'programme_full');

        $this->post(route('admissions.waitlists.auto-promote'))->assertRedirect();
        $this->assertSame('WAITLISTED', $application->refresh()->status, 'A full programme must not absorb a promotion.');

        $offering->forceFill(['confirmed_seats' => 0])->save();
        $this->post(route('admissions.waitlists.auto-promote'))->assertRedirect();
        $this->assertSame('SHORTLISTED', $application->refresh()->status);
    }

    public function test_reconciliation_files_an_exception_for_a_payment_with_no_ledger_entry(): void
    {
        [, $application] = $this->applicant('Grace', 'Atieno');
        ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'reference' => 'ORPHAN-1',
            'channel' => 'MPESA',
            'amount' => 1000,
            'currency' => 'KES',
            'status' => 'PAID',
            'idempotency_key' => Str::uuid()->toString(),
            'paid_at' => now(),
        ]);

        $this->actingAs($this->admissionOfficer('finance_officer'))
            ->post(route('admissions.reconciliation.run'))
            ->assertRedirect();

        $run = PaymentReconciliation::query()->firstOrFail();
        $this->assertSame('INTERNAL_LEDGER', $run->provider);
        $this->assertSame(1, $run->exception_count);
        $this->assertDatabaseHas('payment_reconciliation_exceptions', [
            'payment_reconciliation_id' => $run->id,
            'exception_type' => 'MISSING_LEDGER_ENTRY',
            'status' => 'OPEN',
        ]);
    }

    public function test_audit_integrity_check_confirms_the_append_only_triggers(): void
    {
        $this->seedRbac();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->grantRole($admin, 'system_administrator');

        $this->actingAs($admin)
            ->post(route('admissions.audit.verify'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', ['action' => 'admission.audit_integrity_checked']);
    }

    public function test_the_roll_export_returns_the_matriculation_register_as_csv(): void
    {
        $response = $this->actingAs($this->staff())->get(route('admissions.rolls.export'));

        $response->assertOk();
        $this->assertStringContainsString('Application No', $response->getContent());
    }

    public function test_a_workspace_filter_narrows_the_result_set(): void
    {
        $matching = $this->submitted('Hawa', 'Suleiman');
        $other = $this->submitted('Isaac', 'Mwangi');

        $this->actingAs($this->staff())
            ->get(route('admissions.workspace.work-queues', ['q' => 'Hawa']))
            ->assertOk()
            ->assertSee($matching->application_number)
            ->assertDontSee($other->application_number);
    }

    public function test_only_finance_may_authorise_a_fee_waiver(): void
    {
        [, $application] = $this->applicant('Joyce', 'Kamau');
        $payload = [
            'application_number' => $application->application_number,
            'reason_code' => 'FINANCIAL_HARDSHIP',
            'justification' => 'Verified hardship documentation on file.',
        ];

        $this->actingAs($this->staff())->post(route('admissions.payments.waiver'), $payload)->assertForbidden();

        $this->actingAs($this->admissionOfficer('finance_officer'))
            ->post(route('admissions.payments.waiver'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('payment_waivers', [
            'admission_application_id' => $application->id,
            'reason_code' => 'FINANCIAL_HARDSHIP',
            'status' => 'ACTIVE',
        ]);
        $this->assertSame('WAIVED', $application->refresh()->payment_status);
    }

    private function staff(): User
    {
        return $this->admissionOfficer('admissions_officer');
    }

    /** A paid, complete application carried through submission by the workflow. */
    private function submitted(string $first, string $last): AdmissionApplication
    {
        [$user, $application] = $this->applicant($first, $last);
        $application->update(['completion_percent' => 100, 'declarations_accepted' => true]);
        ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'certificate',
            'original_name' => 'kcse.pdf',
            'storage_path' => 'admissions/test/kcse.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 128,
            'sha256' => hash('sha256', $application->id),
        ]);
        ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'reference' => 'PAY-'.Str::upper(Str::random(8)),
            'channel' => 'MPESA',
            'amount' => 1000,
            'currency' => 'KES',
            'status' => 'PAID',
            'idempotency_key' => Str::uuid()->toString(),
            'paid_at' => now(),
        ]);

        return $this->actingAs($user)->app->make(AdmissionWorkflow::class)->submit($application->refresh());
    }

    /** @return array{0: User, 1: AdmissionApplication} */
    private function applicant(string $first, string $last): array
    {
        $user = User::factory()->create([
            'name' => "{$first} {$last}",
            'role' => 'applicant',
            'is_active' => true,
        ]);
        $profile = ApplicantProfile::create([
            'user_id' => $user->id,
            'applicant_number' => 'MC/APP/2026/'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'phone' => '0700000000',
            'qr_token' => Str::random(48),
        ]);
        $application = AdmissionApplication::create([
            'applicant_profile_id' => $profile->id,
            'programme_offering_id' => $this->offering()->id,
            'application_number' => 'MC/APL/2026/'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'form_data' => [],
        ]);

        return [$user, $application];
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
