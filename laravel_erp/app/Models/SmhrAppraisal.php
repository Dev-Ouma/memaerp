<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_id', 'name', 'dept', 'teaching_eval', 'research_publications', 'community_service', 'overall_score', 'grade', 'completed', 'status'])]
final class SmhrAppraisal extends Model
{
    protected $table = 'smhr_appraisals';
}
