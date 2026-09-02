<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['admission_application_id', 'offer_number', 'verification_token', 'status', 'expires_at', 'conditions', 'checksum', 'issued_at', 'responded_at'])] final class AdmissionOffer extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['expires_at' => 'date', 'issued_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}
