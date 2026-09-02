<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DeletionActionRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'deletion_record_id', 'action', 'requested_by', 'reason', 'status',
        'decided_by', 'decided_at', 'decision_note',
    ];

    protected function casts(): array
    {
        return ['decided_at' => 'immutable_datetime'];
    }

    public function deletionRecord(): BelongsTo
    {
        return $this->belongsTo(DeletionRecord::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
