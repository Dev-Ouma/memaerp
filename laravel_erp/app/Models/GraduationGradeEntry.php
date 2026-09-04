<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['student_id', 'student_name', 'reg_no', 'cgpa', 'classification', 'grades_distribution', 'status'])]
final class GraduationGradeEntry extends Model
{
    protected $table = 'graduation_grade_entries';
}
