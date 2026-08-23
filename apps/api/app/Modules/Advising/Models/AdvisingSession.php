<?php

declare(strict_types=1);

namespace App\Modules\Advising\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdvisingSession extends BaseModel
{
    protected $table = 'advising.advising_sessions';

    protected $fillable = [
        'institution_id', 'student_id', 'advisor_user_id', 'scheduled_at',
        'status', 'mode', 'topic', 'outcome',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_user_id');
    }
}
