<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cohort_code', 'cohort_name', 'academic_year', 'intake_session', 'study_mode', 'capacity', 'enrolled', 'graduation_expected', 'status'])]
final class InstitutionCohort extends Model
{
    protected $table = 'institution_cohorts';
}
