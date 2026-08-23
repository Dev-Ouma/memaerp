<?php

declare(strict_types=1);

namespace App\Modules\Advising\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdvisorAssignment extends BaseModel
{
    protected $table = 'advising.advisor_assignments';

    protected $fillable = [
        'institution_id', 'advisor_user_id', 'student_id', 'assigned_at',
        'is_active', 'assigned_by', 'assignment_reason',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'immutable_datetime',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<User, $this> */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_user_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
