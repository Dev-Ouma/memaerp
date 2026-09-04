<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['timesheet_no', 'student_name', 'department', 'month_cycle', 'hours_claimed', 'hourly_rate', 'total_amount', 'supervisor_rating', 'supervisor_status'])]
final class WorkStudyTimesheet extends Model
{
    protected $table = 'work_study_timesheets';
}
