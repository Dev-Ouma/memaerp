<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CalendarEvent extends BaseModel
{
    use Auditable;

    protected $table = 'institution.calendar_events';

    protected $fillable = ['institution_id', 'academic_year_id', 'term_id', 'event_type', 'title', 'description', 'starts_at', 'ends_at', 'is_critical', 'is_holiday'];

    protected function casts(): array
    {
        return ['starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime', 'is_critical' => 'boolean', 'is_holiday' => 'boolean'];
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<Term, $this> */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
