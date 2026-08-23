<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use App\Modules\Portal\Services\PortalDashboardService;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PortalController extends Controller
{
    public function __construct(private readonly PortalDashboardService $portal) {}

    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json(['data' => $this->portal->dashboard($student)]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $term = \App\Modules\Institution\Models\Term::query()->where('institution_id', $student->institution_id)->current()->first();
        $clearance = app(\App\Modules\Finance\Services\ClearanceService::class)->forStudent($student, $term?->id);

        return response()->json(['data' => $this->portal->alerts($student, $term, $clearance)]);
    }

    public function documents(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json(['data' => $this->portal->documents($student)]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $prefs = $this->portal->preferences((string) $user->institution_id, (string) $user->person_id);

        return response()->json(['data' => $prefs]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $validated = $request->validate([
            'locale' => ['sometimes', 'string', 'max:16'],
            'theme' => ['sometimes', 'string', 'in:light,dark,system'],
            'email_alerts' => ['sometimes', 'boolean'],
            'sms_alerts' => ['sometimes', 'boolean'],
            'dashboard_widgets' => ['sometimes', 'array'],
        ]);

        $prefs = $this->portal->preferences((string) $user->institution_id, (string) $user->person_id);
        $prefs->fill($validated)->save();

        return response()->json(['data' => $prefs]);
    }

    private function student(Request $request): Student
    {
        $user = $this->actor($request);
        if ($user->scopesFor('student.record.view') === []) {
            throw new AuthorizationException;
        }

        return Student::query()->where('person_id', $user->person_id)->with(['person', 'programme'])->firstOrFail();
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
