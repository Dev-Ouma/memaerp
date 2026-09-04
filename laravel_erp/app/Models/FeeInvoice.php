<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no', 'student_id', 'fee_structure_id', 'registration_period_id', 'course_enrolment_id',
    'amount_invoiced', 'amount_paid', 'status', 'issued_at',
])]
final class FeeInvoice extends Model
{
    protected function casts(): array
    {
        return [
            'amount_invoiced' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class, 'registration_period_id');
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrolment::class, 'course_enrolment_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function outstanding(): float
    {
        return max(0, (float) $this->amount_invoiced - (float) $this->amount_paid);
    }

    public function refreshSettlementStatus(): void
    {
        $outstanding = $this->outstanding();
        $this->status = match (true) {
            $outstanding <= 0.009 => 'SETTLED',
            (float) $this->amount_paid > 0 => 'PARTIAL',
            default => 'OPEN',
        };
        $this->save();
    }
}
