<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use Illuminate\Http\JsonResponse;

/**
 * The success envelope defined in PLAN/04-API-STANDARDS.md: payload under `data`, everything else
 * under `meta`. Keeping it in one place means every endpoint, including ones written later, is shaped
 * the same way for the frontend team.
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|list<mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    public static function data(array $data, array $meta = [], int $status = 200): JsonResponse
    {
        return new JsonResponse(
            ['data' => $data, 'meta' => self::meta($meta)],
            $status,
            ['X-Correlation-Id' => correlation_id()],
        );
    }

    /** @param array<string, mixed> $data */
    public static function created(array $data, array $meta = []): JsonResponse
    {
        return self::data($data, $meta, 201);
    }

    /** @param array<string, mixed> $data */
    public static function accepted(array $data, array $meta = []): JsonResponse
    {
        return self::data($data, $meta, 202);
    }

    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204, ['X-Correlation-Id' => correlation_id()]);
    }

    /** @param array<string, mixed> $meta */
    private static function meta(array $meta): array
    {
        return array_merge([
            'request_id' => correlation_id(),
            'timestamp' => now()->toIso8601String(),
        ], $meta);
    }
}
