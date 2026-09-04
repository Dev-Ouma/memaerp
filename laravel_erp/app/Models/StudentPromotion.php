<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['student_id', 'student_name', 'reg_no', 'programme', 'from_stage', 'to_stage', 'cumulative_gpa', 'credits_passed', 'promotion_verdict', 'senate_date'])]
final class StudentPromotion extends Model
{
    protected function casts(): array
    {
        return ['cumulative_gpa' => 'decimal:2', 'senate_date' => 'date'];
    }
}
