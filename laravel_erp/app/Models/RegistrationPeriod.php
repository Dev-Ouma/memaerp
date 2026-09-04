<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'title', 'academic_session_id', 'starts_on', 'regular_deadline', 'late_deadline',
    'min_units', 'max_units', 'financial_gating', 'late_penalty_amount', 'status',
])]
final class RegistrationPeriod extends Model
{
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'regular_deadline' => 'date',
            'late_deadline' => 'date',
            'financial_gating' => 'boolean',
            'late_penalty_amount' => 'decimal:2',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(CourseEnrolment::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }
}
