<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Iam\Models\User;
use App\Modules\Lms\Services\LmsSyncService;
use App\Modules\Lms\Services\MoodleClient;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LmsController extends Controller
{
    public function __construct(
        private readonly LmsSyncService $sync,
        private readonly MoodleClient $moodle,
    ) {}

    public function status(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'lms.sync.view');

        return response()->json(['data' => $this->sync->status((string) $user->institution_id)]);
    }

    public function syncCourse(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'lms.sync.manage');

        $validated = $request->validate(['offering_id' => ['required', 'uuid']]);
        $offering = CourseOffering::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($validated['offering_id']);

        return response()->json(['data' => $this->sync->syncCourse($offering)], 201);
    }

    public function syncEnrollment(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'lms.sync.manage');

        $validated = $request->validate(['enrollment_id' => ['required', 'uuid']]);
        $enrollment = CourseEnrollment::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($validated['enrollment_id']);

        return response()->json(['data' => $this->sync->syncEnrollment($enrollment)], 201);
    }

    public function pullGrades(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'lms.sync.manage');

        $validated = $request->validate(['offering_id' => ['required', 'uuid']]);
        $offering = CourseOffering::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($validated['offering_id']);

        return response()->json(['data' => $this->sync->pullGrades($offering)]);
    }

    public function launchUrl(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'lms.launch.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return response()->json([
            'data' => [
                'url' => $this->moodle->ssoLaunchUrl($student->student_number ?? $student->id, $request->query('path', '/')),
            ],
        ]);
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function requirePermission(User $user, string $permission): void
    {
        if ($user->scopesFor($permission) === []) {
            throw new AuthorizationException;
        }
    }
}
