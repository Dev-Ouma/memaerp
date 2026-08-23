<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Attendance\Services\AttendanceCheckInService;
use App\Modules\Attendance\Services\AttendanceReportService;
use App\Modules\Attendance\Services\AttendanceSessionService;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceSessionService $sessions,
        private readonly AttendanceCheckInService $checkIns,
        private readonly AttendanceReportService $reports,
    ) {}

    public function openSession(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.session.manage');

        $validated = $request->validate([
            'offering_id' => ['required', 'uuid'],
            'teaching_slot_id' => ['sometimes', 'nullable', 'uuid'],
        ]);

        $result = $this->sessions->open(
            $user,
            $validated['offering_id'],
            $validated['teaching_slot_id'] ?? null,
        );

        return response()->json([
            'data' => [
                'session' => $result['session'],
                'qr_token' => $result['qr_token'],
                'qr_payload' => $this->qrPayload($result['session']->id, $result['qr_token']),
            ],
        ], 201);
    }

    public function sessionQr(Request $request, string $sessionId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.session.manage');

        $session = AttendanceSession::query()
            ->where('institution_id', $user->institution_id)
            ->where('lecturer_id', $user->id)
            ->findOrFail($sessionId);

        abort_unless($session->isOpen(), 410, 'Session QR is no longer active.');

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'expires_at' => $session->expires_at,
                'message' => 'QR token was issued when the session opened. Re-open if expired.',
            ],
        ]);
    }

    public function closeSession(Request $request, string $sessionId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.session.manage');

        $session = $this->sessions->close($user, $sessionId);

        return response()->json(['data' => $session]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.checkin.self');

        $validated = $request->validate(['token' => ['required', 'string', 'min:16']]);
        $log = $this->checkIns->checkIn($user, $validated['token']);

        return response()->json(['data' => $log], 201);
    }

    public function myRecord(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.record.view-self');

        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return response()->json(['data' => $this->reports->studentRecord($student)]);
    }

    public function courseReport(Request $request, string $offeringId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.report.view');

        $offering = CourseOffering::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($offeringId);

        return response()->json(['data' => $this->reports->courseReport($offering)]);
    }

    public function atRisk(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.report.view');

        return response()->json([
            'data' => $this->reports->atRiskAlerts((string) $user->institution_id),
        ]);
    }

    public function activeSessions(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attendance.session.manage');

        $sessions = AttendanceSession::query()
            ->where('institution_id', $user->institution_id)
            ->where('lecturer_id', $user->id)
            ->where('status', 'OPEN')
            ->with(['courseOffering.course'])
            ->orderByDesc('opened_at')
            ->get();

        return response()->json(['data' => $sessions]);
    }

    /** @return array{token: string, session_id: string}> */
    private function qrPayload(string $sessionId, string $token): array
    {
        return ['token' => $token, 'session_id' => $sessionId];
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
