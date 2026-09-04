<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['job_code', 'title', 'department', 'supervisor', 'hours_per_week', 'skills_required', 'slots_available', 'slots_filled', 'status'])]
final class WorkStudyPosition extends Model
{
    protected $table = 'work_study_positions';
}
