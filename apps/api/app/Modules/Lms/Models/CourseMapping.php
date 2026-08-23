<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CourseMapping extends BaseModel
{
    protected $table = 'lms.course_mappings';

    protected $fillable = [
        'institution_id', 'course_offering_id', 'moodle_course_id', 'moodle_shortname', 'status', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return ['last_synced_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<CourseOffering, $this> */
    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }
}
