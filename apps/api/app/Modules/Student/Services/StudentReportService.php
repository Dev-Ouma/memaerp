<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Admission\Models\Application;
use App\Modules\Student\Models\MatriculationLog;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StudentReportService
{
    public function dashboard(string $institutionId): array
    {
        $base = Student::query()->where('institution_id', $institutionId);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'ACTIVE')->count(),
            'on_leave' => (clone $base)->where('status', 'ON_LEAVE')->count(),
            'suspended' => (clone $base)->where('status', 'SUSPENDED')->count(),
            'graduated' => (clone $base)->where('status', 'GRADUATED')->count(),
            'matriculation_queue' => Application::query()
                ->where('institution_id', $institutionId)
                ->where('status', 'ACCEPTED')
                ->whereNotNull('documents_verified_at')
                ->count(),
            'matriculated_this_month' => MatriculationLog::query()
                ->where('institution_id', $institutionId)
                ->where('matriculated_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    public function matriculationRoll(string $institutionId, ?string $intakeId, string $format): Response
    {
        $logs = MatriculationLog::query()
            ->where('institution_id', $institutionId)
            ->when($intakeId !== null, function ($query) use ($intakeId): void {
                $query->whereHas('student', fn ($student) => $student->where('intake_id', $intakeId));
            })
            ->with(['student.person', 'student.programme', 'student.intake', 'matriculatedBy'])
            ->orderByDesc('matriculated_at')
            ->get();

        if ($format === 'json') {
            return new JsonResponse(['data' => $logs]);
        }

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($logs): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['student_number', 'full_name', 'programme', 'intake', 'matriculated_at', 'officer']);
                foreach ($logs as $log) {
                    fputcsv($output, [
                        $log->student?->student_number,
                        $log->student?->person?->full_name,
                        $log->student?->programme?->code,
                        $log->student?->intake?->code,
                        $log->matriculated_at?->toDateTimeString(),
                        $log->matriculatedBy?->email,
                    ]);
                }
                fclose($output);
            }, 'matriculation-register.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.matriculation-register', ['logs' => $logs])
            ->setPaper('a4', 'landscape')
            ->download('matriculation-register.pdf');
    }

    public function masterDirectory(string $institutionId, string $format): Response
    {
        $students = Student::query()
            ->where('institution_id', $institutionId)
            ->with(['person', 'programme', 'campus', 'admissionYear'])
            ->orderBy('student_number')
            ->get();

        if ($format === 'json') {
            return new JsonResponse(['data' => $students]);
        }

        return response()->streamDownload(function () use ($students): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }
            fputcsv($output, ['student_number', 'full_name', 'email', 'programme', 'campus', 'year', 'status', 'matriculated_on']);
            foreach ($students as $student) {
                fputcsv($output, [
                    $student->student_number,
                    $student->person?->full_name,
                    $student->person?->primary_email,
                    $student->programme?->code,
                    $student->campus?->code,
                    $student->current_year_level,
                    $student->status,
                    $student->matriculated_on?->toDateString(),
                ]);
            }
            fclose($output);
        }, 'student-master-directory.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
