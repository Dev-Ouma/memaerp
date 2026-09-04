<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['admission_application_id', 'reference', 'channel', 'amount', 'currency', 'status', 'idempotency_key', 'paid_at', 'receipt_number', 'provider_payload', 'institution_id', 'fee_setup_id', 'expected_amount', 'provider', 'provider_request_ref', 'payer_msisdn_masked', 'payer_account_masked', 'expires_at', 'correlation_id', 'created_by'])] final class ApplicationPaymentAttempt extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'expires_at' => 'datetime', 'provider_payload' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}
