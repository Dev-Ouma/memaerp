<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'designation', 'department', 'joining_date', 'stage', 'progress', 'checklist', 'status'])]
final class SmhrOnboardingCandidate extends Model
{
    protected $table = 'smhr_onboarding_candidates';
}
