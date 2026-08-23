<?php

declare(strict_types=1);

namespace App\Modules\Institution\Services;

use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AcademicCalendarService
{
    public function activateAcademicYear(AcademicYear $year, string $resolutionReference): AcademicYear
    {
        return DB::transaction(function () use ($year, $resolutionReference): AcademicYear {
            /** @var AcademicYear $locked */
            $locked = AcademicYear::query()->whereKey($year->id)->lockForUpdate()->firstOrFail();

            if ($locked->terms()->count() === 0) {
                throw ValidationException::withMessages(['terms' => ['At least one term must be configured before activation.']]);
            }

            AcademicYear::query()
                ->where('institution_id', $locked->institution_id)
                ->whereKeyNot($locked->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'status' => 'ARCHIVED']);

            $locked->auditReason('Academic year activated under Senate resolution '.$resolutionReference)
                ->forceFill([
                    'senate_resolution_reference' => $resolutionReference,
                    'senate_approved_at' => now(),
                    'published_at' => now(),
                    'status' => 'ACTIVE',
                    'is_current' => true,
                ])->save();

            return $locked->fresh(['terms']) ?? $locked;
        });
    }

    public function activateTerm(Term $term): Term
    {
        return DB::transaction(function () use ($term): Term {
            /** @var Term $locked */
            $locked = Term::query()->whereKey($term->id)->lockForUpdate()->firstOrFail();
            $year = $locked->academicYear()->lockForUpdate()->firstOrFail();

            if (! $year->is_current || $year->status !== 'ACTIVE') {
                throw ValidationException::withMessages(['academic_year_id' => ['The academic year must be active before a term can be activated.']]);
            }

            $overlapExists = Term::query()
                ->where('institution_id', $locked->institution_id)
                ->where('study_mode_code', $locked->study_mode_code)
                ->whereKeyNot($locked->id)
                ->where('status', 'ACTIVE')
                ->whereDate('starts_on', '<=', $locked->ends_on)
                ->whereDate('ends_on', '>=', $locked->starts_on)
                ->lockForUpdate()
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'starts_on' => ['ERR-CAL-001: This term overlaps an active term for the same study mode.'],
                ]);
            }

            Term::query()
                ->where('institution_id', $locked->institution_id)
                ->where('study_mode_code', $locked->study_mode_code)
                ->whereKeyNot($locked->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'status' => 'ARCHIVED']);

            $locked->auditReason('Academic term activated and published')
                ->forceFill(['status' => 'ACTIVE', 'is_current' => true, 'published_at' => now()])
                ->save();

            return $locked->fresh(['academicYear']) ?? $locked;
        });
    }
}
