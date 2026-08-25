<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\ApplicationPaymentAttempt;
use App\Models\ProgrammeOffering;
use App\Modules\Admission\Setups\SetupResolver;
use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Api\ApiResponse;
use App\Modules\Platform\Audit\AuditRecorder;
use App\Modules\Platform\Numbering\NumberGenerator;
use App\Modules\Platform\Outbox\OutboxPublisher;
use App\Services\AdmissionWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApplicantApplicationController
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->applicantProfile?->applications()->with(['offering.course', 'offering.intake'])->latest()->get() ?? collect();

        return ApiResponse::data($items->map(fn ($application) => $this->serialize($application))->all());
    }

    public function store(Request $request, NumberGenerator $numbers): JsonResponse
    {
        $data = $request->validate(['programme_offering_id' => ['required', 'integer', 'exists:programme_offerings,id']]);
        $profile = $request->user()->applicantProfile;
        if ($profile === null) {
            throw ApiException::conflict('PROFILE_REQUIRED', 'Complete your applicant profile first.');
        }
        $offering = ProgrammeOffering::query()->with('intake')->where('is_published', true)->findOrFail($data['programme_offering_id']);
        $application = AdmissionApplication::create(['applicant_profile_id' => $profile->id, 'programme_offering_id' => $offering->id,
            'application_number' => $numbers->applicationNumber(strtoupper(str_replace('-', '', $offering->intake->code))), 'form_data' => []]);

        return ApiResponse::created($this->serialize($application->load(['offering.course', 'offering.intake'])));
    }

    public function show(Request $request, AdmissionApplication $application): JsonResponse
    {
        $this->assertOwner($request, $application);

        return ApiResponse::data($this->serialize($application->load(['offering.course', 'offering.intake', 'documents', 'histories'])));
    }

    public function update(Request $request, AdmissionApplication $application, AuditRecorder $audit, OutboxPublisher $outbox): JsonResponse
    {
        $this->assertOwner($request, $application);
        if ($application->status !== 'DRAFT') {
            throw ApiException::conflict('APPLICATION_LOCKED', 'Only a draft application can be changed.');
        }
        $data = $request->validate(['lock_version' => ['required', 'integer'], 'section' => ['required', 'string', 'max:60'], 'data' => ['required', 'array'],
            'completion_percent' => ['required', 'integer', 'between:0,100'], 'declarations_accepted' => ['sometimes', 'boolean']]);
        if ((int) $data['lock_version'] !== (int) $application->lock_version) {
            throw ApiException::conflict('VERSION_CONFLICT', 'The application changed in another session.', 'Reload the latest draft before saving again.');
        }
        $before = $application->form_data;
        $form = $before;
        $form[$data['section']] = $data['data'];
        $application->forceFill(['form_data' => $form, 'completion_percent' => $data['completion_percent'],
            'declarations_accepted' => $data['declarations_accepted'] ?? $application->declarations_accepted,
            'lock_version' => $application->lock_version + 1, 'last_activity_at' => now()])->save();
        $audit->record('application.draft_updated', ['subject_type' => AdmissionApplication::class, 'subject_id' => $application->id, 'before' => $before, 'after' => $form, 'classification' => 'confidential']);
        $outbox->publish('application.draft_updated', 'application', (string) $application->id, ['section' => $data['section'], 'lock_version' => $application->lock_version]);

        return ApiResponse::data($this->serialize($application->refresh()));
    }

    public function payment(Request $request, AdmissionApplication $application, AuditRecorder $audit, OutboxPublisher $outbox, SetupResolver $setups): JsonResponse
    {
        $this->assertOwner($request, $application);
        $data = $request->validate(['channel' => ['required', 'string', 'max:40'], 'payer_reference' => ['nullable', 'string', 'max:80']]);
        $feeSetup = $setups->active('payment.application_fee');
        $channelSetup = $setups->active('payment.channels_providers');
        if (! in_array($data['channel'], $channelSetup->configuration['channels'] ?? [], true)) {
            throw ApiException::unprocessable('PAYMENT_CHANNEL_UNAVAILABLE', 'That payment channel is not active.');
        }
        $amount = (int) $feeSetup->configuration['amount'];
        $currency = (string) $feeSetup->configuration['currency'];
        $attempt = DB::transaction(function () use ($application, $data, $request, $audit, $outbox, $setups, $feeSetup, $channelSetup, $amount, $currency): ApplicationPaymentAttempt {
            $attempt = new ApplicationPaymentAttempt;
            $attempt->forceFill(['admission_application_id' => $application->id, 'reference' => 'PAY-'.strtoupper(Str::random(12)), 'channel' => $data['channel'],
                'provider' => $data['channel'], 'amount' => $amount, 'expected_amount' => $amount, 'currency' => $currency, 'status' => 'INITIATED',
                'idempotency_key' => (string) $request->header('Idempotency-Key'), 'expires_at' => now()->addMinutes(15), 'created_by' => $request->user()->id])->save();
            $application->forceFill(['payment_status' => 'INITIATED', 'fee_amount_expected' => $amount, 'fee_currency' => $currency])->save();
            $setups->use('payment.application_fee', ApplicationPaymentAttempt::class, (string) $attempt->id, 'fee_calculation');
            $setups->use('payment.channels_providers', ApplicationPaymentAttempt::class, (string) $attempt->id, 'channel_selection');
            $audit->record('application_fee.initiated', ['subject_type' => ApplicationPaymentAttempt::class, 'subject_id' => $attempt->id, 'after' => ['amount' => $amount, 'currency' => $currency, 'channel' => $data['channel'], 'fee_setup_version_id' => $feeSetup->id, 'channel_setup_version_id' => $channelSetup->id]]);
            $outbox->publish('application_fee.initiated', 'application', (string) $application->id, ['attempt_id' => $attempt->id]);

            return $attempt;
        });

        return ApiResponse::accepted(['id' => $attempt->id, 'reference' => $attempt->reference, 'status' => $attempt->status, 'amount' => $amount, 'currency' => $currency, 'setup_version_id' => $feeSetup->id, 'expires_at' => $attempt->expires_at?->toIso8601String()]);
    }

    public function submit(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow, OutboxPublisher $outbox): JsonResponse
    {
        $this->assertOwner($request, $application);
        $application = $workflow->submit($application);
        $outbox->publish('application.submitted', 'application', (string) $application->id, ['application_number' => $application->application_number]);

        return ApiResponse::data(['id' => $application->id, 'application_number' => $application->application_number, 'status' => $application->status,
            'submission_receipt_number' => $application->submission_receipt_number, 'submitted_at' => $application->submitted_at?->toIso8601String()]);
    }

    private function assertOwner(Request $request, AdmissionApplication $application): void
    {
        if ($application->applicant->user_id !== $request->user()->id) {
            throw ApiException::notFound('Application');
        }
    }

    private function serialize(AdmissionApplication $application): array
    {
        return ['id' => $application->id, 'application_number' => $application->application_number, 'status' => $application->status,
            'payment_status' => $application->payment_status ?? 'NOT_STARTED', 'completion_percent' => $application->completion_percent,
            'lock_version' => $application->lock_version, 'form_data' => $application->form_data,
            'programme' => $application->relationLoaded('offering') ? ['id' => $application->offering->id, 'title' => $application->offering->course->name,
                'intake' => $application->offering->intake->name] : null, 'updated_at' => $application->updated_at?->toIso8601String()];
    }
}
