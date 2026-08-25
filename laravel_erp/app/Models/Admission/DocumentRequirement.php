<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    use HasUuids;

    protected $table = 'document_requirements';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'programme_offering_id',
        'code',
        'name',
        'description',
        'is_mandatory',
        'applies_to',
        'accepted_mime_types',
        'max_size_bytes',
        'min_count',
        'max_count',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'accepted_mime_types' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
