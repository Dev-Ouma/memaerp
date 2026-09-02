<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['grade_letter', 'min_marks', 'max_marks', 'gpa_points', 'performance_descriptor', 'is_active'])]
final class GradeScale extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'min_marks' => 'decimal:2', 'max_marks' => 'decimal:2', 'gpa_points' => 'decimal:2'];
    }
}
