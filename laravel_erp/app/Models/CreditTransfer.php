<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'name', 'admission_number', 'course_code', 'course_name',
    'programme_code', 'programme_name', 'prior_institution', 'credits', 'status', 'status_type',
])]
final class CreditTransfer extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
