<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['admission_application_id', 'reference', 'channel', 'amount', 'currency', 'status', 'idempotency_key', 'paid_at', 'receipt_number', 'provider_payload'])] final class ApplicationPaymentAttempt extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'provider_payload' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}
