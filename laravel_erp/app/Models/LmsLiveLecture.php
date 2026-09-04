<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['session_title', 'course_code', 'instructor', 'platform', 'scheduled_time', 'attendance_mode', 'recording_status', 'session_status'])]
final class LmsLiveLecture extends Model
{
    protected $table = 'lms_live_lectures';
}
