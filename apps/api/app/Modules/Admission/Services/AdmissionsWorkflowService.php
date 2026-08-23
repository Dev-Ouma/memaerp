<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Models\ApplicationPayment;
use App\Modules\Admission\Models\ApplicationReview;
use App\Modules\Admission\Notifications\OfferIssuedNotification;
use App\Modules\Iam\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AdmissionsWorkflowService
{
    /** @var array<string, int> */
    public const REVIEW_STAGES = [
        'DOCUMENT_SCREENING' => 1,
        'COMMITTEE' => 2,
    ];

    public function __construct(
        private readonly QualificationScoringService $scoring,
    ) {}

    public function recordPayment(Application $application, string $channel, string $phone, ?string $transactionRef = null): ApplicationPayment
    {
        if ($application->status !== 'DRAFT' && ! $application->is_fee_paid) {
            throw ValidationException::withMessages(['status' => ['Only a draft application can receive an application fee.']]);
        }

        return DB::transaction(function () use ($application, $channel, $phone, $transactionRef): ApplicationPayment {
            $reference = $transactionRef ?: 'MPESA-'.strtoupper(Str::random(10));
            $payment = ApplicationPayment::query()->create([
                'institution_id' => $application->institution_id,
                'application_id' => $application->id,
                'channel' => strtoupper($channel),
                'transaction_reference' => $reference,
                'payer_phone' => $phone,
                'amount' => $application->application_fee_amount,
                'currency' => $application->application_fee_currency,
                'status' => 'COMPLETED',
                'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'paid_at' => now(),
                'gateway_payload' => [
                    'simulated' => true,
                    'channel' => strtoupper($channel),
                    'result_code' => 0,
                ],
            ]);

            $application->auditReason('Application fee reconciled')->forceFill([
                'is_fee_paid' => true,
            ])->save();

            return $payment;
        });
    }

    public function submit(Application $application): Application
    {
        if ($application->status !== 'DRAFT') {
            throw ValidationException::withMessages(['status' => ['Only a draft application can be submitted.']]);
        }
        if (! $application->is_fee_paid) {
            abort(response()->json([
                'error' => [
                    'code' => 'ERR-ADM-004',
                    'message' => 'An application cannot be submitted until the application fee is fully reconciled.',
                ],
            ], 402));
        }
        if ($application->documents()->count() < 1) {
            throw ValidationException::withMessages(['documents' => ['Upload at least one supporting certificate before submitting.']]);
        }

        return DB::transaction(function () use ($application): Application {
            $score = $this->scoring->scoreFromMeanGrade((string) $application->mean_grade);
            $application->auditReason('Application submitted for admissions review')->forceFill([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'qualification_score' => $score,
            ])->save();

            foreach (self::REVIEW_STAGES as $stage => $sequence) {
                ApplicationReview::query()->firstOrCreate([
                    'application_id' => $application->id,
                    'stage' => $stage,
                ], [
                    'institution_id' => $application->institution_id,
                    'sequence' => $sequence,
                    'status' => 'PENDING',
                ]);
            }

            return $application->fresh($this->relations()) ?? $application;
        });
    }

    public function beginReview(Application $application): Application
    {
        if ($application->status !== 'SUBMITTED') {
            throw ValidationException::withMessages(['status' => ['Only a submitted application can enter document screening.']]);
        }

        $application->auditReason('Application moved to document screening')->forceFill([
            'status' => 'UNDER_REVIEW',
        ])->save();

        return $application->fresh($this->relations()) ?? $application;
    }

    public function verifyDocuments(Application $application, User $officer, ?string $notes = null): Application
    {
        if (! in_array($application->status, ['SUBMITTED', 'UNDER_REVIEW'], true)) {
            throw ValidationException::withMessages(['status' => ['Documents can only be verified while the application is under screening.']]);
        }

        return DB::transaction(function () use ($application, $officer, $notes): Application {
            if ($application->status === 'SUBMITTED') {
                $application->forceFill(['status' => 'UNDER_REVIEW'])->save();
            }

            $review = ApplicationReview::query()
                ->where('application_id', $application->id)
                ->where('stage', 'DOCUMENT_SCREENING')
                ->where('status', 'PENDING')
                ->lockForUpdate()
                ->firstOrFail();

            $review->auditReason('Document screening completed')->update([
                'status' => 'APPROVED',
                'reviewed_by' => $officer->id,
                'reference' => 'DOC-'.now()->format('YmdHis'),
                'comments' => $notes,
                'reviewed_at' => now(),
            ]);

            $application->documents()->update([
                'verification_status' => 'VERIFIED',
                'verified_by' => $officer->id,
                'verified_at' => now(),
                'verification_notes' => $notes,
            ]);

            $eligible = $this->scoring->meetsCutoff($application);
            $application->auditReason($eligible ? 'Applicant shortlisted after document verification' : 'Applicant rejected after document verification')
                ->forceFill([
                    'documents_verified_at' => now(),
                    'documents_verified_by' => $officer->id,
                    'status' => $eligible ? 'SHORTLISTED' : 'REJECTED',
                    'decision_notes' => $eligible ? ($notes ?? 'Meets programme cut-off.') : ($notes ?? 'Does not meet programme cut-off.'),
                ])->save();

            return $application->fresh($this->relations()) ?? $application;
        });
    }

    public function decide(Application $application, User $actor, string $decision, string $reference, ?string $notes = null): Application
    {
        if ($application->status !== 'SHORTLISTED') {
            throw ValidationException::withMessages(['status' => ['Committee decisions apply only to shortlisted applications.']]);
        }
        if (! in_array($decision, ['ADMIT', 'REJECT'], true)) {
            throw ValidationException::withMessages(['decision' => ['Decision must be ADMIT or REJECT.']]);
        }

        return DB::transaction(function () use ($application, $actor, $decision, $reference, $notes): Application {
            $review = ApplicationReview::query()
                ->where('application_id', $application->id)
                ->where('stage', 'COMMITTEE')
                ->where('status', 'PENDING')
                ->lockForUpdate()
                ->firstOrFail();

            $review->auditReason('Admissions committee decision recorded')->update([
                'status' => $decision === 'ADMIT' ? 'APPROVED' : 'REJECTED',
                'reviewed_by' => $actor->id,
                'reference' => $reference,
                'comments' => $notes,
                'reviewed_at' => now(),
            ]);

            if ($decision === 'REJECT') {
                $application->auditReason('Admission declined by committee')->forceFill([
                    'status' => 'REJECTED',
                    'decision_notes' => $notes,
                ])->save();

                return $application->fresh($this->relations()) ?? $application;
            }

            $token = Str::lower(Str::random(40));
            $offerRef = 'OFFER-'.now()->format('Y').'-'.strtoupper(Str::random(8));
            $hash = hash('sha256', $application->id.'|'.$offerRef.'|'.$token);
            $application->auditReason('Admission offer issued')->forceFill([
                'status' => 'ADMITTED',
                'offer_letter_ref' => $offerRef,
                'offer_qr_token' => $token,
                'offer_letter_hash' => $hash,
                'offer_issued_at' => now(),
                'offer_expires_at' => now()->addDays(30),
                'decision_notes' => $notes,
            ])->save();

            $fresh = $application->fresh($this->relations()) ?? $application;
            if ($fresh->person?->primary_email) {
                Notification::route('mail', $fresh->person->primary_email)
                    ->notify(new OfferIssuedNotification($fresh));
            }

            return $fresh;
        });
    }

    public function acceptOffer(Application $application): Application
    {
        if ($application->status !== 'ADMITTED') {
            throw ValidationException::withMessages(['status' => ['Only an admitted application can accept an offer.']]);
        }
        if ($application->offer_expires_at !== null && $application->offer_expires_at->isPast()) {
            $application->auditReason('Admission offer expired')->forceFill(['status' => 'EXPIRED'])->save();
            throw ValidationException::withMessages(['status' => ['This admission offer has expired.']]);
        }

        $application->auditReason('Applicant accepted admission offer')->forceFill([
            'status' => 'ACCEPTED',
            'offer_accepted_at' => now(),
        ])->save();

        return $application->fresh($this->relations()) ?? $application;
    }

    /** @return list<string> */
    private function relations(): array
    {
        return [
            'person', 'programme.department', 'campus', 'academicYear', 'intake', 'studyMode',
            'documents', 'payments', 'reviews.reviewer',
        ];
    }
}
