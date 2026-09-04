<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'course_id', 'staff_no', 'phone', 'designation', 'department', 'employment_type', 'rank', 'qualification', 'employment_status', 'joined_at'])]
final class Staff extends Model
{
    protected $table = 'staff';

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(StaffLeaveRequest::class);
    }
}
