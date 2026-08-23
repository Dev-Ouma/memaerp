<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\StudentStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StudentStatusService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'ACTIVE' => ['ON_LEAVE', 'SUSPENDED', 'WITHDRAWN', 'GRADUATED'],
        'ON_LEAVE' => ['ACTIVE', 'WITHDRAWN'],
        'SUSPENDED' => ['ACTIVE', 'WITHDRAWN'],
        'GRADUATED' => [],
        'WITHDRAWN' => [],
    ];

    public function updateStatus(User $actor, Student $student, string $toStatus, string $reason): Student
    {
        $toStatus = strtoupper($toStatus);
        $fromStatus = $student->status;

        if ($fromStatus === $toStatus) {
            throw ValidationException::withMessages([
                'status' => ['The student is already in '.$toStatus.' status.'],
            ]);
        }

        if (! in_array($toStatus, Student::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid target status.'],
            ]);
        }

        $allowed = self::TRANSITIONS[$fromStatus] ?? [];
        if (! in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from {$fromStatus} to {$toStatus}."],
            ]);
        }

        return DB::transaction(function () use ($actor, $student, $fromStatus, $toStatus, $reason): Student {
            StudentStatusHistory::query()->create([
                'institution_id' => $student->institution_id,
                'student_id' => $student->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason,
                'changed_by' => $actor->id,
                'changed_at' => now(),
            ]);

            $attributes = ['status' => $toStatus];
            if ($toStatus === 'SUSPENDED') {
                $attributes['academic_standing'] = 'SUSPENDED';
            } elseif ($fromStatus === 'SUSPENDED' && $toStatus === 'ACTIVE') {
                $attributes['academic_standing'] = 'GOOD_STANDING';
            } elseif ($toStatus === 'WITHDRAWN') {
                $attributes['academic_standing'] = 'DISCONTINUED';
            }

            $student->auditReason($reason)->forceFill($attributes)->save();

            if ($toStatus !== 'ACTIVE' && $student->digital_id_status === 'ACTIVE') {
                $student->forceFill(['digital_id_status' => 'REVOKED'])->save();
            } elseif ($toStatus === 'ACTIVE' && $student->digital_id_status === 'REVOKED') {
                $student->forceFill(['digital_id_status' => 'ACTIVE'])->save();
            }

            return $student->fresh([
                'person',
                'programme.department',
                'campus',
                'admissionYear',
            ]) ?? $student;
        });
    }
}
