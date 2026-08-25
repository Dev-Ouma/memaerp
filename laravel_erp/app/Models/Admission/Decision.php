<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasUuids;

    protected $table = 'decisions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'decision_batch_id',
        'decision_type',
        'outcome',
        'reason_code',
        'rationale',
        'is_final',
        'decided_by',
        'decided_by_role',
        'decided_at',
        'checked_by',
        'checked_at',
        'superseded_by_decision_id',
        'correlation_id',
        'evidence_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
            'decided_at' => 'datetime',
            'checked_at' => 'datetime',
        ];
    }
}
