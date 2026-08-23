<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Http\Requests\ListStudentsRequest;
use App\Modules\Student\Http\Requests\UpdateStudentRequest;
use App\Modules\Student\Http\Resources\StudentResource;
use App\Modules\Student\Services\StudentRecords;
use App\Platform\Support\RequestContext;
use Illuminate\Http\JsonResponse;

final class StudentController extends Controller
{
    public function __construct(
        private readonly StudentRecords $records,
        private readonly RequestContext $context,
    ) {}

    public function index(ListStudentsRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();
        $paginator = $this->records->paginate(
            $actor,
            $validated['filter'] ?? [],
            $validated['sort'] ?? 'student_number',
            $validated['per_page'] ?? 25,
        );

        return response()->json([
            'data' => StudentResource::collection($paginator->items())->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
                'pagination' => [
                    'next_cursor' => $paginator->nextCursor()?->encode(),
                    'per_page' => $paginator->perPage(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }

    public function show(ListStudentsRequest $request, string $student): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $record = $this->records->findVisible($actor, $student);

        return response()->json([
            'data' => (new StudentResource($record))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function update(UpdateStudentRequest $request, string $student): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();
        $reason = $validated['change_reason'];
        unset($validated['change_reason']);

        $record = $this->records->update($actor, $student, $validated, $reason);

        return response()->json([
            'data' => (new StudentResource($record))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
