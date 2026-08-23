<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Modules\Iam\Models\User;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReviewStep extends BaseModel
{
    use Auditable;

    protected $table = 'curriculum.review_steps';

    protected $fillable = ['institution_id', 'curriculum_version_id', 'stage', 'sequence', 'status', 'reviewed_by', 'reference', 'comments', 'reviewed_at'];

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'reviewed_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class, 'curriculum_version_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
