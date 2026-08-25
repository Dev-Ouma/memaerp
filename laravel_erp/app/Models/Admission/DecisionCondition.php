<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DecisionCondition extends Model
{
    use HasUuids;

    protected $table = 'decision_conditions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'decision_id',
        'code',
        'description',
        'due_date',
        'is_mandatory',
        'status',
        'evidence_document_id',
        'cleared_by',
        'cleared_at',
        'clearance_note',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_mandatory' => 'boolean',
            'cleared_at' => 'datetime',
        ];
    }
}
