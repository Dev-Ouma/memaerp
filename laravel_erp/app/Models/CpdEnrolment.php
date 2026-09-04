<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['participant_no', 'full_name', 'organization', 'course_enrolled', 'completion_progress', 'cpd_points_awarded', 'certificate_ref', 'status'])]
final class CpdEnrolment extends Model
{
    protected function casts(): array
    {
        return ['cpd_points_awarded' => 'decimal:2'];
    }
}
