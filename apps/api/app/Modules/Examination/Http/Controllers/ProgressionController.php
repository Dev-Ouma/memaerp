<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Examination\Services\GpaCalculationService;
use App\Modules\Examination\Services\ProgressionService;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProgressionController extends Controller
{
    public function __construct(
        private readonly GpaCalculationService $gpa,
        private readonly ProgressionService $progression,
    ) {}

    public function calculateBatch(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.approve');

        $validated = $request->validate(['term_id' => ['required', 'uuid']]);
        $records = $this->gpa->calculateBatch((string) $user->institution_id, $validated['term_id']);

        return response()->json(['data' => $records]);
    }

    public function publishResults(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.publish');

        $validated = $request->validate(['term_id' => ['required', 'uuid']]);
        $records = $this->progression->publishResults((string) $user->institution_id, $validated['term_id']);

        return response()->json(['message' => 'Results published.', 'data' => $records]);
    }

    public function myResults(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return response()->json(['data' => $this->progression->myResults($student)]);
    }

    public function resultSlip(Request $request, string $termId): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
        $term = Term::query()->where('institution_id', $user->institution_id)->findOrFail($termId);

        return $this->progression->resultSlip($student, $term);
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
