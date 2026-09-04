<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['unit_code', 'unit_title', 'moodle_course_id', 'enrolled_students', 'instructor_assigned', 'sync_status', 'synced_at'])]
final class MoodleSyncLog extends Model
{
    protected function casts(): array
    {
        return ['synced_at' => 'datetime'];
    }
}
