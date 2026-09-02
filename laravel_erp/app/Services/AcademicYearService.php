<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicSession;
use Carbon\CarbonImmutable;

final class AcademicYearService
{
    public function current(?CarbonImmutable $date = null): AcademicSession
    {
        $date ??= CarbonImmutable::now();
        $startYear = $date->month >= 9 ? $date->year : $date->year - 1;

        return AcademicSession::firstOrCreate([
            'start_date' => CarbonImmutable::create($startYear, 9, 1)->toDateString(),
            'end_date' => CarbonImmutable::create($startYear + 1, 8, 31)->toDateString(),
        ]);
    }
}
