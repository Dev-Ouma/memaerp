<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApplicationReview extends BaseModel
{
    use Auditable;

    protected $table = 'admission.application_reviews';

    protected $fillable = [
        'institution_id', 'application_id', 'stage', 'sequence', 'status',
        'reviewed_by', 'reference', 'comments', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
