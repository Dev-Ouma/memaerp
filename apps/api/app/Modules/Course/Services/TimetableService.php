<?php

declare(strict_types=1);

namespace App\Modules\Course\Services;

use App\Modules\Course\Models\TeachingSlot;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Student\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class TimetableService
{
    /** @return Collection<int, TeachingSlot> */
    public function mySchedule(Student $student, ?string $termId = null): Collection
    {
        $offeringIds = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->when($termId !== null, fn ($q) => $q->whereHas('courseOffering', fn ($o) => $o->where('term_id', $termId)))
            ->pluck('course_offering_id');

        return TeachingSlot::query()
            ->where('institution_id', $student->institution_id)
            ->whereIn('course_offering_id', $offeringIds)
            ->where('status', 'ACTIVE')
            ->with(['courseOffering.course', 'room', 'lecturer'])
            ->orderBy('starts_at')
            ->get();
    }

    /** @param array<string, mixed> $payload */
    public function createSlot(array $payload): TeachingSlot
    {
        $this->assertNoClash(
            (string) $payload['institution_id'],
            $payload['room_id'] ?? null,
            $payload['lecturer_id'] ?? null,
            CarbonImmutable::parse((string) $payload['starts_at']),
            CarbonImmutable::parse((string) $payload['ends_at']),
        );

        return TeachingSlot::query()->create($payload);
    }

    /** @param array<string, mixed> $payload */
    public function clashCheck(array $payload): array
    {
        $starts = CarbonImmutable::parse((string) $payload['starts_at']);
        $ends = CarbonImmutable::parse((string) $payload['ends_at']);

        $roomClash = TeachingSlot::query()
            ->where('institution_id', $payload['institution_id'])
            ->where('room_id', $payload['room_id'])
            ->where('status', 'ACTIVE')
            ->where('starts_at', '<', $ends)
            ->where('ends_at', '>', $starts)
            ->exists();

        $lecturerClash = isset($payload['lecturer_id']) && TeachingSlot::query()
            ->where('institution_id', $payload['institution_id'])
            ->where('lecturer_id', $payload['lecturer_id'])
            ->where('status', 'ACTIVE')
            ->where('starts_at', '<', $ends)
            ->where('ends_at', '>', $starts)
            ->exists();

        return [
            'room_clash' => $roomClash,
            'lecturer_clash' => $lecturerClash,
            'has_clash' => $roomClash || $lecturerClash,
        ];
    }

    public function exportIcs(Student $student, ?string $termId = null): string
    {
        $slots = $this->mySchedule($student, $termId);
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//MEMA ERP//Timetable//EN'];

        foreach ($slots as $slot) {
            $course = $slot->courseOffering?->course;
            $summary = ($course?->code ?? 'CLASS').' — '.($course?->title ?? 'Lecture');
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$slot->id.'@mema.ac.ke';
            $lines[] = 'DTSTART:'.$slot->starts_at?->utc()->format('Ymd\THis\Z');
            $lines[] = 'DTEND:'.$slot->ends_at?->utc()->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:'.$summary;
            $lines[] = 'LOCATION:'.($slot->room?->name ?? 'TBA');
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    private function assertNoClash(
        string $institutionId,
        ?string $roomId,
        ?string $lecturerId,
        CarbonImmutable $starts,
        CarbonImmutable $ends,
    ): void {
        $result = $this->clashCheck([
            'institution_id' => $institutionId,
            'room_id' => $roomId,
            'lecturer_id' => $lecturerId,
            'starts_at' => $starts->toIso8601String(),
            'ends_at' => $ends->toIso8601String(),
        ]);

        if ($result['has_clash']) {
            throw ValidationException::withMessages(['schedule' => ['Teaching slot clashes with an existing booking.']]);
        }
    }
}
