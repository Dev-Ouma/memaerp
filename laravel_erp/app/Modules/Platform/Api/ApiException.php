<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use RuntimeException;
use Throwable;

/**
 * A domain failure that is safe to show the caller. Anything not thrown as an ApiException is treated
 * as internal and rendered as a generic 500 with no detail.
 */
class ApiException extends RuntimeException
{
    public function __construct(public readonly Problem $problem, ?Throwable $previous = null)
    {
        parent::__construct($problem->title, $problem->status, $previous);
    }

    /** @param array<string, mixed> $extensions */
    public static function make(int $status, string $code, string $title, ?string $detail = null, array $extensions = []): self
    {
        return new self(new Problem($status, $code, $title, $detail, [], $extensions));
    }

    public static function notFound(string $resource): self
    {
        return self::make(404, 'RESOURCE_NOT_FOUND', "{$resource} was not found.");
    }

    public static function forbidden(string $detail = 'You do not have permission to perform this action.'): self
    {
        return self::make(403, 'FORBIDDEN', 'Permission denied.', $detail);
    }

    public static function conflict(string $code, string $title, ?string $detail = null): self
    {
        return self::make(409, $code, $title, $detail);
    }

    /** @param array<string, list<string>> $errors */
    public static function unprocessable(string $code, string $title, array $errors = [], ?string $detail = null): self
    {
        return new self(new Problem(422, $code, $title, $detail, $errors));
    }
}
