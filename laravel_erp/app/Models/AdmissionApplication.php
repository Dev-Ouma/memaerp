<?php

namespace App\Models;

use App\Models\Admission\ApprovalStep;
use App\Models\Admission\Decision;
use App\Models\Admission\PaymentTransaction;
use App\Models\Admission\ReviewAssignment;
use App\Models\Admission\StudentConversion;
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

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'admission_application_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class, 'admission_application_id');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'admission_application_id')->orderBy('step_order');
    }

    public function conversion(): HasOne
    {
        return $this->hasOne(StudentConversion::class, 'admission_application_id');
    }

    /**
     * Readiness is derived from what the application actually holds, never from
     * whichever handler happened to run last: a form save after an upload used
     * to reset a complete application back to 80% and lock the applicant out of
     * submission with no way back.
     *
     * @return array{percent:int, outstanding:list<string>}
     */
    public function completionState(): array
    {
        $outstanding = [];

        if (! $this->hasCompletedForm()) {
            $outstanding[] = 'Personal, identification and education details';
        }
        if (! $this->declarations_accepted) {
            $outstanding[] = 'Declaration and consent';
        }
        if ($this->documents()->count() < 1) {
            $outstanding[] = 'At least one supporting document';
        }

        $sections = 3;

        return [
            'percent' => (int) round((($sections - count($outstanding)) / $sections) * 100),
            'outstanding' => $outstanding,
        ];
    }

    /** Persist the derived completion figure and hand it back. */
    public function refreshCompletion(): int
    {
        $percent = $this->completionState()['percent'];
        if ((int) $this->completion_percent !== $percent) {
            $this->forceFill(['completion_percent' => $percent])->save();
        }

        return $percent;
    }

    /** Every mandatory form field the applicant is asked for must be present. */
    private function hasCompletedForm(): bool
    {
        $form = $this->form_data ?? [];
        $profile = $this->applicant;

        foreach (['gender', 'education'] as $key) {
            if (blank($form[$key] ?? null)) {
                return false;
            }
        }

        return $profile !== null
            && filled($profile->date_of_birth)
            && filled($profile->nationality)
            && filled($profile->identity_type)
            && filled($profile->identity_number);
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
