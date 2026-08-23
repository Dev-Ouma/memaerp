<?php

declare(strict_types=1);

namespace App\Platform\Concerns;

use App\Platform\Support\Uuid7;

/**
 * Assigns a time-ordered UUID on create. Every table in this system uses UUID primary keys —
 * never auto-increment integers, which leak record counts and make cross-environment data
 * movement (migration, anonymised staging refresh) error-prone.
 */
trait HasUuid7
{
    public static function bootHasUuid7(): void
    {
        static::creating(function ($model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Uuid7::generate();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
