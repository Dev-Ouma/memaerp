<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CourseReview extends BaseModel
{
    use Auditable;

    protected $table = 'course.course_reviews';

    protected $fillable = [
        'institution_id', 'course_id', 'stage', 'sequence', 'status',
        'reviewed_by', 'reference', 'comments', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
