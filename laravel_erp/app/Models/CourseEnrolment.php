<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'registration_period_id', 'student_id', 'subject_id', 'status', 'registered_at',
])]
final class CourseEnrolment extends Model
{
    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class, 'registration_period_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(FeeInvoice::class);
    }
}
