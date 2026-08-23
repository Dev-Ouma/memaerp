<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Student\Models\StudentNumberSequence;
use Illuminate\Support\Facades\DB;

final class StudentNumberService
{
    public function allocate(string $institutionId, Programme $programme, AcademicYear $admissionYear): string
    {
        return DB::transaction(function () use ($institutionId, $programme, $admissionYear): string {
            $sequence = StudentNumberSequence::query()
                ->where('institution_id', $institutionId)
                ->where('programme_id', $programme->id)
                ->where('academic_year_id', $admissionYear->id)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = StudentNumberSequence::query()->create([
                    'institution_id' => $institutionId,
                    'programme_id' => $programme->id,
                    'academic_year_id' => $admissionYear->id,
                    'last_sequence' => 0,
                ]);
            }

            $next = $sequence->last_sequence + 1;
            $sequence->forceFill(['last_sequence' => $next])->save();

            $yearLabel = $admissionYear->starts_on?->format('Y')
                ?? (preg_match('/(\d{4})/', (string) $admissionYear->code, $matches) ? $matches[1] : (string) now()->year);

            return sprintf('%s/%s/%05d', $programme->code, $yearLabel, $next);
        });
    }
}
