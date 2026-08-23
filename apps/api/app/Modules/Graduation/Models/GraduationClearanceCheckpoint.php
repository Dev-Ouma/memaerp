<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GraduationClearanceCheckpoint extends BaseModel
{
    protected $table = 'graduation.clearance_checkpoints';

    protected $fillable = [
        'institution_id', 'graduation_application_id', 'department_code', 'department_name',
        'status', 'cleared_by', 'cleared_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'cleared_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<GraduationApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(GraduationApplication::class, 'graduation_application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
