<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasUuids;

    protected $table = 'communications';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'admission_application_id',
        'recipient_user_id',
        'recipient_masked',
        'channel',
        'template_code',
        'template_version',
        'subject',
        'body_hash',
        'body_ref',
        'status',
        'attempts',
        'queued_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'failure_reason',
        'provider_message_id',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
