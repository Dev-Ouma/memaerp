<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DeletionRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'entity_type', 'model_type', 'record_id', 'deleted_by', 'deleted_by_role',
        'ip_address', 'user_agent', 'channel', 'action_type', 'reason',
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

    /**
     * @return BelongsTo<User, DeletionRecord>
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * @return BelongsTo<User, DeletionRecord>
     */
    public function restoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * @return BelongsTo<User, DeletionRecord>
     */
    public function purgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purged_by');
    }
}
