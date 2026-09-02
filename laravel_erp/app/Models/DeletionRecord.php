<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class DeletionRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'entity_type', 'model_type', 'record_id', 'deleted_by', 'deleted_by_role', 'reason',
        'original_location', 'owner_type', 'owner_id', 'snapshot', 'deleted_at', 'purge_after',
        'retention_rule_id', 'restored_at', 'restored_by', 'purged_at', 'purged_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'deleted_at' => 'immutable_datetime',
            'purge_after' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
            'purged_at' => 'immutable_datetime',
        ];
    }
}
