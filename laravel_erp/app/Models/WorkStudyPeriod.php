<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['trimester', 'academic_year', 'application_start', 'application_deadline', 'total_budget', 'committed_budget', 'hourly_rate', 'max_weekly_hours', 'target_beneficiaries', 'status'])]
final class WorkStudyPeriod extends Model
{
    protected $table = 'work_study_periods';
}
