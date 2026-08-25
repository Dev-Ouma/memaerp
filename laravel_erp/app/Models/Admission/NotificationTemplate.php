<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasUuids;

    protected $table = 'notification_templates';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'channel',
        'locale',
        'version',
        'subject',
        'body',
        'placeholders',
        'allows_sensitive_detail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
            'allows_sensitive_detail' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
