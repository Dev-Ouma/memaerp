<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StudentConversion extends Model
{
    use HasUuids;

    protected $table = 'student_conversions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'idempotency_key',
        'person_id',
        'student_id',
        'student_number',
        'programme_admission_id',
        'admission_roll_id',
        'status',
        'converted_by',
        'converted_at',
        'failure_code',
        'failure_reason',
        'external_reference',
        'payload',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
