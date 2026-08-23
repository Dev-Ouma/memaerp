<?php

declare(strict_types=1);

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Student\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnrollmentController extends Controller
{
    /**
     * List all students with their current registration status.
     */
    public function students(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $students = Student::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['person', 'programme', 'campus'])
            ->get();

        return response()->json(['data' => $students]);
    }

    /**
     * Show a single student's full academic record.
     */
    public function showStudent(Request $request, string $id): JsonResponse
    {
        $student = Student::query()
            ->with([
                'person',
                'programme',
                'campus',
                'termRegistrations.term.academicYear',
                'termRegistrations.courseEnrollments.courseOffering.course',
                'termRegistrations.courseEnrollments.mark',
            ])
            ->findOrFail($id);

        return response()->json(['data' => $student]);
    }

    /**
     * List term registrations for the current user's institution.
     */
    public function termRegistrations(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $registrations = TermRegistration::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['student.person', 'term.academicYear', 'courseEnrollments.courseOffering.course'])
            ->get();

        return response()->json(['data' => $registrations]);
    }

    /**
     * List course enrollments (class rosters).
     */
    public function courseEnrollments(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $enrollments = CourseEnrollment::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with([
                'student.person',
                'courseOffering.course',
                'courseOffering.term',
                'mark',
            ])
            ->get();

        return response()->json(['data' => $enrollments]);
    }
}
