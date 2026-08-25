<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConsentRecord extends Model
{
    use HasUuids;

    protected $table = 'consent_records';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'policy_type',
        'policy_version',
        'accepted',
        'recorded_at',
        'source_channel',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'accepted' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }
}
