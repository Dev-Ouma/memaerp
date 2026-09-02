<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['session_code', 'session_title', 'start_date', 'end_date', 'daily_slots', 'moderation_deadline', 'status'])]
final class ExamSession extends Model
{
    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'moderation_deadline' => 'date'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }
}
