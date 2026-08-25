<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdmissionRoll extends Model
{
    use HasUuids;

    protected $table = 'admission_rolls';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'reference',
        'title',
        'admission_intake_id',
        'programme_offering_id',
        'query_snapshot',
        'status',
        'version',
        'total_entries',
        'eligible_entries',
        'checksum',
        'generated_document_id',
        'created_by',
        'submitted_by',
        'approved_by',
        'approved_at',
        'frozen_by',
        'frozen_at',
        'supersedes_roll_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'query_snapshot' => 'array',
            'approved_at' => 'datetime',
            'frozen_at' => 'datetime',
        ];
    }
}
