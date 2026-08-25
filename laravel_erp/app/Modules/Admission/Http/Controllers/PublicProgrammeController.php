<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Models\ProgrammeOffering;
use App\Modules\Platform\Api\ApiResponse;
use App\Modules\Platform\Api\CursorPaginator;
use App\Modules\Platform\Api\QueryFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicProgrammeController
{
    public function index(Request $request): JsonResponse
    {
        $query = ProgrammeOffering::query()->with(['course', 'intake'])
            ->where('is_published', true)->whereHas('intake', fn ($q) => $q->where('is_published', true));
        QueryFilters::apply($query, $request, [
            'campus' => 'campus',
            'study_mode' => 'study_mode',
            'intake' => fn ($q, $value) => $q->whereHas('intake', fn ($i) => $i->where('code', $value)),
            'q' => fn ($q, $value) => $q->whereHas('course', fn ($c) => $c->where('name', 'ilike', "%{$value}%")->orWhere('code', 'ilike', "%{$value}%")),
        ]);
        $page = CursorPaginator::paginate($query, $request, ['created_at' => 'created_at'], 'id');

        return ApiResponse::data($page['items']->map(fn ($offering) => $this->serialize($offering))->all(), $page['meta']);
    }

    public function show(ProgrammeOffering $offering): JsonResponse
    {
        abort_unless($offering->is_published && $offering->intake()->where('is_published', true)->exists(), 404);

        return ApiResponse::data($this->serialize($offering->load(['course', 'intake'])));
    }

    private function serialize(ProgrammeOffering $offering): array
    {
        return ['id' => (string) $offering->id, 'title' => $offering->course->name, 'code' => $offering->course->code,
            'campus' => $offering->campus, 'study_mode' => $offering->study_mode, 'capacity' => $offering->capacity,
            'application_fee' => ['amount' => $offering->application_fee, 'currency' => 'KES'],
            'requirements' => $offering->requirements, 'intake' => ['code' => $offering->intake->code, 'name' => $offering->intake->name,
                'opens_at' => $offering->intake->opens_at?->toDateString(), 'closes_at' => $offering->intake->closes_at?->toDateString()]];
    }
}
