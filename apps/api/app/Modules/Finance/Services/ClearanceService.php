<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Student\Models\Student;

final class ClearanceService
{
    public const REGISTRATION_THRESHOLD = 70.0;

    /** @return array<string, mixed> */
    public function forPerson(string $institutionId, string $personId, ?string $termId = null): array
    {
        $query = Invoice::query()
            ->where('institution_id', $institutionId)
            ->where('person_id', $personId);

        if ($termId !== null) {
            $query->where('term_id', $termId);
        }

        $due = (float) (clone $query)->sum('amount_due');
        $paid = (float) (clone $query)->sum('amount_paid');
        $balance = max(0, $due - $paid);
        $percent = $due > 0 ? round(($paid / $due) * 100, 2) : 100.0;

        return [
            'total_due' => $due,
            'total_paid' => $paid,
            'balance' => $balance,
            'payment_percentage' => $percent,
            'registration_cleared' => $percent >= self::REGISTRATION_THRESHOLD,
            'exam_cleared' => $balance <= 0 && $due > 0 ? $paid >= $due : $due === 0.0 || $balance <= 0,
            'graduation_cleared' => $balance <= 0,
        ];
    }

    /** @return array<string, mixed> */
    public function forStudent(Student $student, ?string $termId = null): array
    {
        return $this->forPerson($student->institution_id, $student->person_id, $termId);
    }

    public function registrationCleared(Student $student, ?string $termId = null): bool
    {
        return (bool) $this->forStudent($student, $termId)['registration_cleared'];
    }

    public function examCleared(Student $student, ?string $termId = null): bool
    {
        return (bool) $this->forStudent($student, $termId)['exam_cleared'];
    }
}
