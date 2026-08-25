<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasUuids;

    protected $table = 'document_templates';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'template_type',
        'version',
        'effective_from',
        'effective_to',
        'body',
        'placeholders',
        'signatory_name',
        'signatory_title',
        'signature_image_path',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'placeholders' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
