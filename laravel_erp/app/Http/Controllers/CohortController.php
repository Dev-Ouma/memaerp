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
        $stats = [
            'totalProgrammeMappings' => 48,
            'mappedDegreeProgrammes' => 18,
            'mappedCurriculaVersions' => 24,
            'activeMappedStudents' => 14850,
        ];

        $mappings = [
            [
                'id' => 1,
                'mapping_code' => 'PCM-2026-BCS',
                'programme_code' => 'MEMA-BCS',
                'programme_name' => 'Bachelor of Science in Computer Science',
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'curriculum_version' => 'v2026.1 (CUE Approved)',
                'school' => 'School of Science & Technology',
                'enrolled_scholars' => 640,
                'progression_year' => 'Year 1 Trimester 2',
                'status' => 'Active Mapping',
            ],
            [
                'id' => 2,
                'mapping_code' => 'PCM-2026-MDS',
                'programme_code' => 'MEMA-MDS',
                'programme_name' => 'Master of Data Science',
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'curriculum_version' => 'v2025.2 (Modular)',
                'school' => 'School of Science & Technology',
                'enrolled_scholars' => 185,
                'progression_year' => 'Year 1 Trimester 2',
                'status' => 'Active Mapping',
            ],
            [
                'id' => 3,
                'mapping_code' => 'PCM-2026-BBA',
                'programme_code' => 'MEMA-BBA',
                'programme_name' => 'Bachelor of Business Administration',
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'curriculum_version' => 'v2026.1 (Trimester Track)',
                'school' => 'School of Business & Economics',
                'enrolled_scholars' => 520,
                'progression_year' => 'Year 1 Trimester 2',
                'status' => 'Active Mapping',
            ],
            [
                'id' => 4,
                'mapping_code' => 'PCM-2027-BCS',
                'programme_code' => 'MEMA-BCS',
                'programme_name' => 'Bachelor of Science in Computer Science',
                'cohort_code' => 'COH-2027-JAN-INT',
                'curriculum_version' => 'v2026.1 (CUE Approved)',
                'school' => 'School of Science & Technology',
                'enrolled_scholars' => 380,
                'progression_year' => 'Year 1 Trimester 1',
                'status' => 'Active Mapping',
            ],
        ];

        return view('cohort.programme-cohort-mapping', compact('stats', 'mappings'));
    }

    /**
     * 4. Programme Cohort Publish - Finance
     */
    public function publishFinance(Request $request): View
    {
        $stats = [
            'publishedFinanceSchedules' => 36,
            'totalBilledVolume' => 'KES 342,500,000',
            'directPaymentReconciled' => 'KES 284,900,000 (83.2%)',
            'pendingFinancePublish' => 4,
        ];

        $financeSchedules = [
            [
                'id' => 1,
                'publish_ref' => 'PUB-FIN-2026-BCS',
                'programme_name' => 'BSc. Computer Science (Sept 2026 Cohort)',
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'tuition_fee_per_trimester' => 'KES 45,000',
                'statutory_fees' => 'KES 8,500',
                'fee_billing_schedule' => 'Auto-Invoice at Unit Registration',
                'minimum_exam_clearance' => '100% Tuition Fee Cleared',
                'finance_approval' => 'Finance Director Published',
                'publish_status' => 'Live in Billing Engine',
            ],
            [
                'id' => 2,
                'publish_ref' => 'PUB-FIN-2026-MDS',
                'programme_name' => 'Master of Data Science (Sept 2026 Cohort)',
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'tuition_fee_per_trimester' => 'KES 75,000',
                'statutory_fees' => 'KES 12,000',
                'fee_billing_schedule' => 'Auto-Invoice Trimester Start',
                'minimum_exam_clearance' => '100% Cleared (R19 Gating)',
                'finance_approval' => 'Finance Director Published',
                'publish_status' => 'Live in Billing Engine',
            ],
            [
                'id' => 3,
                'publish_ref' => 'PUB-FIN-2027-BCS',
                'programme_name' => 'BSc. Computer Science (Jan 2027 Cohort)',
                'cohort_code' => 'COH-2027-JAN-INT',
                'tuition_fee_per_trimester' => 'KES 45,000',
                'statutory_fees' => 'KES 8,500',
                'fee_billing_schedule' => 'Auto-Invoice at Unit Registration',
                'minimum_exam_clearance' => '100% Tuition Fee Cleared',
                'finance_approval' => 'Finance Director Published',
                'publish_status' => 'Live in Billing Engine',
            ],
            [
                'id' => 4,
                'publish_ref' => 'PUB-FIN-2027-MAY-EXEC',
                'programme_name' => 'Executive MBA / Postgrad (May 2027 Cohort)',
                'cohort_code' => 'COH-2027-MAY-EXEC',
                'tuition_fee_per_trimester' => 'KES 90,000',
                'statutory_fees' => 'KES 15,000',
                'fee_billing_schedule' => 'Trimester Installment 50:50',
                'minimum_exam_clearance' => '100% Cleared Prior to Exam',
                'finance_approval' => 'Under Senior Accountant Audit',
                'publish_status' => 'Pending Finance Publish',
            ],
        ];

        return view('cohort.publish-finance', compact('stats', 'financeSchedules'));
    }

    /**
     * 5. Cohort Transfer
     */
    public function cohortTransfer(Request $request): View
    {
        $stats = [
            'totalCohortTransfers' => 124,
            'approvedMigrations' => 108,
            'pendingDeanReview' => 12,
            'specialDeferrals' => 4,
        ];

        $transfers = [
            [
                'id' => 1,
                'transfer_no' => 'CT-2027-0051',
                'student_name' => 'Victor Kipkorir Cheruiyot',
                'reg_no' => 'MEMA/BCS/2025/0312',
                'programme' => 'BSc. Computer Science',
                'source_cohort' => 'COH-2025-SEP-MAIN',
                'target_cohort' => 'COH-2026-SEP-MAIN',
                'transfer_reason' => 'Medical Deferral (1 Trimester Sabbatical)',
                'academic_credit_status' => 'All 8 Units Carried Forward',
                'fee_credit_transferred' => 'KES 45,000 Preserved',
                'approval_status' => 'Approved by Registrar Academic',
            ],
            [
                'id' => 2,
                'transfer_no' => 'CT-2027-0052',
                'student_name' => 'Caroline Akinyi Okumu',
                'reg_no' => 'MEMA/BBA/2025/0844',
                'programme' => 'Bachelor of Business Administration',
                'source_cohort' => 'COH-2025-SEP-MAIN',
                'target_cohort' => 'COH-2027-JAN-INT',
                'transfer_reason' => 'Accelerated Trimester Progression Track',
                'academic_credit_status' => 'Fast-track Approved',
                'fee_credit_transferred' => 'KES 45,000 Applied',
                'approval_status' => 'Approved by Registrar Academic',
            ],
            [
                'id' => 3,
                'transfer_no' => 'CT-2027-0053',
                'student_name' => 'Francis Mwangi Kamau',
                'reg_no' => 'MEMA/BIT/2026/0119',
                'programme' => 'BSc. Information Technology',
                'source_cohort' => 'COH-2026-SEP-MAIN',
                'target_cohort' => 'COH-2027-JAN-INT',
                'transfer_reason' => 'Employment Schedule Shift (Moved to Evening Virtual Track)',
                'academic_credit_status' => 'Pending HOD Unit Audit',
                'fee_credit_transferred' => 'Under Verification',
                'approval_status' => 'Pending Faculty Dean Endorsement',
            ],
        ];

        return view('cohort.cohort-transfer', compact('stats', 'transfers'));
    }
}
