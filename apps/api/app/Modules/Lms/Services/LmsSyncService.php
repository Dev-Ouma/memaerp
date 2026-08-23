<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Lms\Models\CourseMapping;
use App\Modules\Lms\Models\EnrollmentMapping;
use App\Modules\Lms\Models\SyncLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class LmsSyncService
{
    public function __construct(private readonly MoodleClient $moodle) {}

    public function syncCourse(CourseOffering $offering): CourseMapping
    {
        $existing = CourseMapping::query()->where('course_offering_id', $offering->id)->first();
        if ($existing !== null && $existing->status === 'SYNCED') {
            return $existing;
        }

        $log = $this->startLog($offering->institution_id, 'course', $offering->id, 'erp_to_lms');

        try {
            $offering->loadMissing(['course', 'term']);
            $shortname = sprintf('%s-%s', $offering->course?->code ?? 'COURSE', $offering->section_code);
            $fullname = trim(($offering->course?->title ?? 'Course').' · '.$offering->section_code);
            $remote = $this->moodle->createCourse($fullname, $shortname);

            $mapping = CourseMapping::query()->updateOrCreate(
                ['course_offering_id' => $offering->id],
                [
                    'institution_id' => $offering->institution_id,
                    'moodle_course_id' => $remote['moodle_course_id'],
                    'moodle_shortname' => $remote['shortname'],
                    'status' => 'SYNCED',
                    'last_synced_at' => Carbon::now(),
                ],
            );

            $this->completeLog($log, 'SYNCED');

            return $mapping;
        } catch (\Throwable $exception) {
            $this->failLog($log, $exception->getMessage());
            throw $exception;
        }
    }

    public function syncEnrollment(CourseEnrollment $enrollment): EnrollmentMapping
    {
        $enrollment->loadMissing(['courseOffering.course', 'student.person']);
        $courseMapping = CourseMapping::query()
            ->where('course_offering_id', $enrollment->course_offering_id)
            ->where('status', 'SYNCED')
            ->first();

        if ($courseMapping === null) {
            $courseMapping = $this->syncCourse($enrollment->courseOffering);
        }

        $existing = EnrollmentMapping::query()->where('course_enrollment_id', $enrollment->id)->first();
        if ($existing !== null && $existing->status === 'SYNCED') {
            return $existing;
        }

        $log = $this->startLog($enrollment->institution_id, 'enrollment', $enrollment->id, 'erp_to_lms');

        try {
            abort_unless($courseMapping->moodle_course_id !== null, 422, 'Moodle course mapping missing.');
            $moodleUserId = (string) ($enrollment->student?->student_number ?? $enrollment->student_id);
            $remote = $this->moodle->enrollUser((int) $courseMapping->moodle_course_id, $moodleUserId);

            $mapping = EnrollmentMapping::query()->updateOrCreate(
                ['course_enrollment_id' => $enrollment->id],
                [
                    'institution_id' => $enrollment->institution_id,
                    'moodle_enrollment_id' => $remote['moodle_enrollment_id'],
                    'status' => 'SYNCED',
                    'last_synced_at' => Carbon::now(),
                ],
            );

            $this->completeLog($log, 'SYNCED');

            return $mapping;
        } catch (\Throwable $exception) {
            $this->failLog($log, $exception->getMessage());
            throw $exception;
        }
    }

    /** @return array{offering_id: string, grades_imported: int}> */
    public function pullGrades(CourseOffering $offering): array
    {
        $mapping = CourseMapping::query()->where('course_offering_id', $offering->id)->first();
        if ($mapping === null || $mapping->moodle_course_id === null) {
            throw ValidationException::withMessages([
                'offering_id' => ['Course offering has not been synced to Moodle yet.'],
            ]);
        }

        $log = $this->startLog($offering->institution_id, 'grade', $offering->id, 'lms_to_erp');

        try {
            $grades = $this->moodle->pullGradebook((int) $mapping->moodle_course_id);
            $this->completeLog($log, 'SYNCED');

            return [
                'offering_id' => $offering->id,
                'grades_imported' => count($grades),
            ];
        } catch (\Throwable $exception) {
            $this->failLog($log, $exception->getMessage());
            throw $exception;
        }
    }

    /** @return array<string, mixed> */
    public function status(string $institutionId): array
    {
        $recent = SyncLog::query()
            ->where('institution_id', $institutionId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return [
            'enabled' => $this->moodle->isEnabled(),
            'queue_depth' => SyncLog::query()->where('institution_id', $institutionId)->where('status', 'PENDING')->count(),
            'failed_count' => SyncLog::query()->where('institution_id', $institutionId)->where('status', 'FAILED')->count(),
            'course_mappings' => CourseMapping::query()->where('institution_id', $institutionId)->count(),
            'enrollment_mappings' => EnrollmentMapping::query()->where('institution_id', $institutionId)->count(),
            'recent' => $recent,
        ];
    }

    /** @return Collection<int, SyncLog> */
    public function recentLogs(string $institutionId, int $limit = 50): Collection
    {
        return SyncLog::query()
            ->where('institution_id', $institutionId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function startLog(string $institutionId, string $type, string $entityId, string $direction): SyncLog
    {
        return SyncLog::query()->create([
            'institution_id' => $institutionId,
            'sync_type' => $type,
            'entity_id' => $entityId,
            'direction' => $direction,
            'status' => 'SYNCING',
        ]);
    }

    private function completeLog(SyncLog $log, string $status): void
    {
        $log->update([
            'status' => $status,
            'synced_at' => Carbon::now(),
            'error_message' => null,
        ]);
    }

    private function failLog(SyncLog $log, string $message): void
    {
        $log->update([
            'status' => 'FAILED',
            'error_message' => $message,
            'synced_at' => Carbon::now(),
        ]);
    }
}
