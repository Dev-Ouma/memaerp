<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProgrammeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $programmes = Programme::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['department.faculty', 'versions.effectiveYear'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $programmes,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $programme = Programme::query()
            ->with([
                'department.faculty',
                'versions.curriculumCourses.course',
                'versions.effectiveYear',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => $programme,
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
            'name' => ['required', 'string', 'max:200'],
            'award_level' => ['required', 'string', 'in:CERTIFICATE,DIPLOMA,BACHELORS,MASTERS,DOCTORATE'],
            'duration_years' => ['sometimes', 'integer', 'min:1', 'max:7'],
            'total_credits_required' => ['required', 'integer', 'min:30'],
        ]);

        $programme = Programme::query()->create(array_merge(
            $validated,
            ['institution_id' => $user->institution_id]
        ));

        return response()->json([
            'message' => 'Programme created successfully.',
            'data' => $programme,
        ], 201);
    }
}
