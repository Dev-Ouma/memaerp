<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CohortYear;
use App\Models\InstitutionCohort;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CohortController extends Controller
{
    /**
     * 1. Academic Year Setup (CRUD Master)
     */
    public function academicYear(Request $request): View
    {
        $years = CohortYear::orderBy('start_date', 'desc')->get();

        $stats = [
            'activeAcademicYear' => $years->where('status', 'Active')->first()?->name ?? '—',
            'currentTrimester' => $years->where('status', 'Active')->first()?->description ?? '—',
            'registeredStudents' => Student::query()->count(),
            'censusAuditStatus' => $years->isNotEmpty() ? 'Loaded from database' : 'No years configured',
            'totalYears' => $years->count(),
        ];

        return view('cohort.academic-year', compact('stats', 'years'));
    }

    public function storeAcademicYear(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:cohort_academic_years,name'],
            'code' => ['required', 'string', 'max:30', 'unique:cohort_academic_years,code'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', 'in:Active,Closed,Upcoming'],
            'description' => ['nullable', 'string'],
        ]);

        $year = CohortYear::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'description' => ($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$year->name}' created successfully.",
                'year' => $year,
            ]);
        }

        return redirect()->route('cohort.academic-year')->with('success', "Academic Year '{$year->name}' created successfully.");
    }

    public function updateAcademicYear(Request $request, CohortYear $academicYear): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:cohort_academic_years,name,'.$academicYear->id],
            'code' => ['required', 'string', 'max:30', 'unique:cohort_academic_years,code,'.$academicYear->id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', 'in:Active,Closed,Upcoming'],
            'description' => ['nullable', 'string'],
        ]);

        $academicYear->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'description' => ($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$academicYear->name}' updated successfully.",
            ]);
        }

        return redirect()->route('cohort.academic-year')->with('success', "Academic Year '{$academicYear->name}' updated successfully.");
    }

    public function destroyAcademicYear(Request $request, CohortYear $academicYear): RedirectResponse|JsonResponse
    {
        $name = $academicYear->name;
        $academicYear->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$name}' moved to Recycle Bin.",
            ]);
        }

        return redirect()->route('cohort.academic-year')->with('success', "Academic Year '{$name}' moved to Recycle Bin.");
    }

    /**
     * 2. Cohort Creation
     */
    public function cohortCreation(Request $request): View
    {
        $records = InstitutionCohort::query()->latest()->get();
        $stats = [
            'totalActiveCohorts' => $records->filter(fn (InstitutionCohort $c): bool => str_contains(strtolower($c->status), 'active'))->count(),
            'odelVirtualCohorts' => $records->filter(fn (InstitutionCohort $c): bool => str_contains(strtolower((string) $c->study_mode), 'odel') || str_contains(strtolower((string) $c->study_mode), 'virtual'))->count(),
            'regularCampusCohorts' => $records->filter(fn (InstitutionCohort $c): bool => str_contains(strtolower((string) $c->study_mode), 'campus') || str_contains(strtolower((string) $c->study_mode), 'regular'))->count(),
            'totalEnrolledInCohorts' => (int) $records->sum('enrolled'),
        ];

        $cohorts = $records->map(fn (InstitutionCohort $row): array => [
            'id' => $row->id,
            'cohort_code' => $row->cohort_code,
            'cohort_name' => $row->cohort_name,
            'academic_year' => $row->academic_year ?? '—',
            'intake_session' => $row->intake_session ?? '—',
            'study_mode' => $row->study_mode ?? '—',
            'capacity' => (int) $row->capacity,
            'enrolled' => (int) $row->enrolled,
            'graduation_expected' => $row->graduation_expected ?? '—',
            'status' => $row->status,
        ])->all();

        return view('cohort.cohort-creation', compact('stats', 'cohorts'))->with('operationalCreate', [
            'title' => 'Add cohort',
            'hint' => 'Persists to institution_cohorts.',
            'action' => route('cohort.cohort-creation.store'),
            'fields' => [
                ['name' => 'cohort_code', 'label' => 'Cohort code', 'required' => true],
                ['name' => 'cohort_name', 'label' => 'Cohort name', 'required' => true],
                ['name' => 'academic_year', 'label' => 'Academic year'],
                ['name' => 'intake_session', 'label' => 'Intake session'],
                ['name' => 'study_mode', 'label' => 'Study mode'],
                ['name' => 'capacity', 'label' => 'Capacity', 'type' => 'number'],
                ['name' => 'enrolled', 'label' => 'Enrolled', 'type' => 'number'],
                ['name' => 'graduation_expected', 'label' => 'Graduation expected'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeCohort(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cohort_code' => ['required', 'string', 'max:80', 'unique:institution_cohorts,cohort_code'],
            'cohort_name' => ['required', 'string', 'max:190'],
            'academic_year' => ['nullable', 'string', 'max:40'],
            'intake_session' => ['nullable', 'string', 'max:120'],
            'study_mode' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'enrolled' => ['nullable', 'integer', 'min:0'],
            'graduation_expected' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        InstitutionCohort::query()->create([
            ...$data,
            'capacity' => $data['capacity'] ?? 0,
            'enrolled' => $data['enrolled'] ?? 0,
            'status' => $data['status'] ?? 'Active',
        ]);

        return back()->with('success', 'Cohort saved.');
    }

    /**
     * 3. Programme Cohort Mapping List
     */
    public function programmeCohortMapping(Request $request): View
    {
        $mappings = [];
        $stats = [
            'totalProgrammeMappings' => 0,
            'mappedDegreeProgrammes' => 0,
            'mappedCurriculaVersions' => 0,
            'activeMappedStudents' => Student::query()->count(),
        ];

        return view('cohort.programme-cohort-mapping', compact('stats', 'mappings'));
    }

    /**
     * 4. Programme Cohort Publish - Finance
     */
    public function publishFinance(Request $request): View
    {
        $financeSchedules = [];
        $stats = [
            'publishedFinanceSchedules' => 0,
            'totalBilledVolume' => 'KES 0',
            'directPaymentReconciled' => 'KES 0',
            'pendingFinancePublish' => 0,
        ];

        return view('cohort.publish-finance', compact('stats', 'financeSchedules'));
    }

    /**
     * 5. Cohort Transfer
     */
    public function cohortTransfer(Request $request): View
    {
        $transfers = [];
        $stats = [
            'totalCohortTransfers' => 0,
            'approvedMigrations' => 0,
            'pendingDeanReview' => 0,
            'specialDeferrals' => 0,
        ];

        return view('cohort.cohort-transfer', compact('stats', 'transfers'));
    }
}
