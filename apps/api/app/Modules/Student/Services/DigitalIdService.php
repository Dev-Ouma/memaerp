<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class DigitalIdService
{
    public function issueToken(Student $student): string
    {
        return hash('sha256', $student->id.'|'.Str::random(32).'|'.now()->timestamp);
    }

    public function renderPdf(Student $student): Response
    {
        abort_unless($student->status === 'ACTIVE', 422, 'Digital ID is only available for active students.');
        abort_unless($student->digital_id_status === 'ACTIVE', 422, 'Digital ID has been revoked or replaced.');

        $student->loadMissing(['person', 'programme', 'campus', 'admissionYear']);

        $verifyUrl = url('/api/v1/students/verify-id/'.$student->digital_id_token);

        return Pdf::loadView('reports.digital-student-id', [
            'student' => $student,
            'verifyUrl' => $verifyUrl,
        ])->setPaper([0, 0, 242.65, 153.07])->download(
            'student-id-'.str_replace(['/', '\\'], '-', $student->student_number).'.pdf',
        );
    }

    public function verify(string $token): ?Student
    {
        return Student::query()
            ->with(['person', 'programme', 'campus'])
            ->where('digital_id_token', $token)
            ->where('status', 'ACTIVE')
            ->where('digital_id_status', 'ACTIVE')
            ->first();
    }
}
