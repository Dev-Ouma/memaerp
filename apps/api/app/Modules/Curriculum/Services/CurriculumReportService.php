<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Services;

use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use Barryvdh\DomPDF\Facade\Pdf;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

final class CurriculumReportService
{
    public function version(CurriculumVersion $version, string $format): Response
    {
        $version->load(['programme.department.faculty', 'effectiveYear', 'curriculumCourses.course', 'curriculumCourses.electiveGroup', 'electiveGroups', 'reviewSteps']);
        $programme = $version->programme;
        if (! $programme instanceof Programme) {
            throw new LogicException('A curriculum report requires its programme relation.');
        }
        $safeName = str_replace(['/', '\\'], '-', $programme->code.'-'.$version->version_code);

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($version): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['year', 'semester', 'course_code', 'course_title', 'credits', 'course_type', 'elective_group']);
                foreach ($version->curriculumCourses->sortBy(['year_level', 'semester']) as $item) {
                    fputcsv($output, [$item->year_level, $item->semester, $item->course?->code, $item->course?->title, $item->course?->credits, $item->course_type, $item->electiveGroup?->name]);
                }
                fclose($output);
            }, "curriculum-{$safeName}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.curriculum-handbook', ['version' => $version])
            ->setPaper('a4')->download("programme-handbook-{$safeName}.pdf");
    }
}
