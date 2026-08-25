<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Turns every exception into a problem document, and never leaks the inside of the system.
 *
 * Only exceptions the domain deliberately raised carry their message to the client. Everything else —
 * driver errors, template errors, programming mistakes — is logged with the correlation id and rendered
 * as a bare 500, so a caller can quote the id to support without the response having disclosed a table
 * name, a file path or a stack frame.
 */
final class ApiExceptionRenderer
{
    /** SQLSTATE raised by the append-only and immutability triggers. */
    private const SQLSTATE_IMMUTABLE = '42501';

    private const SQLSTATE_UNIQUE_VIOLATION = '23505';

    private const SQLSTATE_CHECK_VIOLATION = '23514';

    private const SQLSTATE_FOREIGN_KEY_VIOLATION = '23503';

    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        return $this->problemFor($e)->toResponse();
    }

    private function problemFor(Throwable $e): Problem
    {
        return match (true) {
            $e instanceof ApiException => $e->problem,

            $e instanceof ValidationException => new Problem(
                422,
                'VALIDATION_FAILED',
                'The submitted data is not valid.',
                'One or more fields need attention before this request can be accepted.',
                $e->errors(),
            ),

            $e instanceof AuthenticationException => new Problem(
                401,
                'UNAUTHENTICATED',
                'Authentication is required.',
                'Present a valid bearer token in the Authorization header.',
            ),

            $e instanceof AuthorizationException => new Problem(
                403,
                'FORBIDDEN',
                'Permission denied.',
                'Your roles do not grant this action in this scope.',
            ),

            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => new Problem(
                404,
                'RESOURCE_NOT_FOUND',
                'The requested resource was not found.',
            ),

            $e instanceof QueryException => $this->fromQueryException($e),

            $e instanceof HttpExceptionInterface => new Problem(
                $e->getStatusCode(),
                $this->codeForStatus($e->getStatusCode()),
                $this->titleForStatus($e->getStatusCode()),
                $e->getStatusCode() === 429 ? 'Too many requests. Retry after the period indicated.' : null,
            ),

            default => $this->internal($e),
        };
    }

    private function fromQueryException(QueryException $e): Problem
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');

        // Constraint violations are reported as conflicts without echoing the constraint name: the
        // index name would disclose schema internals, and the caller can act on the category alone.
        $problem = match ($sqlState) {
            self::SQLSTATE_IMMUTABLE => new Problem(
                409,
                'RECORD_IMMUTABLE',
                'This record cannot be modified.',
                'The record is append-only or has been frozen, and the database rejected the change.',
            ),
            self::SQLSTATE_UNIQUE_VIOLATION => new Problem(
                409,
                'DUPLICATE_RECORD',
                'That record already exists.',
                'A uniqueness rule prevented this write. Reload and try again.',
            ),
            self::SQLSTATE_CHECK_VIOLATION => new Problem(
                422,
                'INVARIANT_VIOLATED',
                'The request would break a data integrity rule.',
            ),
            self::SQLSTATE_FOREIGN_KEY_VIOLATION => new Problem(
                422,
                'RELATED_RECORD_MISSING',
                'A referenced record does not exist or is still in use.',
            ),
            default => null,
        };

        if ($problem === null) {
            return $this->internal($e);
        }

        Log::warning('Database constraint rejected a write.', [
            'sqlstate' => $sqlState,
            'correlation_id' => correlation_id(),
        ]);

        return $problem;
    }

    private function internal(Throwable $e): Problem
    {
        Log::error('Unhandled API exception.', [
            'correlation_id' => correlation_id(),
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile().':'.$e->getLine(),
        ]);

        return new Problem(
            500,
            'INTERNAL_ERROR',
            'The request could not be completed.',
            'An unexpected error occurred. Quote the correlation id when contacting support.',
        );
    }

    private function codeForStatus(int $status): string
    {
        return match ($status) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN',
            404 => 'RESOURCE_NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            409 => 'CONFLICT',
            413 => 'PAYLOAD_TOO_LARGE',
            415 => 'UNSUPPORTED_MEDIA_TYPE',
            429 => 'RATE_LIMITED',
            503 => 'SERVICE_UNAVAILABLE',
            default => 'REQUEST_FAILED',
        };
    }

    private function titleForStatus(int $status): string
    {
        return match ($status) {
            400 => 'The request could not be understood.',
            401 => 'Authentication is required.',
            403 => 'Permission denied.',
            404 => 'The requested resource was not found.',
            405 => 'That method is not allowed on this endpoint.',
            409 => 'The request conflicts with the current state.',
            413 => 'The upload is larger than the allowed size.',
            415 => 'That media type is not supported.',
            429 => 'Too many requests.',
            503 => 'The service is temporarily unavailable.',
            default => 'The request could not be completed.',
        };
    }
}
