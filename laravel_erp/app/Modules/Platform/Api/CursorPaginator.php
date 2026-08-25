<?php

declare(strict_types=1);

namespace App\Modules\Platform\Api;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Keyset ("cursor") pagination.
 *
 * Offset pagination silently skips or repeats rows when the underlying set changes between pages, which
 * for a live application queue happens constantly. Keyset pagination is stable: the cursor names the
 * last row seen, so new arrivals cannot shift a page under the reader.
 *
 * The cursor is opaque to clients — a base64url payload of the sort value and tiebreak key. It is not a
 * security boundary; the query it is applied to is always already scoped by permission.
 */
final class CursorPaginator
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @param  array<string, string>  $sortable  public sort name => column
     * @return array{items: Collection<int, mixed>, meta: array<string, mixed>}
     */
    public static function paginate(
        Builder $query,
        Request $request,
        array $sortable = ['created_at' => 'created_at'],
        string $keyColumn = 'id',
    ): array {
        $limit = self::limit($request);
        $direction = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortName = (string) $request->query('sort', array_key_first($sortable));
        if (! array_key_exists($sortName, $sortable)) {
            throw ApiException::unprocessable(
                'INVALID_SORT',
                'Unsupported sort field.',
                ['sort' => ['Sort must be one of: '.implode(', ', array_keys($sortable)).'.']],
            );
        }
        $sortColumn = $sortable[$sortName];

        $cursor = self::decode($request->query('cursor'));
        $backwards = $cursor !== null && $cursor['d'] === 'prev';
        $effective = $backwards ? ($direction === 'asc' ? 'desc' : 'asc') : $direction;

        if ($cursor !== null) {
            $comparator = $effective === 'asc' ? '>' : '<';
            $query->where(function (Builder $inner) use ($sortColumn, $keyColumn, $comparator, $cursor): void {
                $inner->where($sortColumn, $comparator, $cursor['v'])
                    ->orWhere(function (Builder $tie) use ($sortColumn, $keyColumn, $comparator, $cursor): void {
                        $tie->where($sortColumn, '=', $cursor['v'])->where($keyColumn, $comparator, $cursor['k']);
                    });
            });
        }

        $rows = $query
            ->orderBy($sortColumn, $effective)
            ->orderBy($keyColumn, $effective)
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit)->values();

        if ($backwards) {
            $items = $items->reverse()->values();
        }

        $first = $items->first();
        $last = $items->last();

        return [
            'items' => $items,
            'meta' => [
                'pagination' => [
                    'limit' => $limit,
                    'count' => $items->count(),
                    'has_more' => $hasMore,
                    'sort' => $sortName,
                    'order' => $direction,
                    'next_cursor' => $last !== null && ($hasMore || $backwards)
                        ? self::encode(self::value($last, $sortColumn), self::value($last, $keyColumn), 'next')
                        : null,
                    'prev_cursor' => $first !== null && $cursor !== null
                        ? self::encode(self::value($first, $sortColumn), self::value($first, $keyColumn), 'prev')
                        : null,
                ],
            ],
        ];
    }

    public static function limit(Request $request): int
    {
        $limit = (int) $request->query('limit', (string) self::DEFAULT_LIMIT);

        if ($limit < 1) {
            return self::DEFAULT_LIMIT;
        }

        return min($limit, self::MAX_LIMIT);
    }

    private static function value(mixed $row, string $column): string
    {
        $value = is_array($row) ? ($row[$column] ?? null) : ($row->{$column} ?? null);

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s.u') : (string) $value;
    }

    private static function encode(string $value, string $key, string $direction): string
    {
        return rtrim(strtr(base64_encode(json_encode(
            ['v' => $value, 'k' => $key, 'd' => $direction],
            JSON_THROW_ON_ERROR,
        )), '+/', '-_'), '=');
    }

    /** @return array{v: string, k: string, d: string}|null */
    private static function decode(mixed $cursor): ?array
    {
        if (! is_string($cursor) || $cursor === '') {
            return null;
        }

        $decoded = json_decode(
            (string) base64_decode(strtr($cursor, '-_', '+/'), true),
            true,
        );

        if (! is_array($decoded) || ! isset($decoded['v'], $decoded['k'], $decoded['d'])) {
            throw ApiException::unprocessable('INVALID_CURSOR', 'The pagination cursor is not valid.', [
                'cursor' => ['Provide a cursor returned by a previous page, or omit it to start at the first page.'],
            ]);
        }

        return ['v' => (string) $decoded['v'], 'k' => (string) $decoded['k'], 'd' => $decoded['d'] === 'prev' ? 'prev' : 'next'];
    }
}
