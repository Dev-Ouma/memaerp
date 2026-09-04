<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['assignment_title', 'course_code', 'weight', 'submission_deadline', 'submissions_count', 'turnitin_status', 'grading_status'])]
final class LmsAssignment extends Model
{
    protected $table = 'lms_assignments';
}
