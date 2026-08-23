<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Services;

use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Curriculum\Models\CurriculumVersion;
use Illuminate\Http\Exceptions\HttpResponseException;

final class PrerequisiteGraphService
{
    public function add(
        CurriculumVersion $version,
        string $courseId,
        string $requiredCourseId,
        string $type,
    ): CoursePrerequisite {
        $this->ensureMutable($version);

        $mapped = $version->curriculumCourses()->whereIn('course_id', [$courseId, $requiredCourseId])->distinct()->count('course_id');
        if ($mapped !== 2 || $courseId === $requiredCourseId) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CUR-001', 'message' => 'Both distinct courses must belong to this curriculum version.'],
            ], 422));
        }

        if ($type === 'PREREQUISITE' && $this->pathExists($version->id, $requiredCourseId, $courseId)) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CUR-CYCLE', 'message' => 'This prerequisite would create a cyclic dependency.'],
            ], 400));
        }

        return CoursePrerequisite::query()->create([
            'institution_id' => $version->institution_id,
            'curriculum_version_id' => $version->id,
            'course_id' => $courseId,
            'prerequisite_course_id' => $requiredCourseId,
            'requirement_type' => $type,
        ]);
    }

    private function pathExists(string $versionId, string $from, string $target): bool
    {
        $edges = CoursePrerequisite::query()
            ->where('curriculum_version_id', $versionId)
            ->where('requirement_type', 'PREREQUISITE')
            ->get(['course_id', 'prerequisite_course_id'])
            ->groupBy('course_id');
        $stack = [$from];
        $visited = [];

        while ($stack !== []) {
            $node = array_pop($stack);
            if ($node === $target) {
                return true;
            }
            if (isset($visited[$node])) {
                continue;
            }
            $visited[$node] = true;
            foreach ($edges->get($node, collect()) as $edge) {
                $stack[] = (string) $edge->prerequisite_course_id;
            }
        }

        return false;
    }

    private function ensureMutable(CurriculumVersion $version): void
    {
        if ($version->isLocked()) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CUR-002', 'message' => 'Approved curriculum versions are read-only. Create a new version.'],
            ], 409));
        }
    }
}
