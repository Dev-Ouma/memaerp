<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EducationHistory extends Model
{
    use HasUuids;

    protected $table = 'education_history';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'institution_name',
        'qualification_level_id',
        'qualification_name',
        'index_number',
        'started_on',
        'ended_on',
        'mean_grade',
        'mean_points',
        'subject_grades',
        'country_code',
        'evidence_document_id',
        'is_highest',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'mean_points' => 'decimal:2',
            'subject_grades' => 'array',
            'is_highest' => 'boolean',
        ];
    }
}
