<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\BudgetProposal;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('dashboard', match ($user->activeRole()) {
            'student' => $this->studentDashboard($user),
            'staff' => $this->teacherDashboard($user),
            'parent' => $this->parentDashboard($user),
            'applicant' => $this->applicantDashboard($user),
            default => $this->adminDashboard(),
        });
    }

    private function applicantDashboard(User $user): array
    {
        return [
            'dashboardType' => 'applicant',
            'application' => $user->applicantProfile?->applications()
                ->with(['offering.course', 'offering.intake', 'offer', 'documents', 'payments', 'histories'])
                ->latest()->first(),
        ];
    }

    private function adminDashboard(): array
    {
        $applications = AdmissionApplication::query()
            ->with(['applicant.user', 'offering.course', 'offering.intake'])
            ->get();
        $profiles = ApplicantProfile::query()->with('user')->get();
        $studentsCount = Student::query()->count();
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

        $paid = (float) ApplicationPaymentAttempt::query()->where('status', 'PAID')->sum('amount');
        $target = (float) DB::table('admission_applications')
            ->join('programme_offerings', 'programme_offerings.id', '=', 'admission_applications.programme_offering_id')
            ->sum('programme_offerings.application_fee');
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
            'graduated' => 0,
            'programmesCount' => $coursesCount,
            'alumniCount' => 0,
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
            'research' => ['grantsTotal' => $this->money(0), 'activeProjects' => 0, 'publicationsYtd' => 0, 'innovationPipeline' => 0],
            'governance' => ['auditReadiness' => $auditReadiness, 'pendingSenateApprovals' => $applications->where('status', 'APPROVAL_PENDING')->count(), 'budgetUtilization' => $budgetRate],
        ];

        return [
            'dashboardType' => 'admin',
            'stats' => ['students' => $studentsCount, 'staff' => $staffCount, 'courses' => $coursesCount, 'subjects' => $subjectsCount],
            'students' => Student::query()->with(['user', 'course'])->latest()->limit(6)->get(),
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
        ];
    }

    private function studentDashboard(User $user): array
    {
        $student = $user->student()->with(['course', 'academicSession'])->first();
        $total = $student ? AttendanceRecord::query()->where('student_id', $student->id)->count() : 0;
        $present = $student ? AttendanceRecord::query()->where('student_id', $student->id)->where('present', true)->count() : 0;

        return ['dashboardType' => 'student', 'student' => $student, 'results' => $student ? StudentResult::query()->with('subject')->where('student_id', $student->id)->get() : collect(), 'attendanceRate' => $this->percentage($present, $total)];
    }

    private function teacherDashboard(User $user): array
    {
        $teacher = $user->staffProfile()->with('course')->first();
        $subjects = $teacher ? Subject::query()->with('course')->where('staff_id', $teacher->id)->get() : collect();

        return ['dashboardType' => 'teacher', 'teacher' => $teacher, 'subjects' => $subjects, 'studentCount' => $teacher?->course_id ? Student::query()->where('course_id', $teacher->course_id)->count() : 0, 'recentResults' => $subjects->isEmpty() ? collect() : StudentResult::query()->with(['student.user', 'subject'])->whereIn('subject_id', $subjects->pluck('id'))->latest()->limit(8)->get()];
    }

    private function parentDashboard(User $user): array
    {
        $children = $user->children()->with(['user', 'course', 'academicSession'])->get();
        $studentIds = $children->pluck('id');

        return ['dashboardType' => 'parent', 'children' => $children, 'childResults' => $studentIds->isEmpty() ? collect() : StudentResult::query()->with(['student.user', 'subject'])->whereIn('student_id', $studentIds)->get(), 'childAttendance' => $this->childAttendance($studentIds)];
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
