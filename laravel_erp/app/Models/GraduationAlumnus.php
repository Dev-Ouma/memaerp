<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['alumni_code', 'student_name', 'reg_no', 'programme', 'contact', 'grad_year', 'status'])]
final class GraduationAlumnus extends Model
{
    protected $table = 'graduation_alumni';
}
