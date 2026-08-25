<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AdmissionOffer;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationVersion;
use App\Models\AuditLog;
use App\Modules\Platform\Numbering\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AdmissionWorkflow
{
    public function __construct(private readonly NumberGenerator $numbers) {}

    private const NEXT = [
        'DRAFT' => ['SUBMITTED', 'WITHDRAWN'],
        'SUBMITTED' => ['UNDER_REVIEW', 'WITHDRAWN'],
        'UNDER_REVIEW' => ['INFO_REQUESTED', 'VERIFIED', 'REJECTED'],
        'INFO_REQUESTED' => ['UNDER_REVIEW', 'WITHDRAWN'],
        'VERIFIED' => ['SHORTLISTED', 'WAITLISTED', 'REJECTED'],
        'SHORTLISTED' => ['APPROVAL_PENDING', 'WAITLISTED', 'REJECTED'],
        'APPROVAL_PENDING' => ['ADMITTED_CONDITIONAL', 'ADMITTED', 'REJECTED'],
        'ADMITTED_CONDITIONAL' => ['ADMITTED', 'REVOKED'],
        'ADMITTED' => ['ACCEPTED', 'DECLINED', 'REVOKED'],
        'WAITLISTED' => ['APPROVAL_PENDING', 'REJECTED', 'WITHDRAWN'],
        'ACCEPTED' => ['READY_TO_ENROL', 'WITHDRAWN'],
        'READY_TO_ENROL' => ['ENROLLED'],
    ];

    public function submit(AdmissionApplication $application): AdmissionApplication
    {
        return DB::transaction(function () use ($application): AdmissionApplication {
            $application = AdmissionApplication::query()->lockForUpdate()->findOrFail($application->id);
            if ($application->status !== 'DRAFT') {
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

            $snapshot = [
                'application' => $application->form_data,
                'applicant' => $application->applicant->only(['applicant_number', 'date_of_birth', 'phone', 'nationality', 'county']),
                'offering' => $application->offering->load('course', 'intake')->toArray(),
                'declarations_accepted' => true,
                'payment_reference' => $application->payments()->where('status', 'PAID')->value('reference'),
            ];
            $version = ApplicationVersion::create([
                'admission_application_id' => $application->id,
                'version' => 1,
                'snapshot' => $snapshot,
                'checksum' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR)),
                'created_at' => now(),
            ]);
            $from = $application->status;
            $application->forceFill([
                'status' => 'SUBMITTED',
                'payment_status' => $application->payments()->whereIn('status', ['PAID', 'WAIVED'])->latest()->value('status') ?? 'PAID',
                'submitted_version_id' => $version->id,
                'current_version' => 1,
                'submission_receipt_number' => $this->numbers->submissionReceiptNumber(),
                'submitted_at' => now(),
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
        if ($to === 'ADMITTED') {
            $this->issueOffer($application);
        }

        return $application->refresh();
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
