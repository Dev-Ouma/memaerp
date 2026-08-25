<?php

namespace App\Models;

use App\Models\Admission\PaymentTransaction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['applicant_profile_id', 'programme_offering_id', 'application_number', 'status', 'form_data', 'completion_percent', 'lock_version', 'declarations_accepted', 'submitted_at', 'decision_at'])] final class AdmissionApplication extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['form_data' => 'array', 'declarations_accepted' => 'boolean', 'submitted_at' => 'datetime', 'decision_at' => 'datetime'];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ProgrammeOffering::class, 'programme_offering_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ApplicationPaymentAttempt::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'admission_application_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function offer(): HasOne
    {
        return $this->hasOne(AdmissionOffer::class);
    }

    public function isPaid(): bool
    {
        $expectedAmount = (int) ($this->fee_amount_expected ?? 1000);
        $expectedCurrency = $this->fee_currency ?? 'KES';

        return $this->paymentTransactions()
            ->where('is_authoritative_fee', true)
            ->whereIn('status', ['PAID', 'WAIVED'])
            ->where('amount', $expectedAmount)
            ->where('currency', $expectedCurrency)
            ->exists()
            || $this->payments()
                ->whereIn('status', ['PAID', 'WAIVED'])
                ->where('amount', $expectedAmount)
                ->where('currency', $expectedCurrency)
                ->exists();
    }
}
