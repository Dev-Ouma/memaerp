<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamCard;
use App\Modules\Finance\Services\ClearanceService;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ExamCardService
{
    public function __construct(private readonly ClearanceService $clearance) {}

    public function issue(Student $student, Term $term): ExamCard
    {
        if (! $this->clearance->examCleared($student, $term->id)) {
            abort(response()->json([
                'error' => ['code' => 'ERR-FIN-EXM', 'message' => 'Exam card requires full fee clearance.'],
            ], 402));
        }

        return ExamCard::query()->updateOrCreate(
            ['student_id' => $student->id, 'term_id' => $term->id],
            [
                'institution_id' => $student->institution_id,
                'qr_token' => hash('sha256', $student->id.'|'.$term->id.'|'.Str::random(16)),
                'status' => 'ACTIVE',
                'issued_at' => now(),
                'expires_at' => $term->exam_ends_on?->endOfDay(),
            ],
        );
    }

    public function renderPdf(Student $student, Term $term): Response
    {
        $card = $this->issue($student, $term);
        $student->load(['person', 'programme']);
        $enrollments = $student->termRegistrations()
            ->where('term_id', $term->id)
            ->with(['courseEnrollments.courseOffering.course'])
            ->first()?->courseEnrollments ?? collect();

        return Pdf::loadView('reports.exam-card', [
            'student' => $student,
            'term' => $term,
            'card' => $card,
            'courses' => $enrollments,
            'verifyUrl' => url('/api/v1/exams/verify-card/'.$card->qr_token),
        ])->setPaper('a4')->download(
            'exam-card-'.str_replace(['/', '\\'], '-', $student->student_number).'.pdf',
        );
    }

    public function verify(string $token): ?ExamCard
    {
        return ExamCard::query()
            ->with(['student.person', 'student.programme', 'term'])
            ->where('qr_token', $token)
            ->where('status', 'ACTIVE')
            ->first();
    }
}
