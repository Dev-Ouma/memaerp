<?php

declare(strict_types=1);

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasUuids;

    protected $table = 'audit_events';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'occurred_at',
        'sequence_no',
        'actor_user_id',
        'actor_role',
        'action',
        'subject_type',
        'subject_id',
        'institution_id',
        'correlation_id',
        'source_channel',
        'ip_address',
        'user_agent',
        'before',
        'after',
        'classification',
        'previous_hash',
        'evidence_hash',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'before' => 'array',
            'after' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, AuditEvent>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
