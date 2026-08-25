<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Modules\Admission\Setups\SetupManager;
use App\Modules\Platform\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminSetupApiController
{
    public function index(Request $request): JsonResponse
    {
        $rows = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')->limit(1)])
            ->when($request->query('q'), fn ($q, $value) => $q->where('name', 'ilike', "%{$value}%"))
            ->when($request->query('category'), fn ($q, $value) => $q->where('category', $value))
            ->orderBy('category')->orderBy('name')->paginate(min(100, max(1, (int) $request->query('limit', 25))));

        return ApiResponse::data($rows->map(fn ($setup) => $this->serialize($setup))->all(), ['pagination' => ['page' => $rows->currentPage(), 'pages' => $rows->lastPage(), 'total' => $rows->total()]]);
    }

    public function show(AdminSetupDefinition $setup): JsonResponse
    {
        return ApiResponse::data($this->serialize($setup->load('versions')));
    }

    public function store(Request $request, AdminSetupDefinition $setup, SetupManager $manager): JsonResponse
    {
        $data = $request->validate(['configuration' => ['required', 'array'], 'change_reason' => ['required', 'string', 'max:500']]);
        $version = $manager->draft($setup, $data['configuration'], $data['change_reason'], $request->user()->id);

        return ApiResponse::created(['id' => $version->id, 'setup_key' => $setup->setup_key, 'version' => $version->version, 'status' => $version->status, 'checksum' => $version->checksum]);
    }

    public function publish(Request $request, AdminSetupVersion $version, SetupManager $manager): JsonResponse
    {
        $data = $request->validate(['effective_from' => ['required', 'date'], 'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from']]);
        $version = $manager->publish($version, $data['effective_from'], $data['effective_to'] ?? null, $request->user()->id);

        return ApiResponse::data(['id' => $version->id, 'version' => $version->version, 'status' => $version->status, 'effective_from' => $version->effective_from->toDateString(), 'effective_to' => $version->effective_to?->toDateString()]);
    }

    private function serialize(AdminSetupDefinition $setup): array
    {
        return ['id' => $setup->id, 'key' => $setup->setup_key, 'category' => $setup->category, 'name' => $setup->name,
            'consumer' => $setup->consumer, 'missing_behaviour' => $setup->missing_behaviour,
            'versions' => $setup->relationLoaded('versions') ? $setup->versions->map(fn ($v) => ['id' => $v->id, 'version' => $v->version, 'status' => $v->status,
                'effective_from' => $v->effective_from?->toDateString(), 'effective_to' => $v->effective_to?->toDateString(), 'checksum' => $v->checksum])->all() : []];
    }
}
