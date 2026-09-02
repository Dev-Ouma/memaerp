<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AdmissionOffer;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationVersion;
use App\Models\AuditLog;
use App\Modules\Admission\Services\AdmissionPipeline;
use App\Modules\Platform\Numbering\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AdmissionWorkflow
{
    public function __construct(
        private readonly NumberGenerator $numbers,
        private readonly StudentConversionService $conversions,
        private readonly AdmissionPipeline $pipeline,
    ) {}

    private const NEXT = [
        'DRAFT' => ['SUBMITTED', 'WITHDRAWN'],
        'SUBMITTED' => ['UNDER_REVIEW', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'WITHDRAWN'],
        'UNDER_REVIEW' => ['INFO_REQUESTED', 'RETURNED_FOR_CORRECTION', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'REJECTED', 'DEFERRED'],
        'INFO_REQUESTED' => ['SUBMITTED', 'UNDER_REVIEW', 'WITHDRAWN'],
        'RETURNED_FOR_CORRECTION' => ['SUBMITTED', 'WITHDRAWN'],
        'VERIFIED' => ['SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'WAITLISTED', 'REJECTED', 'DEFERRED'],
        'SHORTLISTED' => ['APPROVAL_PENDING', 'ADMITTED', 'WAITLISTED', 'REJECTED', 'DEFERRED'],
        'APPROVAL_PENDING' => ['ADMITTED_CONDITIONAL', 'ADMITTED', 'REJECTED', 'DEFERRED'],
        'ADMITTED_CONDITIONAL' => ['ADMITTED', 'REVOKED'],
        'ADMITTED' => ['ACCEPTED', 'DECLINED', 'REVOKED', 'DEFERRED'],
        'WAITLISTED' => ['SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'REJECTED', 'WITHDRAWN'],
        'ACCEPTED' => ['READY_TO_ENROL', 'ENROLLED', 'DECLINED', 'WITHDRAWN', 'DEFERRED'],
        'READY_TO_ENROL' => ['ENROLLED', 'WITHDRAWN'],
        'ENROLLED' => [],
        'REJECTED' => ['UNDER_REVIEW'],
        'DEFERRED' => ['UNDER_REVIEW', 'ADMITTED'],
        'DECLINED' => ['UNDER_REVIEW'],
        'WITHDRAWN' => [],
        'REVOKED' => ['UNDER_REVIEW'],
    ];

    public function submit(AdmissionApplication $application): AdmissionApplication
    {
        return DB::transaction(function () use ($application): AdmissionApplication {
            $application = AdmissionApplication::query()->lockForUpdate()->findOrFail($application->id);
            if (! in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true)) {
                return $application;
            }
            if (! $application->isPaid()) {
                throw ValidationException::withMessages(['payment' => 'A confirmed KES 1,000 payment is required before submission.']);
            }
            if (! $application->declarations_accepted || $application->completion_percent < 100) {
                throw ValidationException::withMessages(['application' => 'Complete all required sections and declarations before submission.']);
            }
            if ($application->documents()->count() < 1) {
                throw ValidationException::withMessages(['documents' => 'Upload at least one supporting document before submission.']);
            }

            $nextVersionNumber = ((int) $application->current_version) + 1;
            $snapshot = [
                'application' => $application->form_data,
                'applicant' => $application->applicant->only(['applicant_number', 'date_of_birth', 'phone', 'nationality', 'county']),
                'offering' => $application->offering->load('course', 'intake')->toArray(),
                'declarations_accepted' => true,
                'payment_reference' => $application->payments()->where('status', 'PAID')->value('reference'),
            ];
            $version = ApplicationVersion::create([
                'admission_application_id' => $application->id,
                'version' => $nextVersionNumber,
                'snapshot' => $snapshot,
                'checksum' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR)),
                'created_at' => now(),
            ]);
            $from = $application->status;
            $application->forceFill([
                'status' => 'SUBMITTED',
                'payment_status' => $application->payments()->whereIn('status', ['PAID', 'WAIVED'])->latest()->value('status') ?? 'PAID',
                'submitted_version_id' => $version->id,
                'current_version' => $nextVersionNumber,
                'submission_receipt_number' => $application->submission_receipt_number ?: $this->numbers->submissionReceiptNumber(),
                'submitted_at' => $application->submitted_at ?: now(),
                'last_activity_at' => now(),
            ])->save();
            ApplicationStatusHistory::create([
                'admission_application_id' => $application->id,
                'from_status' => $from,
                'to_status' => 'SUBMITTED',
                'actor_user_id' => auth()->id(),
                'reason_code' => 'applicant_submission',
                'note' => 'Application submitted with confirmed payment.',
                'created_at' => now(),
            ]);
            AuditLog::record('admission.status_changed', $application, ['status' => $from], ['status' => 'SUBMITTED', 'reason' => 'applicant_submission']);

            // A submission that nobody is assigned to is invisible work, so the
            // triage desk is opened in the same transaction as the submission.
            $this->pipeline->openAssignment($application->refresh(), AdmissionPipeline::STAGE_TRIAGE);

            return $application->refresh();
        });
    }

    public function move(AdmissionApplication $application, string $to, string $reason, ?string $note = null): AdmissionApplication
    {
        $from = $application->status;
        if (! in_array($to, self::NEXT[$from] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "Transition from {$from} to {$to} is not allowed."]);
        }
        $application->update(['status' => $to, 'decision_at' => in_array($to, ['ADMITTED', 'REJECTED'], true) ? now() : $application->decision_at]);
        ApplicationStatusHistory::create(['admission_application_id' => $application->id, 'from_status' => $from, 'to_status' => $to, 'actor_user_id' => auth()->id(), 'reason_code' => $reason, 'note' => $note, 'created_at' => now()]);
        AuditLog::record('admission.status_changed', $application, ['status' => $from], ['status' => $to, 'reason' => $reason]);
        $this->recordPipelineArtefacts($application, $to, $reason, $note);
        if ($to === 'ADMITTED') {
            $this->issueOffer($application);
        }

        if ($to === 'ENROLLED') {
            // The seam into academic records: creates the student, or fails
            // loudly and rolls the reason into student_conversions.
            $this->conversions->convert($application->refresh(), auth()->id());
        }

        if ($to === 'ACCEPTED') {
            // Acceptance is the applicant's binding commitment and READY_TO_ENROL
            // carries no further applicant action, so the staging step is taken
            // in the same request rather than waiting on a clerk.
            return $this->move($application->refresh(), 'READY_TO_ENROL', 'offer_accepted', 'Offer accepted by the applicant.');
        }

        return $application->refresh();
    }

    /**
     * Complete enrolment for an applicant who has accepted their offer. This is
     * the transition that materialises the student record.
     */
    public function enrol(AdmissionApplication $application, string $reason = 'applicant_enrolment'): AdmissionApplication
    {
        return $this->move($application, 'ENROLLED', $reason, 'Enrolment completed and student record created.');
    }

    /**
     * A status change is the visible half of a decision; this writes the half
     * the review, approval and waitlist workspaces are built on.
     */
    private function recordPipelineArtefacts(AdmissionApplication $application, string $to, string $reason, ?string $note): void
    {
        $actor = auth()->id();

        [$decisionType, $outcome] = match ($to) {
            'SHORTLISTED' => ['SHORTLIST', 'ADMIT'],
            'WAITLISTED' => ['FINAL', 'WAITLIST'],
            'ADMITTED' => ['FINAL', 'ADMIT'],
            'ADMITTED_CONDITIONAL' => ['FINAL', 'ADMIT_CONDITIONAL'],
            'REJECTED' => ['FINAL', 'REJECT'],
            'DEFERRED' => ['FINAL', 'DEFER'],
            'REVOKED' => ['FINAL', 'REVOKE'],
            default => [null, null],
        };

        if ($decisionType !== null && $actor !== null) {
            $this->pipeline->recordDecision(
                $application, $decisionType, $outcome, $actor, $reason, $note, $decisionType === 'FINAL',
            );
        }

        if ($to === 'APPROVAL_PENDING') {
            $this->pipeline->openApprovalLadder($application);
        }

        // Reaching a verdict closes the desk that was holding the file.
        if (in_array($to, ['VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ADMITTED_CONDITIONAL', 'REJECTED', 'WAITLISTED'], true)) {
            $this->pipeline->completeAssignment($application, AdmissionPipeline::STAGE_TRIAGE);
        }

        // Departmental scoring only starts once the file has been verified.
        if ($to === 'VERIFIED') {
            $this->pipeline->openAssignment($application, AdmissionPipeline::STAGE_DEPARTMENT_REVIEW, null, $actor);
        }
    }

    private function issueOffer(AdmissionApplication $application): void
    {
        $number = 'MC/ADM/'.now()->format('Ym').'/'.strtoupper(substr(str_replace('-', '', $application->id), -8));
        $token = Str::random(48);
        AdmissionOffer::firstOrCreate(
            ['admission_application_id' => $application->id],
            ['offer_number' => $number, 'verification_token' => $token, 'expires_at' => $application->offering->intake->acceptance_deadline, 'checksum' => hash('sha256', $application->id.$number.$token)],
        );
    }
}
