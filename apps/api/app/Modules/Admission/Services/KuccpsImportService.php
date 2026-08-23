<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Models\KuccpsPlacement;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Intake;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\PersonIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KuccpsImportService
{
    /**
     * @param  list<array{kuccps_index: string, applicant_name: string, programme_code: string, mean_grade?: string|null, aggregate_points?: float|null}>  $rows
     * @return array{imported: int, applications_created: int, batch: string}
     */
    public function import(string $institutionId, ?string $intakeId, array $rows): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages(['rows' => ['Provide at least one KUCCPS placement row.']]);
        }

        $batch = 'KUCCPS-'.now()->format('YmdHis');
        $campus = Campus::query()->where('institution_id', $institutionId)->where('is_main_campus', true)->first()
            ?? Campus::query()->where('institution_id', $institutionId)->firstOrFail();
        $year = AcademicYear::query()->where('institution_id', $institutionId)->where('is_current', true)->firstOrFail();
        $intake = $intakeId
            ? Intake::query()->where('institution_id', $institutionId)->whereKey($intakeId)->firstOrFail()
            : Intake::query()->where('institution_id', $institutionId)->where('status', 'ACTIVE')->first();

        $imported = 0;
        $created = 0;

        DB::transaction(function () use ($institutionId, $intake, $campus, $year, $rows, $batch, &$imported, &$created): void {
            foreach ($rows as $row) {
                $programme = Programme::query()
                    ->where('institution_id', $institutionId)
                    ->where('code', $row['programme_code'])
                    ->first();

                $placement = KuccpsPlacement::query()->create([
                    'institution_id' => $institutionId,
                    'intake_id' => $intake?->id,
                    'kuccps_index' => $row['kuccps_index'],
                    'applicant_name' => $row['applicant_name'],
                    'programme_code' => $row['programme_code'],
                    'programme_id' => $programme?->id,
                    'mean_grade' => $row['mean_grade'] ?? null,
                    'aggregate_points' => $row['aggregate_points'] ?? null,
                    'import_batch' => $batch,
                    'status' => $programme ? 'MATCHED' : 'UNMATCHED',
                ]);
                $imported++;

                if (! $programme instanceof Programme) {
                    continue;
                }

                $parts = preg_split('/\s+/', trim($row['applicant_name'])) ?: [];
                $given = $parts[0] ?? 'KUCCPS';
                $family = $parts[count($parts) - 1] ?? 'Applicant';
                $email = strtolower(str_replace(['/', ' '], ['', '.'], $row['kuccps_index'])).'@kuccps.mema.ac.ke';

                $person = Person::query()->firstOrCreate(
                    ['institution_id' => $institutionId, 'primary_email' => $email],
                    [
                        'given_name' => $given,
                        'family_name' => $family,
                        'nationality' => 'KE',
                    ],
                );

                PersonIdentity::query()->firstOrCreate(
                    [
                        'institution_id' => $institutionId,
                        'person_id' => $person->id,
                        'identity_type' => PersonIdentity::TYPE_APPLICANT,
                        'identifier' => $row['kuccps_index'],
                    ],
                    [
                        'status' => PersonIdentity::STATUS_ACTIVE,
                        'started_on' => now()->toDateString(),
                    ],
                );

                $application = Application::query()->create([
                    'institution_id' => $institutionId,
                    'person_id' => $person->id,
                    'programme_id' => $programme->id,
                    'campus_id' => $campus->id,
                    'academic_year_id' => $year->id,
                    'intake_id' => $intake?->id,
                    'application_number' => $this->nextApplicationNumber($institutionId),
                    'status' => 'SHORTLISTED',
                    'is_fee_paid' => true,
                    'mean_grade' => $row['mean_grade'] ?? null,
                    'qualification_score' => $row['aggregate_points'] ?? null,
                    'kcse_index_number' => $row['kuccps_index'],
                    'entry_path' => 'KUCCPS',
                    'submitted_at' => now(),
                    'secondary_school_name' => 'KUCCPS Placement',
                ]);

                $placement->forceFill([
                    'application_id' => $application->id,
                    'status' => 'LINKED',
                ])->save();
                $created++;
            }
        });

        return ['imported' => $imported, 'applications_created' => $created, 'batch' => $batch];
    }

    private function nextApplicationNumber(string $institutionId): string
    {
        $year = now()->format('Y');
        $count = Application::query()->where('institution_id', $institutionId)->withTrashed()->count() + 1;

        return sprintf('APP-%s-%05d', $year, $count).'-'.strtoupper(Str::random(2));
    }
}
