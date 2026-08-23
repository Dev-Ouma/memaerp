<?php

declare(strict_types=1);

namespace App\Platform\Http;

use App\Platform\Support\RequestContext;
use Illuminate\Http\JsonResponse;

final class ApiErrorResponse
{
    /**
     * @param  array<string, list<string>>  $fields
     * @param  array<string, string>  $headers
     */
    public static function make(
        string $code,
        string $message,
        int $status,
        array $fields = [],
        array $headers = [],
    ): JsonResponse {
        $context = app(RequestContext::class);

        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'fields' => $fields,
                'trace_id' => $context->correlationId(),
            ],
            'meta' => [
                'request_id' => $context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ], $status, $headers);
    }
}
