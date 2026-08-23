<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OfferingWaitlist extends BaseModel
{
    use Auditable;

    protected $table = 'course.offering_waitlist';

    protected $fillable = [
        'institution_id', 'course_offering_id', 'student_id', 'position', 'status',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<CourseOffering, $this> */
    public function offering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }
}
