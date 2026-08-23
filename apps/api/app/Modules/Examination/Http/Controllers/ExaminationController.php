<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Examination\Models\TermGpa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExaminationController extends Controller
{
    /**
     * Get marks sheet for a given course offering.
     */
    public function marksSheet(Request $request, string $offeringId): JsonResponse
    {
        $marks = StudentMark::query()
            ->whereHas('courseEnrollment', fn ($q) => $q->where('course_offering_id', $offeringId))
            ->with([
                'courseEnrollment.student.person',
                'courseEnrollment.courseOffering.course',
                'submitter',
            ])
            ->get();

        return response()->json(['data' => $marks]);
    }

    /**
     * Get all term GPAs.
     */
    public function termGpas(Request $request): JsonResponse
    {
        $institutionId = $request->user()?->institution_id;

        $gpas = TermGpa::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['student.person', 'student.programme', 'term.academicYear'])
            ->get();

        return response()->json(['data' => $gpas]);
    }
}
