<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['personal_report_id', 'frequency', 'delivery_email', 'format', 'is_active'])]
final class ReportSchedule extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function personalReport(): BelongsTo
    {
        return $this->belongsTo(PersonalReport::class);
    }
}
