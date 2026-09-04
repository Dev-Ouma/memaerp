<?php

declare(strict_types=1);

namespace App\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Stats bag that returns 0 for missing keys so DB-backed screens
 * with empty domain tables do not throw Undefined array key errors.
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 */
final class SoftStatsBag implements ArrayAccess, Countable, IteratorAggregate
{
    /** @param array<string, mixed> $data */
    public function __construct(private array $data = []) {}

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? 0;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
