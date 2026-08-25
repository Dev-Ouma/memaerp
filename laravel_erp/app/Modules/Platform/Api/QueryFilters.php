<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

/**
 * Filtering is allow-listed, and an unknown filter is an error rather than a silent no-op.
 *
 * Silently ignoring `?status=ADMITED` would hand the caller a full unfiltered list while they believe
 * they are looking at admitted applicants — a correctness and a disclosure problem. A 422 makes the
 * typo visible immediately.
 */
final class QueryFilters
{
    /** Reserved by the pagination, sorting and shaping conventions rather than by any resource. */
    public const RESERVED = ['cursor', 'limit', 'sort', 'order', 'include', 'fields', 'q', 'page'];

    /**
     * @param  array<string, callable(Builder, string): void|string>  $allowed  filter name => column or handler
     */
    public static function apply(Builder $query, Request $request, array $allowed): void
    {
        $unknown = array_values(array_diff(
            array_keys($request->query()),
            array_merge(self::RESERVED, array_keys($allowed)),
        ));

        if ($unknown !== []) {
            throw ApiException::unprocessable(
                'UNKNOWN_FILTER',
                'Unsupported query parameter.',
                ['query' => array_map(static fn (string $key): string => "`{$key}` is not a supported filter.", $unknown)],
                'Supported filters: '.(($allowed === []) ? 'none' : implode(', ', array_keys($allowed))).'.',
            );
        }

        foreach ($allowed as $name => $handler) {
            $value = $request->query($name);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_callable($handler)) {
                $handler($query, is_array($value) ? implode(',', $value) : (string) $value);

                continue;
            }

            is_array($value)
                ? $query->whereIn($handler, $value)
                : $query->where($handler, $value);
        }
    }

    /** Splits `A,B,C` into a list, for `IN` style filters. */
    public static function list(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn ($v): bool => $v !== ''));
    }
}
