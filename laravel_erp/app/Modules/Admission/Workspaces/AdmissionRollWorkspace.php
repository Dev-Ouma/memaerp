<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Matriculation register: admitted applicants and the student records they
 * became. Registration numbers come from student_conversions, which is the only
 * place an admission is allowed to mint one.
 */
final class AdmissionRollWorkspace extends Workspace
{
    public function stats(): array
    {
        $roll = $this->roll();

        $withNumber = (clone $roll)->whereNotNull('sc.student_number')->count();
        $genderCounts = (clone $roll)
            ->leftJoin('people as p', 'p.id', '=', 'ap.person_id')
            ->selectRaw("count(*) filter (where lower(p.gender) in ('female','f')) as female, count(*) filter (where p.gender is not null) as known")
            ->first();

        return [
            'totalMatriculated' => (clone $roll)->count(),
            'regNumbersIssued' => $withNumber,
            'schoolsRepresented' => (clone $roll)->distinct()->count(DB::raw('coalesce(fac.name, c.name)')),
            'femaleRatio' => $genderCounts !== null && (int) $genderCounts->known > 0
                ? $this->percentage((int) $genderCounts->female, (int) $genderCounts->known).'%'
                : 'Not declared',
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->roll()
            ->leftJoin('students as st', 'st.id', '=', 'sc.student_id')
            ->select([
                'a.id as application_id', 'a.application_number', 'a.status as application_status',
                'u.name as student_name', 'c.name as programme', 'fac.name as school',
                'ai.cohort_label', 'ai.academic_year', 'ai.name as intake_name',
                'sc.student_number', 'sc.converted_at', 'sc.status as conversion_status',
                'st.admission_number',
            ]);

        if (($cohort = $filters['cohort'] ?? null) !== null && $cohort !== '') {
            $query->where('ai.id', $cohort);
        }
        if (($status = $filters['status'] ?? null) !== null && $status !== '') {
            $status === 'Matriculated'
                ? $query->whereNotNull('sc.student_number')
                : $query->whereNull('sc.student_number');
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByRaw('sc.converted_at desc nulls last')->orderBy('a.application_number')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->application_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'student_name' => $row->student_name,
                'programme' => $row->programme,
                'school' => $row->school ?? 'Unassigned',
                'cohort' => $row->cohort_label ?? $row->academic_year ?? $row->intake_name ?? '—',
                'admission_number' => $row->admission_number ?? $row->student_number ?? 'Pending issue',
                'enrolment_date' => $row->converted_at !== null ? date('d M Y', strtotime((string) $row->converted_at)) : 'Not enrolled',
                'status' => $this->rollStatus($row),
            ]);
    }

    private function roll(): Builder
    {
        return $this->applications()
            ->leftJoin('student_conversions as sc', 'sc.admission_application_id', '=', 'a.id')
            ->whereIn('a.status', self::ADMITTED_STATUSES);
    }

    private function rollStatus(object $row): string
    {
        return match (true) {
            $row->conversion_status === 'COMPLETED' => 'Matriculated',
            $row->conversion_status === 'FAILED' => 'Conversion failed',
            $row->application_status === 'ENROLLED' => 'Enrolled',
            $row->application_status === 'READY_TO_ENROL' => 'Ready to enrol',
            $row->application_status === 'ACCEPTED' => 'Offer accepted',
            default => 'Awaiting acceptance',
        };
    }

    public function cohorts(): array
    {
        return DB::table('admission_intakes')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
