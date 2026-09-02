<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'description', 'source', 'columns', 'filters', 'sorting', 'grouping', 'options', 'is_draft'])]
final class PersonalReport extends Model
{
    protected function casts(): array
    {
        return [
            'columns' => 'json',
            'filters' => 'json',
            'sorting' => 'json',
            'grouping' => 'json',
            'options' => 'json',
            'is_draft' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class);
    }
}
