<?php

declare(strict_types=1);

namespace App\Modules\Course\Services;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

final class CourseReportService
{
    /** @param Collection<int, Course> $courses */
    public function catalogue(Collection $courses, string $format): Response
    {
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($courses): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['code', 'title', 'credits', 'lecture_hours', 'lab_hours', 'tutorial_hours', 'department', 'status']);
                foreach ($courses as $course) {
                    fputcsv($output, [
                        $course->code, $course->title, $course->credits, $course->lecture_hours,
                        $course->lab_hours, $course->tutorial_hours, $course->department?->code, $course->status,
                    ]);
                }
                fclose($output);
            }, 'course-catalogue.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.course-catalogue', ['courses' => $courses])
            ->setPaper('a4')->download('course-catalogue.pdf');
    }

    /** @param Collection<int, CourseOffering> $offerings */
    public function sections(Collection $offerings, string $format): Response
    {
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($offerings): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['course_code', 'title', 'section', 'campus', 'term', 'lecturer', 'enrolled', 'capacity', 'status']);
                foreach ($offerings as $offering) {
                    $lecturer = $offering->lecturer;
                    fputcsv($output, [
                        $offering->course?->code, $offering->course?->title, $offering->section_code,
                        $offering->campus?->code, $offering->term?->code,
                        $lecturer?->email, $offering->enrolled_count, $offering->max_capacity, $offering->status,
                    ]);
                }
                fclose($output);
            }, 'semester-sections.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.course-sections', ['offerings' => $offerings])
            ->setPaper('a4')->download('semester-sections.pdf');
    }

    public function syllabus(Course $course): Response
    {
        if ($course->department === null) {
            throw new LogicException('A syllabus requires its owning department.');
        }
        $safeName = str_replace(['/', '\\', ' '], '-', $course->code);

        return Pdf::loadView('reports.course-syllabus', ['course' => $course])
            ->setPaper('a4')->download("syllabus-{$safeName}.pdf");
    }
}
