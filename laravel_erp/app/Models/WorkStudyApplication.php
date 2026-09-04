<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['app_no', 'student_name', 'reg_no', 'programme', 'preferred_role', 'current_gpa', 'need_category', 'fee_arrears', 'socio_economic_score', 'vetting_status'])]
final class WorkStudyApplication extends Model
{
    protected $table = 'work_study_applications';
}
