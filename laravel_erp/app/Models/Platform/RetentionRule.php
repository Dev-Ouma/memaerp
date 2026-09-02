<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RetentionRule extends Model
{
    use HasUuids;

    protected $table = 'retention_rules';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'code',
        'subject_type',
        'description',
        'retention_months',
        'disposal_action',
        'is_active',
        'version',
        'status',
        'effective_from',
        'effective_to',
        'created_by',
        'change_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'effective_from' => 'immutable_date',
            'effective_to' => 'immutable_date',
        ];
    }
}
