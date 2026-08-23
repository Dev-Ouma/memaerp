<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Iam\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AttendanceSessionService
{
    /** @return array{session: AttendanceSession, qr_token: string}> */
    public function open(User $lecturer, string $offeringId, ?string $teachingSlotId = null): array
    {
        $offering = CourseOffering::query()
            ->where('institution_id', $lecturer->institution_id)
            ->findOrFail($offeringId);

        if ($offering->lecturer_id !== $lecturer->id) {
            throw ValidationException::withMessages([
                'offering_id' => ['You are not the assigned lecturer for this offering.'],
            ]);
        }

        $open = AttendanceSession::query()
            ->where('course_offering_id', $offering->id)
            ->where('status', 'OPEN')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($open !== null) {
            throw ValidationException::withMessages([
                'offering_id' => ['An attendance session is already open for this offering.'],
            ]);
        }

        $plainToken = Str::random(48);
        $ttl = (int) config('attendance.qr_ttl_minutes', 5);

        $session = AttendanceSession::query()->create([
            'institution_id' => $offering->institution_id,
            'course_offering_id' => $offering->id,
            'teaching_slot_id' => $teachingSlotId,
            'lecturer_id' => $lecturer->id,
            'session_date' => Carbon::today(),
            'status' => 'OPEN',
            'qr_token_hash' => hash('sha256', $plainToken),
            'expires_at' => Carbon::now()->addMinutes($ttl),
            'opened_at' => Carbon::now(),
        ]);

        return ['session' => $session->load(['courseOffering.course']), 'qr_token' => $plainToken];
    }

    public function close(User $lecturer, string $sessionId): AttendanceSession
    {
        $session = AttendanceSession::query()
            ->where('institution_id', $lecturer->institution_id)
            ->findOrFail($sessionId);

        if ($session->lecturer_id !== $lecturer->id) {
            throw ValidationException::withMessages([
                'session_id' => ['You cannot close a session you did not open.'],
            ]);
        }

        $session->update([
            'status' => 'CLOSED',
            'closed_at' => Carbon::now(),
        ]);

        app(AttendanceThresholdService::class)->evaluateOffering($session->course_offering_id);

        return $session->fresh(['courseOffering.course', 'logs.student.person']);
    }

    public function findByToken(string $plainToken): ?AttendanceSession
    {
        return AttendanceSession::query()
            ->where('qr_token_hash', hash('sha256', $plainToken))
            ->where('status', 'OPEN')
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }
}
