<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DecisionBatch extends Model
{
    use HasUuids;

    protected $table = 'decision_batches';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'reference',
        'decision_type',
        'outcome',
        'filter_snapshot',
        'preview',
        'item_count',
        'status',
        'rationale',
        'created_by',
        'approved_by',
        'approved_at',
        'applied_at',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'filter_snapshot' => 'array',
            'preview' => 'array',
            'approved_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }
}
