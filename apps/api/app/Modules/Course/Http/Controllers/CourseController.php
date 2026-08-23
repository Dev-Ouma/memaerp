<?php

declare(strict_types=1);

namespace App\Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $courses = Course::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['department.faculty', 'prerequisites.prerequisiteCourse'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $courses,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $course = Course::query()
            ->with([
                'department.faculty',
                'prerequisites.prerequisiteCourse',
                'offerings.term',
                'offerings.campus',
                'offerings.lecturer.person',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => $course,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validate([
            'department_id' => ['required', 'uuid', 'exists:'.Department::class.',id'],
            'code' => ['required', 'string', 'max:32'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'lecture_hours' => ['sometimes', 'integer', 'min:0'],
            'lab_hours' => ['sometimes', 'integer', 'min:0'],
            'tutorial_hours' => ['sometimes', 'integer', 'min:0'],
        ]);

        $course = Course::query()->create(array_merge(
            $validated,
            ['institution_id' => $user->institution_id]
        ));

        return response()->json([
            'message' => 'Course created successfully.',
            'data' => $course,
        ], 201);
    }

    public function activeOfferings(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $offerings = CourseOffering::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with([
                'course.department',
                'term.academicYear',
                'campus',
                'lecturer.person',
            ])
            ->where('is_open_for_enrollment', true)
            ->get();

        return response()->json([
            'data' => $offerings,
        ]);
    }
}
