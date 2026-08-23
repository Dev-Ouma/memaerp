<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\TermGpa;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

final class ProgressionService
{
    public function __construct(private readonly GpaCalculationService $gpa) {}

    /** @return Collection<int, TermGpa> */
    public function publishResults(string $institutionId, string $termId): Collection
    {
        $records = $this->gpa->calculateBatch($institutionId, $termId);
        TermGpa::query()
            ->where('institution_id', $institutionId)
            ->where('term_id', $termId)
            ->update(['is_published' => true, 'published_at' => now()]);

        return $records;
    }

    /** @return Collection<int, TermGpa> */
    public function myResults(Student $student): Collection
    {
        return TermGpa::query()
            ->where('student_id', $student->id)
            ->where('is_published', true)
            ->with(['term.academicYear'])
            ->orderByDesc('calculated_at')
            ->get();
    }

    public function resultSlip(Student $student, Term $term): Response
    {
        $record = TermGpa::query()
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('is_published', true)
            ->firstOrFail();

        $student->load(['person', 'programme']);

        return Pdf::loadView('reports.result-slip', ['student' => $student, 'term' => $term, 'record' => $record])
            ->setPaper('a4')
            ->download('result-slip-'.$student->student_number.'-'.$term->code.'.pdf');
    }
}
