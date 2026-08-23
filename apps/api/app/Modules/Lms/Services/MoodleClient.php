<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin Moodle Web Services client. When disabled, returns deterministic stub IDs for local/dev sync.
 */
final class MoodleClient
{
    public function isEnabled(): bool
    {
        return (bool) config('lms.enabled') && config('lms.base_url') !== null && config('lms.token') !== null;
    }

    /** @return array{moodle_course_id: int, shortname: string}> */
    public function createCourse(string $fullname, string $shortname, string $categoryId = '1'): array
    {
        if (! $this->isEnabled()) {
            return [
                'moodle_course_id' => abs(crc32($shortname)),
                'shortname' => $shortname,
            ];
        }

        $response = Http::asForm()
            ->timeout((int) config('lms.timeout', 15))
            ->post($this->endpoint(), [
                'wstoken' => config('lms.token'),
                'wsfunction' => 'core_course_create_courses',
                'moodlewsrestformat' => 'json',
                'courses' => [[
                    'fullname' => $fullname,
                    'shortname' => $shortname,
                    'categoryid' => $categoryId,
                ]],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Moodle course creation failed: '.$response->body());
        }

        $payload = $response->json();
        $course = is_array($payload) ? ($payload[0] ?? null) : null;
        if (! is_array($course) || ! isset($course['id'])) {
            throw new RuntimeException('Moodle returned an unexpected course payload.');
        }

        return [
            'moodle_course_id' => (int) $course['id'],
            'shortname' => $shortname,
        ];
    }

    /** @return array{moodle_enrollment_id: int}> */
    public function enrollUser(int $moodleCourseId, string $moodleUserId, int $roleId = 5): array
    {
        if (! $this->isEnabled()) {
            return ['moodle_enrollment_id' => abs(crc32($moodleCourseId.':'.$moodleUserId))];
        }

        $response = Http::asForm()
            ->timeout((int) config('lms.timeout', 15))
            ->post($this->endpoint(), [
                'wstoken' => config('lms.token'),
                'wsfunction' => 'enrol_manual_enrol_users',
                'moodlewsrestformat' => 'json',
                'enrolments' => [[
                    'roleid' => $roleId,
                    'userid' => (int) $moodleUserId,
                    'courseid' => $moodleCourseId,
                ]],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Moodle enrollment failed: '.$response->body());
        }

        return ['moodle_enrollment_id' => abs(crc32($moodleCourseId.':'.$moodleUserId))];
    }

    /** @return list<array{userid: int, grade: float, itemname: string}>> */
    public function pullGradebook(int $moodleCourseId): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $response = Http::asForm()
            ->timeout((int) config('lms.timeout', 15))
            ->post($this->endpoint(), [
                'wstoken' => config('lms.token'),
                'wsfunction' => 'gradereport_user_get_grade_items',
                'moodlewsrestformat' => 'json',
                'courseid' => $moodleCourseId,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Moodle grade pull failed: '.$response->body());
        }

        $items = $response->json('usergrades') ?? [];

        return is_array($items) ? $items : [];
    }

    public function ssoLaunchUrl(string $moodleUserId, string $targetPath = '/'): string
    {
        $base = rtrim((string) config('lms.base_url'), '/');

        return $base.'/login/index.php?wantsurl='.urlencode($targetPath).'&username='.urlencode($moodleUserId);
    }

    private function endpoint(): string
    {
        return rtrim((string) config('lms.base_url'), '/').'/webservice/rest/server.php';
    }
}
