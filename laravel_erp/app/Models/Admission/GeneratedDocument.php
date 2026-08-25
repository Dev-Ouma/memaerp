<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GeneratedDocument extends Model
{
    use HasUuids;

    protected $table = 'generated_documents';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'document_type',
        'subject_type',
        'subject_id',
        'admission_application_id',
        'document_template_id',
        'template_version',
        'version',
        'storage_disk',
        'storage_key',
        'mime_type',
        'size_bytes',
        'checksum',
        'generated_by',
        'generated_at',
        'is_current',
        'superseded_at',
        'supersedes_document_id',
        'retention_until',
        'legal_hold',
        'classification',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'is_current' => 'boolean',
            'superseded_at' => 'datetime',
            'retention_until' => 'date',
            'legal_hold' => 'boolean',
        ];
    }
}
