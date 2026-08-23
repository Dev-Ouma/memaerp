<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Course\Models\TeachingSlot;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AttendanceSession extends BaseModel
{
    protected $table = 'attendance.sessions';

    protected $fillable = [
        'institution_id', 'course_offering_id', 'teaching_slot_id', 'lecturer_id',
        'session_date', 'status', 'qr_token_hash', 'expires_at', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'expires_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
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

    /** @return BelongsTo<TeachingSlot, $this> */
    public function teachingSlot(): BelongsTo
    {
        return $this->belongsTo(TeachingSlot::class);
    }

    /** @return BelongsTo<User, $this> */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /** @return HasMany<SessionLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(SessionLog::class, 'session_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN' && $this->expires_at->isFuture();
    }
}
