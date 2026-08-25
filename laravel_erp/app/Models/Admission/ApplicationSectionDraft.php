<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApplicationSectionDraft extends Model
{
    use HasUuids;

    protected $table = 'application_section_drafts';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'section_key',
        'payload',
        'completion_percent',
        'is_complete',
        'validation_errors',
        'lock_version',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_complete' => 'boolean',
            'validation_errors' => 'array',
        ];
    }
}
