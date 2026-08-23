<?php

declare(strict_types=1);

namespace App\Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Course\Models\Room;
use App\Modules\Course\Models\TeachingSlot;
use App\Modules\Course\Services\TimetableService;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TimetableController extends Controller
{
    public function __construct(private readonly TimetableService $timetable) {}

    public function mySchedule(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return response()->json([
            'data' => $this->timetable->mySchedule($student, $request->query('term_id')),
        ]);
    }

    public function exportIcs(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
        $ics = $this->timetable->exportIcs($student, $request->query('term_id'));

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="my-timetable.ics"',
        ]);
    }

    public function clashCheck(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.manage');

        $validated = $request->validate([
            'room_id' => ['required', 'uuid'],
            'lecturer_id' => ['sometimes', 'nullable', 'uuid'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        return response()->json([
            'data' => $this->timetable->clashCheck([
                ...$validated,
                'institution_id' => $user->institution_id,
            ]),
        ]);
    }

    public function storeSlot(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.manage');

        $validated = $request->validate([
            'course_offering_id' => ['required', 'uuid'],
            'room_id' => ['required', 'uuid'],
            'lecturer_id' => ['sometimes', 'nullable', 'uuid'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $slot = $this->timetable->createSlot([
            ...$validated,
            'institution_id' => $user->institution_id,
            'status' => 'ACTIVE',
        ]);

        return response()->json(['data' => $slot->load(['courseOffering.course', 'room'])], 201);
    }

    public function rooms(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');

        $rooms = Room::query()
            ->where('institution_id', $user->institution_id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $rooms]);
    }

    public function offeringSlots(Request $request, string $offeringId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');

        $slots = TeachingSlot::query()
            ->where('institution_id', $user->institution_id)
            ->where('course_offering_id', $offeringId)
            ->with(['room', 'lecturer'])
            ->orderBy('starts_at')
            ->get();

        return response()->json(['data' => $slots]);
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
