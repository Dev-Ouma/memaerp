<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReviewAssignment extends Model
{
    use HasUuids;

    protected $table = 'review_assignments';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'workflow_step_id',
        'assignee_id',
        'assigned_by',
        'stage',
        'role_code',
        'status',
        'priority',
        'due_at',
        'started_at',
        'completed_at',
        'delegated_to',
        'reassigned_from',
        'conflict_declared',
        'conflict_note',
        'recusal_reason',
        'escalated_at',
        'escalated_to',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'conflict_declared' => 'boolean',
            'escalated_at' => 'datetime',
        ];
    }
}
