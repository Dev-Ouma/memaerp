<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasUuids;

    protected $table = 'approval_steps';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'decision_id',
        'step_order',
        'role_code',
        'approver_id',
        'status',
        'acted_at',
        'comment',
        'delegated_from',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }
}
