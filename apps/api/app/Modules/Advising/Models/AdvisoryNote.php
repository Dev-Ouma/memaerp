<?php

declare(strict_types=1);

namespace App\Modules\Advising\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdvisoryNote extends BaseModel
{
    protected $table = 'advising.advisory_notes';

    protected $fillable = [
        'institution_id', 'student_id', 'advisor_user_id', 'note_type', 'note_text',
        'is_confidential', 'visible_to_student', 'follow_up_status', 'follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'is_confidential' => 'boolean',
            'visible_to_student' => 'boolean',
            'follow_up_at' => 'immutable_datetime',
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
