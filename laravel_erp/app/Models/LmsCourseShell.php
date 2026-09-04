<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['shell_code', 'course_title', 'faculty', 'instructor', 'intake_cohort', 'delivery_mode', 'enrolled_count', 'modules_count', 'status'])]
final class LmsCourseShell extends Model
{
    protected $table = 'lms_course_shells';
}
