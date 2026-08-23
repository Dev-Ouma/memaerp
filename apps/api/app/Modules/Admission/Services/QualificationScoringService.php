<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Models\ProgrammeCutoff;

final class QualificationScoringService
{
    /** @var array<string, float> */
    private const GRADE_POINTS = [
        'A' => 84.0,
        'A-' => 78.0,
        'B+' => 72.0,
        'B' => 66.0,
        'B-' => 60.0,
        'C+' => 54.0,
        'C' => 48.0,
        'C-' => 42.0,
        'D+' => 36.0,
        'D' => 30.0,
        'D-' => 24.0,
        'E' => 12.0,
    ];

    public function scoreFromMeanGrade(string $meanGrade): float
    {
        $normalized = strtoupper(trim($meanGrade));
        if (isset(self::GRADE_POINTS[$normalized])) {
            return self::GRADE_POINTS[$normalized];
        }

        if (is_numeric($meanGrade)) {
            return round((float) $meanGrade, 2);
        }

        return 0.0;
    }

    public function meetsCutoff(Application $application): bool
    {
        $cutoff = ProgrammeCutoff::query()
            ->where('institution_id', $application->institution_id)
            ->where('programme_id', $application->programme_id)
            ->where('academic_year_id', $application->academic_year_id)
            ->where('is_active', true)
            ->first();

        if (! $cutoff instanceof ProgrammeCutoff) {
            return ($application->qualification_score ?? 0) >= 48.0;
        }

        $score = (float) ($application->qualification_score ?? 0);
        if ($score < (float) $cutoff->minimum_score) {
            return false;
        }

        if ($cutoff->minimum_mean_grade === null || $application->mean_grade === null) {
            return true;
        }

        $required = self::GRADE_POINTS[strtoupper($cutoff->minimum_mean_grade)] ?? 0.0;
        $actual = self::GRADE_POINTS[strtoupper($application->mean_grade)] ?? $score;

        return $actual >= $required;
    }
}
