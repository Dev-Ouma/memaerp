<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CohortYear;
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
            'activeAcademicYear' => $years->where('status', 'Active')->first()?->name ?? '2026/2027',
            'currentTrimester' => 'Trimester II (Jan - Apr 2027)',
            'registeredStudents' => 14850,
            'censusAuditStatus' => 'Senate Approved',
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
        $stats = [
            'totalActiveCohorts' => 24,
            'odelVirtualCohorts' => 16,
            'regularCampusCohorts' => 8,
            'totalEnrolledInCohorts' => 14850,
        ];

        $cohorts = [
            [
                'id' => 1,
                'cohort_code' => 'COH-2026-SEP-MAIN',
                'cohort_name' => 'September 2026 Main Intake Cohort',
                'academic_year' => '2026/2027',
                'intake_session' => 'September Trimester I',
                'study_mode' => 'ODeL & Virtual Campus',
                'capacity' => 2500,
                'enrolled' => 2180,
                'graduation_expected' => 'November 2030',
                'status' => 'Active / Enrolling',
            ],
            [
                'id' => 2,
                'cohort_code' => 'COH-2027-JAN-INT',
                'cohort_name' => 'January 2027 Intermediate Intake',
                'academic_year' => '2026/2027',
                'intake_session' => 'January Trimester II',
                'study_mode' => 'ODeL Virtual Learning',
                'capacity' => 1800,
                'enrolled' => 1420,
                'graduation_expected' => 'April 2031',
                'status' => 'Active / Enrolling',
            ],
            [
                'id' => 3,
                'cohort_code' => 'COH-2027-MAY-EXEC',
                'cohort_name' => 'May 2027 Executive & PG Cohort',
                'academic_year' => '2026/2027',
                'intake_session' => 'May Trimester III',
                'study_mode' => 'Executive Hybrid (Weekend)',
                'capacity' => 600,
                'enrolled' => 120,
                'graduation_expected' => 'November 2029',
                'status' => 'Registration Open',
            ],
            [
                'id' => 4,
                'cohort_code' => 'COH-2025-SEP-MAIN',
                'cohort_name' => 'September 2025 Continuing Cohort',
                'academic_year' => '2025/2026',
                'intake_session' => 'September Trimester I',
                'study_mode' => 'ODeL & Virtual Campus',
                'capacity' => 2200,
                'enrolled' => 2085,
                'graduation_expected' => 'November 2029',
                'status' => 'Continuing (Year 2)',
            ],
        ];

        return view('cohort.cohort-creation', compact('stats', 'cohorts'));
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
