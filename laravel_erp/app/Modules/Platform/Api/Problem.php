<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use Illuminate\Http\JsonResponse;

/**
 * RFC 7807 problem details, with the house extension members the frontend relies on.
 *
 * The body carries `type/title/status/detail` from the RFC plus a stable machine `code`, per-field
 * `errors`, and the `correlation_id` needed to find the matching server log. Internal exception
 * messages and stack traces never reach this object — the renderer substitutes a safe title.
 */
final class Problem
{
    public const BASE_URI = 'https://api.mema.ac.ke/problems/';

    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, mixed>  $extensions
     */
    public function __construct(
        public readonly int $status,
        public readonly string $code,
        public readonly string $title,
        public readonly ?string $detail = null,
        public readonly array $errors = [],
        public readonly array $extensions = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $body = [
            'type' => self::BASE_URI.str_replace('_', '-', strtolower($this->code)),
            'title' => $this->title,
            'status' => $this->status,
            'code' => $this->code,
        ];

        if ($this->detail !== null) {
            $body['detail'] = $this->detail;
        }

        if ($this->errors !== []) {
            $body['errors'] = $this->errors;
        }

        $body += $this->extensions;
        $body['correlation_id'] = correlation_id();
        $body['meta'] = [
            'request_id' => correlation_id(),
            'timestamp' => now()->toIso8601String(),
        ];

        return $body;
    }

    public function toResponse(): JsonResponse
    {
        return new JsonResponse(
            $this->toArray(),
            $this->status,
            ['Content-Type' => 'application/problem+json', 'X-Correlation-Id' => correlation_id()],
        );
    }
}
