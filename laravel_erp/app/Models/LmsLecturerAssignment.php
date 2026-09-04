<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['assignment_ref', 'instructor_name', 'course_shell', 'department', 'role', 'access_level', 'teaching_assistant', 'office_hours', 'status'])]
final class LmsLecturerAssignment extends Model
{
    protected $table = 'lms_lecturer_assignments';
}
