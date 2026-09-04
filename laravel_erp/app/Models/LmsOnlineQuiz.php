<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['quiz_title', 'course_code', 'weight', 'duration_minutes', 'completed_attempts', 'avg_score', 'proctoring_mode', 'status'])]
final class LmsOnlineQuiz extends Model
{
    protected $table = 'lms_online_quizzes';
}
