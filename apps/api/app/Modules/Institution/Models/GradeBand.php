<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GradeBand extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'institution.grade_bands';

    protected $fillable = [
        'grading_scale_id', 'letter', 'min_mark', 'max_mark', 'grade_point', 'is_pass',
    ];

    protected function casts(): array
    {
        return [
            'min_mark' => 'decimal:2',
            'max_mark' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_pass' => 'boolean',
        ];
    }

    /** @return BelongsTo<GradingScale, $this> */
    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }
}
