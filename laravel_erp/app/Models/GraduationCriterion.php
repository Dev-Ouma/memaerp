<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['programme', 'min_credits', 'min_cgpa', 'thesis_required', 'clearance_nodes', 'status'])]
final class GraduationCriterion extends Model
{
    protected $table = 'graduation_criteria';
}
