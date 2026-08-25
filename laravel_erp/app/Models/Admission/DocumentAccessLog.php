<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;

class DocumentAccessLog extends Model
{
    protected $table = 'document_access_logs';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'application_document_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'correlation_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}
