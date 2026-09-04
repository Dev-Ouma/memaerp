<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['staff_id', 'leave_date', 'end_date', 'message', 'status', 'leave_type', 'days', 'reliever'])]
final class StaffLeaveRequest extends Model
{
    protected $table = 'staff_leave_requests';

    protected function casts(): array
    {
        return [
            'leave_date' => 'date',
            'end_date' => 'date',
            'days' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
