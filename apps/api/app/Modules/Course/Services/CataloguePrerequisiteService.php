<?php

declare(strict_types=1);

namespace App\Modules\Course\Services;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CoursePrerequisite;
use Illuminate\Http\Exceptions\HttpResponseException;

final class CataloguePrerequisiteService
{
    public function add(Course $course, string $requiredCourseId, string $type): CoursePrerequisite
    {
        if ($course->id === $requiredCourseId) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CRS-002', 'message' => 'A course cannot require itself.'],
            ], 422));
        }

        $required = Course::query()
            ->where('institution_id', $course->institution_id)
            ->whereKey($requiredCourseId)
            ->first();
        if (! $required instanceof Course) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CRS-002', 'message' => 'The required course must belong to this institution.'],
            ], 422));
        }

        if ($type === 'PREREQUISITE' && $this->pathExists((string) $course->institution_id, $requiredCourseId, (string) $course->id)) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CUR-CYCLE', 'message' => 'This prerequisite would create a cyclic dependency.'],
            ], 400));
        }

        return CoursePrerequisite::query()->create([
            'institution_id' => $course->institution_id,
            'course_id' => $course->id,
            'prerequisite_course_id' => $requiredCourseId,
            'requirement_type' => $type,
        ]);
    }

    private function pathExists(string $institutionId, string $from, string $target): bool
    {
        $edges = CoursePrerequisite::query()
            ->where('institution_id', $institutionId)
            ->whereNull('curriculum_version_id')
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
}
