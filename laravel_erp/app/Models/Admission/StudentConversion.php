<?php

declare(strict_types=1);

namespace App\Models\Admission;

use App\Models\AdmissionApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }
}
