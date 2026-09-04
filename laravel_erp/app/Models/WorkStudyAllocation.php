<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['allocation_code', 'student_name', 'reg_no', 'assigned_position', 'department', 'supervisor', 'approved_weekly_hours', 'start_date', 'end_date', 'contract_status'])]
final class WorkStudyAllocation extends Model
{
    protected $table = 'work_study_allocations';
}
