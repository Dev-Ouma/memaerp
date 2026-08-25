<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasUuids;

    protected $table = 'workflow_steps';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'workflow_instance_id',
        'step_key',
        'name',
        'sequence',
        'required_role',
        'status',
        'outcome',
        'activated_at',
        'due_at',
        'completed_at',
        'paused_at',
        'paused_seconds',
        'escalated_at',
        'completed_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'paused_at' => 'datetime',
            'escalated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
