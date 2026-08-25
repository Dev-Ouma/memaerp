<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    use HasUuids;

    protected $table = 'export_jobs';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'report_code',
        'requested_by',
        'purpose',
        'filters',
        'columns',
        'format',
        'row_limit',
        'status',
        'row_count',
        'storage_disk',
        'storage_key',
        'checksum',
        'size_bytes',
        'classification',
        'includes_sensitive_columns',
        'requested_at',
        'started_at',
        'completed_at',
        'expires_at',
        'download_count',
        'last_downloaded_at',
        'error_code',
        'error_detail',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'includes_sensitive_columns' => 'boolean',
            'requested_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
        ];
    }
}
