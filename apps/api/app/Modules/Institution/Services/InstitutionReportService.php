<?php

declare(strict_types=1);

namespace App\Modules\Institution\Services;

use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\CalendarEvent;
use App\Modules\Institution\Models\Campus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class InstitutionReportService
{
    public function directory(string $institutionId, string $format): Response
    {
        $campuses = Campus::query()->where('institution_id', $institutionId)
            ->with('faculties.departments')->orderBy('name')->get();

        if ($format === 'json') {
            return new JsonResponse(['data' => $campuses]);
        }

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($campuses): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['campus_code', 'campus_name', 'faculty_code', 'faculty_name', 'department_code', 'department_name', 'status']);
                foreach ($campuses as $campus) {
                    foreach ($campus->faculties as $faculty) {
                        foreach ($faculty->departments as $department) {
                            fputcsv($output, [$campus->code, $campus->name, $faculty->code, $faculty->name, $department->code, $department->name, $department->status]);
                        }
                    }
                }
                fclose($output);
            }, 'institutional-directory.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.institution-directory', ['campuses' => $campuses])
            ->setPaper('a4', 'landscape')->download('institutional-directory.pdf');
    }

    public function calendar(string $institutionId, ?string $academicYearId = null): Response
    {
        $year = AcademicYear::query()->where('institution_id', $institutionId)
            ->when($academicYearId !== null, fn ($query) => $query->whereKey($academicYearId), fn ($query) => $query->current())
            ->with(['terms' => fn ($query) => $query->orderBy('sequence')])->firstOrFail();
        $events = CalendarEvent::query()->where('institution_id', $institutionId)
            ->where('academic_year_id', $year->id)->orderBy('starts_at')->get();

        $safeCode = str_replace(['/', '\\'], '-', $year->code);

        return Pdf::loadView('reports.academic-calendar', ['year' => $year, 'events' => $events])
            ->setPaper('a4')->download("academic-calendar-{$safeCode}.pdf");
    }
}
