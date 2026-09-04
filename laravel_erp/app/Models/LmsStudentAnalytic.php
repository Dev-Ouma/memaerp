<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['student_name', 'reg_no', 'programme', 'engagement_score', 'total_logins_trimester', 'video_watch_rate', 'cat_completion_rate', 'risk_status'])]
final class LmsStudentAnalytic extends Model
{
    protected $table = 'lms_student_analytics';
}
