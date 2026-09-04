<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\BudgetProposal;
use App\Models\CalendarEvent;
use App\Models\CohortYear;
use App\Models\Course;
use App\Models\ExamSchedule;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = auth()->user();

        return view('dashboard', match ($user->activeRole()) {
            'student' => $this->studentDashboard($user),
            'staff' => $this->teacherDashboard($user),
            'parent' => $this->parentDashboard($user),
            'applicant' => $this->applicantDashboard($user),
            default => $this->adminDashboard($request),
        });
    }

    private function applicantDashboard(User $user): array
    {
        $application = $user->applicantProfile?->applications()
            ->with(['offering.course', 'offering.intake', 'offer', 'documents', 'payments', 'histories'])
            ->latest()->first();

        $status = $application?->status ?? 'NOT_STARTED';

        $steps = [
            [
                'step' => 1,
                'title' => 'Profile & Programme',
                'description' => 'Personal details and selected programme offering',
                'status' => $application ? 'completed' : 'current',
                'icon' => 'user-check',
            ],
            [
                'step' => 2,
                'title' => 'Document Upload',
                'description' => 'Certificates, identification and academic transcripts',
                'status' => $application && $application->documents->isNotEmpty() ? 'completed' : ($application ? 'current' : 'pending'),
                'icon' => 'file-text',
            ],
            [
                'step' => 3,
                'title' => 'Application Fee',
                'description' => 'Payment processing and automated M-Pesa verification',
                'status' => $application && $application->payments->where('status', 'PAID')->isNotEmpty() ? 'completed' : ($application && $application->status !== 'DRAFT' ? 'current' : 'pending'),
                'icon' => 'credit-card',
            ],
            [
                'step' => 4,
                'title' => 'Admissions Review',
                'description' => 'Document verification, faculty shortlisting and sign-off',
                'status' => in_array($status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'], true) ? 'completed' : (in_array($status, ['SUBMITTED', 'UNDER_REVIEW', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING'], true) ? 'current' : 'pending'),
                'icon' => 'clipboard-check',
            ],
            [
                'step' => 5,
                'title' => 'Offer & Enrollment',
                'description' => 'Admission letter issuance, acceptance and student registration',
                'status' => $status === 'ENROLLED' ? 'completed' : (in_array($status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL'], true) ? 'current' : 'pending'),
                'icon' => 'award',
            ],
        ];

        $requiredDocTypes = ['NATIONAL_ID' => 'National ID / Birth Certificate', 'ACADEMIC_TRANSCRIPT' => 'KCSE Certificate / Transcript', 'PASSPORT_PHOTO' => 'Passport Size Photo'];
        $uploadedDocs = $application ? $application->documents->keyBy('document_type') : collect();

        $checklist = collect($requiredDocTypes)->map(function (string $label, string $type) use ($uploadedDocs): array {
            $doc = $uploadedDocs->get($type);

            return [
                'type' => $type,
                'label' => $label,
                'is_uploaded' => $doc !== null,
                'status' => $doc?->verification_status ?? 'NOT_UPLOADED',
                'document' => $doc,
            ];
        })->values()->all();

        $totalFee = (float) ($application?->offering?->application_fee ?? 1500.00);
        $totalPaid = (float) ($application?->payments->where('status', 'PAID')->sum('amount') ?? 0.00);
        $latestPayment = $application?->payments->sortByDesc('created_at')->first();

        return [
            'dashboardType' => 'applicant',
            'application' => $application,
            'steps' => $steps,
            'checklist' => $checklist,
            'totalFee' => $totalFee,
            'totalPaid' => $totalPaid,
            'isFeePaid' => $totalPaid >= $totalFee && $totalPaid > 0,
            'latestPayment' => $latestPayment,
            'offer' => $application?->offer,
        ];
    }

    private function adminDashboard(Request $request): array
    {
        $selectedAcademicYear = trim((string) $request->query('academic_year', ''));
        $selectedSemester = trim((string) $request->query('semester', ''));
        $selectedCohort = trim((string) $request->query('cohort', ''));
        $selectedProgramme = trim((string) $request->query('programme', ''));
        $selectedLevel = trim((string) $request->query('level', ''));

        // Query builders
        $applicationsQuery = AdmissionApplication::query()
            ->with(['applicant.user', 'offering.course', 'offering.intake']);
        $studentsQuery = Student::query()
            ->with(['user', 'course', 'academicSession']);
        $paymentsQuery = ApplicationPaymentAttempt::query();

        // 1. Academic Year Filter
        if ($selectedAcademicYear !== '') {
            $yearDigits = preg_replace('/[^0-9]/', '', substr($selectedAcademicYear, 0, 4));
            if ($yearDigits !== '') {
                $yearInt = (int) $yearDigits;
                $applicationsQuery->where(function ($q) use ($selectedAcademicYear, $yearInt) {
                    $q->whereHas('offering.intake', function ($iq) use ($selectedAcademicYear, $yearInt) {
                        $iq->where('name', 'ilike', "%{$selectedAcademicYear}%")
                            ->orWhere('name', 'ilike', "%{$yearInt}%")
                            ->orWhere('code', 'ilike', "%{$yearInt}%");
                    })->orWhereYear('created_at', $yearInt);
                });

                $studentsQuery->where(function ($q) use ($yearInt) {
                    $q->whereHas('academicSession', function ($sq) use ($yearInt) {
                        $sq->whereYear('start_date', $yearInt);
                    })->orWhere('admission_number', 'ilike', "%/{$yearInt}/%")
                        ->orWhereYear('created_at', $yearInt);
                });
            }
        }

        // 2. Cohort / Intake Filter
        if ($selectedCohort !== '') {
            $applicationsQuery->whereHas('offering.intake', function ($q) use ($selectedCohort) {
                $q->where('name', 'ilike', "%{$selectedCohort}%")
                    ->orWhere('code', 'ilike', "%{$selectedCohort}%");
            });
        }

        // 3. Programme / Course Filter
        if ($selectedProgramme !== '') {
            $applicationsQuery->where(function ($q) use ($selectedProgramme) {
                if (is_numeric($selectedProgramme)) {
                    $q->whereHas('offering', fn ($oq) => $oq->where('course_id', (int) $selectedProgramme));
                } else {
                    $q->whereHas('offering.course', fn ($cq) => $cq->where('code', 'ilike', $selectedProgramme)->orWhere('name', 'ilike', "%{$selectedProgramme}%"));
                }
            });

            $studentsQuery->where(function ($q) use ($selectedProgramme) {
                if (is_numeric($selectedProgramme)) {
                    $q->where('course_id', (int) $selectedProgramme);
                } else {
                    $q->whereHas('course', fn ($cq) => $cq->where('code', 'ilike', $selectedProgramme)->orWhere('name', 'ilike', "%{$selectedProgramme}%"));
                }
            });
        }

        // 4. Level Filter
        if ($selectedLevel !== '') {
            $levelKeywords = match (strtolower($selectedLevel)) {
                'undergraduate' => ['bachelor', 'bcs', 'bba', 'bse', 'bsc', 'bed', 'llb', 'mbchb', 'degree'],
                'postgraduate', 'masters', 'phd' => ['master', 'msc', 'mba', 'mph', 'phd', 'doctorate', 'postgraduate'],
                'diploma' => ['diploma', 'dip', 'hdip'],
                'certificate' => ['certificate', 'cert'],
                'short course', 'short' => ['executive', 'short', 'bootcamp', 'certificate'],
                default => [$selectedLevel],
            };

            $applicationsQuery->whereHas('offering.course', function ($cq) use ($levelKeywords) {
                $cq->where(function ($sub) use ($levelKeywords) {
                    foreach ($levelKeywords as $kw) {
                        $sub->orWhere('name', 'ilike', "%{$kw}%")->orWhere('code', 'ilike', "%{$kw}%");
                    }
                });
            });

            $studentsQuery->whereHas('course', function ($cq) use ($levelKeywords) {
                $cq->where(function ($sub) use ($levelKeywords) {
                    foreach ($levelKeywords as $kw) {
                        $sub->orWhere('name', 'ilike', "%{$kw}%")->orWhere('code', 'ilike', "%{$kw}%");
                    }
                });
            });
        }

        // Fetch filtered datasets
        $applications = $applicationsQuery->get();
        $filteredAppIds = $applications->pluck('id');
        $filteredStudentUserIds = $studentsQuery->pluck('user_id');

        $profiles = ApplicantProfile::query()
            ->with('user')
            ->when($filteredAppIds->isNotEmpty(), function ($q) use ($filteredAppIds) {
                $q->whereHas('applications', fn ($aq) => $aq->whereIn('id', $filteredAppIds));
            })
            ->get();

        $studentsCount = $studentsQuery->count();
        $staffCount = Staff::query()->whereHas('user', fn ($query) => $query->where('is_active', true))->count();
        $coursesCount = Course::query()->count();
        $subjectsCount = Subject::query()->count();
        $attendanceTotal = AttendanceRecord::query()->count();
        $attendanceRate = $this->percentage(AttendanceRecord::query()->where('present', true)->count(), $attendanceTotal);

        $totalApplications = $applications->count();
        $terminalStatuses = ['REJECTED', 'DECLINED', 'EXPIRED', 'WITHDRAWN', 'ENROLLED'];
        $inProgress = $applications->whereNotIn('status', $terminalStatuses)->count();
        $admitted = $applications->whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $accepted = $applications->whereIn('status', ['ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $enrolled = $applications->where('status', 'ENROLLED')->count();
        $rejected = $applications->where('status', 'REJECTED')->count();
        $offerRejected = $applications->where('status', 'DECLINED')->count();
        $submitted = $applications->whereNotIn('status', ['DRAFT'])->count();

        // Financials calculated for filtered scope
        if ($filteredAppIds->isNotEmpty()) {
            $paid = (float) ApplicationPaymentAttempt::query()
                ->whereIn('admission_application_id', $filteredAppIds)
                ->where('status', 'PAID')
                ->sum('amount');
            $target = (float) DB::table('admission_applications')
                ->join('programme_offerings', 'programme_offerings.id', '=', 'admission_applications.programme_offering_id')
                ->whereIn('admission_applications.id', $filteredAppIds)
                ->sum('programme_offerings.application_fee');
        } else {
            $paid = (float) ApplicationPaymentAttempt::query()->where('status', 'PAID')->sum('amount');
            $target = (float) DB::table('admission_applications')
                ->join('programme_offerings', 'programme_offerings.id', '=', 'admission_applications.programme_offering_id')
                ->sum('programme_offerings.application_fee');
        }

        $budgetRequested = (float) BudgetProposal::query()->sum('requested_amount');
        $budgetApproved = (float) BudgetProposal::query()->sum('approved_amount');
        $financialRate = $this->decimalPercentage($paid, $target);
        $budgetRate = $this->decimalPercentage($budgetApproved, $budgetRequested);
        $academicCoverage = $this->decimalPercentage(StudentResult::query()->distinct('subject_id')->count('subject_id'), $subjectsCount);
        $acceptanceRate = $this->decimalPercentage($accepted, $admitted);
        $auditReadiness = AuditLog::query()->exists() ? 100.0 : 0.0;

        $sources = $profiles->groupBy(fn (ApplicantProfile $profile): string => $profile->source_channel ?: 'Other')
            ->map->count()->sortDesc()->all();
        $months = collect(range(1, 12))->map(fn (int $month): array => [
            'month' => now()->startOfYear()->month($month)->format('M'),
            'value' => $applications->filter(fn (AdmissionApplication $application): bool => $application->created_at->year === now()->year && $application->created_at->month === $month)->count(),
        ])->all();
        $programmePopularity = $applications->groupBy(fn (AdmissionApplication $application): string => $application->offering?->course?->code ?? 'Unassigned')
            ->map(fn (Collection $items, string $code): array => ['code' => $code, 'count' => $items->count()])
            ->sortByDesc('count')->values()->take(10)->all();
        $programmeDistribution = $applications->groupBy(fn (AdmissionApplication $application): string => $application->offering?->course?->name ?? 'Unassigned')
            ->map(function (Collection $items, string $name): array {
                $total = max(1, $items->count());

                return [
                    'name' => $name,
                    'count' => $items->count(),
                    'admitted' => $this->percentage($items->whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count(), $total),
                    'registered' => $this->percentage($items->where('status', 'ENROLLED')->count(), $total),
                    'pending' => $this->percentage($items->whereNotIn('status', ['REJECTED', 'DECLINED', 'ENROLLED'])->count(), $total),
                ];
            })->sortByDesc('count')->values()->take(8)->all();
        $counties = $profiles->groupBy(fn (ApplicantProfile $profile): string => $profile->county ?: 'Not specified')
            ->map(fn (Collection $items, string $name): array => ['name' => $name, 'count' => $items->count()])
            ->sortByDesc('count')->values()->take(10)->all();

        $kuccps = $profiles->filter(fn (ApplicantProfile $profile): bool => str_contains(strtolower((string) $profile->source_channel), 'kuccps'))->count();
        $female = $profiles->where('user.gender', 'F')->count();
        $male = $profiles->where('user.gender', 'M')->count();
        $profileCount = max(1, $profiles->count());
        $supportCount = $profiles->where('has_support_need', true)->count();
        $youthCount = $profiles->filter(fn (ApplicantProfile $profile): bool => $profile->date_of_birth !== null && $profile->date_of_birth->age <= 35)->count();

        $graduatedCount = $studentsCount;
        $alumniCount = $studentsCount;

        $activeResearchProjects = 0;
        $researchSupervisors = 0;
        try {
            $activeResearchProjects = DB::table('pg_research_candidates')->count();
            $researchSupervisors = DB::table('pg_supervisors')->count();
        } catch (\Throwable) {
        }

        $researchGrants = (float) BudgetProposal::query()->where(function ($q) {
            $q->where('department', 'ilike', '%Research%')->orWhere('description', 'ilike', '%Research%');
        })->sum('approved_amount');

        $metrics = [
            'applications' => $totalApplications,
            'inProgress' => $inProgress,
            'interestedApplicants' => $profiles->count(),
            'reverify' => ApplicationDocument::query()->whereIn('verification_status', ['REJECTED', 'REQUIRES_REUPLOAD'])->count(),
            'admitted' => $admitted,
            'inReview' => $applications->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING'])->count(),
            'l2Rejected' => $rejected,
            'offerRejected' => $offerRejected,
            'enrolled' => $enrolled,
            'enrolledInReview' => $applications->where('status', 'READY_TO_ENROL')->count(),
            'accepted' => $accepted,
            'initiated' => $applications->where('status', 'DRAFT')->count(),
            'graduated' => $graduatedCount,
            'programmesCount' => $coursesCount,
            'alumniCount' => $alumniCount,
            'pendingAdmissions' => $inProgress,
            'dropOffRate' => number_format($this->decimalPercentage($rejected + $offerRejected, $totalApplications), 2).'%',
            'admissionsBySource' => $sources,
            'applicationTrends' => $months,
            'programmePopularity' => $programmePopularity,
            'schoolDistribution' => $programmeDistribution,
            'placement' => ['pssp' => round((($profiles->count() - $kuccps) / $profileCount) * 100, 1), 'kuccps' => round(($kuccps / $profileCount) * 100, 1)],
            'inclusivity' => ['disability' => round(($supportCount / $profileCount) * 100, 2), 'youth' => round(($youthCount / $profileCount) * 100, 2)],
            'gender' => ['female' => round(($female / $profileCount) * 100, 1), 'male' => round(($male / $profileCount) * 100, 1)],
            'counties' => $counties,
        ];
        $executive = [
            'financials' => ['collected' => $this->money($paid), 'target' => $this->money($target), 'rate' => $financialRate, 'yoy' => '0.0%', 'outstanding' => $this->money(max(0, $target - $paid))],
            'academicHealth' => ['complianceRate' => $academicCoverage, 'accreditedCount' => $coursesCount, 'totalProgrammes' => $coursesCount, 'studentFacultyRatio' => '1 : '.($staffCount > 0 ? (int) ceil($studentsCount / $staffCount) : 0), 'nursingCapacity' => 0.0],
            'retention' => ['rate' => $acceptanceRate, 'atRiskCount' => $rejected, 'interventionsActive' => $applications->whereIn('status', ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'])->count(), 'dropOffDelta' => '0.0%'],
            'research' => ['grantsTotal' => $this->money($researchGrants), 'activeProjects' => $activeResearchProjects, 'publicationsYtd' => $researchSupervisors, 'innovationPipeline' => $activeResearchProjects],
            'governance' => ['auditReadiness' => $auditReadiness, 'pendingSenateApprovals' => $applications->where('status', 'APPROVAL_PENDING')->count(), 'budgetUtilization' => $budgetRate],
        ];

        // Available filter options
        $availableYears = ['2026/2027', '2025/2026', '2024/2025', '2023/2024'];
        try {
            $dbYears = CohortYear::query()->pluck('name')->filter()->values()->all();
            if (! empty($dbYears)) {
                $availableYears = array_values(array_unique(array_merge($availableYears, $dbYears)));
            }
        } catch (\Throwable) {
        }

        $availableSemesters = [
            'Semester 1',
            'Semester 2',
            'Trimester 1',
            'Trimester 2',
            'Trimester 3',
        ];

        $availableCohorts = [
            '2026/2027 - September Intake',
            '2026/2027 - January Intake',
            '2026/2027 - May Intake',
            '2025/2026 - September Intake',
            '2025/2026 - January Intake',
        ];
        try {
            $dbIntakes = AdmissionIntake::query()->pluck('name')->filter()->values()->all();
            if (! empty($dbIntakes)) {
                $availableCohorts = array_values(array_unique(array_merge($availableCohorts, $dbIntakes)));
            }
        } catch (\Throwable) {
        }

        $availableProgrammes = Course::query()->orderBy('name')->get(['id', 'code', 'name']);

        $availableLevels = [
            'Undergraduate' => 'Undergraduate (Degree)',
            'Postgraduate' => 'Postgraduate (Masters / PhD)',
            'Diploma' => 'Diploma Programmes',
            'Certificate' => 'Certificate Courses',
            'Short Course' => 'Executive & Short Courses',
        ];

        $activeFilters = array_filter([
            'academic_year' => $selectedAcademicYear,
            'semester' => $selectedSemester,
            'cohort' => $selectedCohort,
            'programme' => $selectedProgramme,
            'level' => $selectedLevel,
        ]);

        $filtersPayload = [
            'academic_year' => $selectedAcademicYear,
            'semester' => $selectedSemester,
            'cohort' => $selectedCohort,
            'programme' => $selectedProgramme,
            'level' => $selectedLevel,
            'options' => [
                'academic_years' => $availableYears,
                'semesters' => $availableSemesters,
                'cohorts' => $availableCohorts,
                'programmes' => $availableProgrammes,
                'levels' => $availableLevels,
            ],
            'active' => $activeFilters,
            'active_count' => count($activeFilters),
            'has_active' => count($activeFilters) > 0,
        ];

        return [
            'dashboardType' => 'admin',
            'stats' => ['students' => $studentsCount, 'staff' => $staffCount, 'courses' => $coursesCount, 'subjects' => $subjectsCount],
            'students' => $studentsQuery->latest()->limit(6)->get(),
            'results' => StudentResult::query()->with(['student.user', 'subject'])->latest()->limit(5)->get(),
            'attendanceRate' => $attendanceRate,
            'stakeholders' => User::query()->where('role', '!=', 'admin')->where('is_active', true)->orderBy('role')->orderBy('name')->get(),
            'executive' => $executive,
            'signalRows' => [
                ['label' => 'Financial performance', 'values' => array_fill(0, 5, $financialRate)],
                ['label' => 'Academic quality', 'values' => array_fill(0, 5, $academicCoverage)],
                ['label' => 'Student retention', 'values' => array_fill(0, 5, $acceptanceRate)],
                ['label' => 'Governance readiness', 'values' => array_fill(0, 5, $auditReadiness)],
            ],
            'metrics' => $metrics,
            'filters' => $filtersPayload,
        ];
    }

    private function studentDashboard(User $user): array
    {
        $student = $user->student()->with(['course', 'academicSession'])->first();
        $total = $student ? AttendanceRecord::query()->where('student_id', $student->id)->count() : 0;
        $present = $student ? AttendanceRecord::query()->where('student_id', $student->id)->where('present', true)->count() : 0;
        $attendanceRate = $this->percentage($present, $total);

        $results = $student ? StudentResult::query()->with('subject.staff.user')->where('student_id', $student->id)->get() : collect();
        $subjects = $student?->course_id ? Subject::query()->with('staff.user')->where('course_id', $student->course_id)->get() : collect();

        // Calculate Average Score and GPA
        $avgScore = $results->isNotEmpty() ? round($results->avg(fn ($r) => (float) ($r->test_score + $r->exam_score)), 1) : 0.0;
        $gpa = $results->isNotEmpty() ? round($results->avg(function ($r): float {
            $tot = $r->test_score + $r->exam_score;
            if ($tot >= 70) {
                return 4.0;
            }
            if ($tot >= 60) {
                return 3.0;
            }
            if ($tot >= 50) {
                return 2.0;
            }
            if ($tot >= 40) {
                return 1.0;
            }

            return 0.0;
        }), 2) : 0.0;

        $academicStanding = $avgScore >= 70 ? 'Distinction / Dean\'s List' : ($avgScore >= 60 ? 'Credit Standing' : ($avgScore >= 50 ? 'Good Standing' : ($avgScore > 0 ? 'Academic Warning' : 'Enrolled')));

        // Per-subject attendance breakdown
        $subjectAttendance = collect();
        if ($student) {
            try {
                $subjectAttendance = AttendanceRecord::query()
                    ->join('attendances', 'attendances.id', '=', 'attendance_records.attendance_id')
                    ->where('attendance_records.student_id', $student->id)
                    ->select('attendances.subject_id', DB::raw('count(*) as total'), DB::raw('sum(case when attendance_records.present = true then 1 else 0 end) as present'))
                    ->groupBy('attendances.subject_id')
                    ->get()
                    ->keyBy('subject_id');
            } catch (\Throwable) {
            }
        }

        // Upcoming Exam Schedule
        $upcomingExams = collect();
        if ($subjects->isNotEmpty()) {
            try {
                $upcomingExams = ExamSchedule::query()
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->with(['subject', 'center'])
                    ->orderBy('exam_date')
                    ->limit(4)
                    ->get();
            } catch (\Throwable) {
            }
        }

        // Upcoming Calendar Events
        $upcomingEvents = collect();
        try {
            $upcomingEvents = CalendarEvent::query()
                ->where('user_id', $user->id)
                ->where('start_time', '>=', now()->startOfDay())
                ->orderBy('start_time')
                ->limit(4)
                ->get();
        } catch (\Throwable) {
        }

        return [
            'dashboardType' => 'student',
            'student' => $student,
            'results' => $results,
            'subjects' => $subjects,
            'attendanceRate' => $attendanceRate,
            'attendancePresent' => $present,
            'attendanceTotal' => $total,
            'avgScore' => $avgScore,
            'gpa' => $gpa,
            'academicStanding' => $academicStanding,
            'subjectAttendance' => $subjectAttendance,
            'upcomingExams' => $upcomingExams,
            'upcomingEvents' => $upcomingEvents,
        ];
    }

    private function teacherDashboard(User $user): array
    {
        $teacher = $user->staffProfile()->with('course')->first();
        $subjects = $teacher ? Subject::query()->with('course')->where('staff_id', $teacher->id)->get() : collect();
        $studentCount = $teacher?->course_id ? Student::query()->where('course_id', $teacher->course_id)->count() : 0;
        $subjectIds = $subjects->pluck('id');

        $recentResults = $subjectIds->isEmpty()
            ? collect()
            : StudentResult::query()->with(['student.user', 'subject'])->whereIn('subject_id', $subjectIds)->latest()->limit(8)->get();

        $allSubjectResults = $subjectIds->isEmpty()
            ? collect()
            : StudentResult::query()->whereIn('subject_id', $subjectIds)->get();

        $gradedCount = $allSubjectResults->count();
        $classAverage = $allSubjectResults->isNotEmpty()
            ? round($allSubjectResults->avg(fn ($r) => (float) ($r->test_score + $r->exam_score)), 1)
            : 0.0;

        $attendanceSessionsCount = 0;
        if ($subjectIds->isNotEmpty()) {
            try {
                $attendanceSessionsCount = Attendance::query()->whereIn('subject_id', $subjectIds)->count();
            } catch (\Throwable) {
            }
        }

        $assignedStudents = collect();
        if ($teacher?->course_id) {
            $assignedStudents = Student::query()
                ->with(['user', 'course'])
                ->where('course_id', $teacher->course_id)
                ->limit(8)
                ->get();
        }

        return [
            'dashboardType' => 'teacher',
            'teacher' => $teacher,
            'subjects' => $subjects,
            'studentCount' => $studentCount,
            'recentResults' => $recentResults,
            'gradedCount' => $gradedCount,
            'classAverage' => $classAverage,
            'attendanceSessionsCount' => $attendanceSessionsCount,
            'assignedStudents' => $assignedStudents,
        ];
    }

    private function parentDashboard(User $user): array
    {
        $children = $user->children()->with(['user', 'course', 'academicSession'])->get();
        $studentIds = $children->pluck('id');

        $childResults = $studentIds->isEmpty()
            ? collect()
            : StudentResult::query()->with(['student.user', 'subject'])->whereIn('student_id', $studentIds)->get();

        $childAttendance = $this->childAttendance($studentIds);

        $childMetrics = $children->mapWithKeys(function (Student $child) use ($childResults, $childAttendance): array {
            $results = $childResults->where('student_id', $child->id);
            $avgScore = $results->isNotEmpty() ? round($results->avg(fn ($r) => (float) ($r->test_score + $r->exam_score)), 1) : 0.0;
            $attendance = $childAttendance[$child->id] ?? 0;

            return [$child->id => [
                'avgScore' => $avgScore,
                'publishedResultsCount' => $results->count(),
                'attendanceRate' => $attendance,
                'status' => $avgScore >= 60 ? 'Good Academic Progress' : ($avgScore > 0 ? 'Needs Attention' : 'Active Enrollment'),
            ]];
        })->all();

        return [
            'dashboardType' => 'parent',
            'children' => $children,
            'childResults' => $childResults,
            'childAttendance' => $childAttendance,
            'childMetrics' => $childMetrics,
        ];
    }

    private function childAttendance(Collection $studentIds): array
    {
        return $studentIds->mapWithKeys(function (int $studentId): array {
            $total = AttendanceRecord::query()->where('student_id', $studentId)->count();
            $present = AttendanceRecord::query()->where('student_id', $studentId)->where('present', true)->count();

            return [$studentId => $this->percentage($present, $total)];
        })->all();
    }

    private function percentage(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round(($part / $whole) * 100) : 0;
    }

    private function decimalPercentage(float|int $part, float|int $whole): float
    {
        return $whole > 0 ? round(($part / $whole) * 100, 1) : 0.0;
    }

    private function money(float|int $amount): string
    {
        return 'KES '.number_format($amount, 2);
    }
}
