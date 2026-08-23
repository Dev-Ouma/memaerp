<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StudentMark extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'examination.student_marks';

    protected $fillable = [
        'institution_id',
        'course_enrollment_id',
        'cat_score',
        'exam_score',
        'total_score',
        'letter_grade',
        'grade_points',
        'is_submitted',
        'submitted_by',
        'approval_status',
    ];

    protected function casts(): array
    {
        return [
            'cat_score' => 'decimal:2',
            'exam_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'grade_points' => 'decimal:2',
            'is_submitted' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<CourseEnrollment, $this> */
    public function courseEnrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
