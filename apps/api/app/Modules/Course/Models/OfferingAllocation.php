<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OfferingAllocation extends BaseModel
{
    use Auditable;

    protected $table = 'course.offering_allocations';

    protected $fillable = [
        'institution_id', 'course_offering_id', 'lecturer_id', 'role', 'workload_credits',
    ];

    protected function casts(): array
    {
        return ['workload_credits' => 'integer'];
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

    /** @return BelongsTo<User, $this> */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }
}
