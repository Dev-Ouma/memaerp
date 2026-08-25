<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ScoringCriterion extends Model
{
    use HasUuids;

    protected $table = 'scoring_criteria';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'scoring_rubric_id',
        'code',
        'name',
        'description',
        'weight',
        'min_score',
        'max_score',
        'is_knockout',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'min_score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'is_knockout' => 'boolean',
        ];
    }
}
