<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReviewScore extends Model
{
    use HasUuids;

    protected $table = 'review_scores';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'application_review_id',
        'scoring_criteria_id',
        'raw_score',
        'weighted_score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'raw_score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
        ];
    }
}
