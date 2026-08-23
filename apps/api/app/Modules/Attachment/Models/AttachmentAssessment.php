<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Models;

use App\Modules\Iam\Models\User;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AttachmentAssessment extends BaseModel
{
    public const TYPE_HOST = 'HOST_EVAL';

    public const TYPE_UNIVERSITY = 'UNIVERSITY_EVAL';

    public const TYPE_FINAL_REPORT = 'FINAL_REPORT';

    protected $table = 'attachment.attachment_assessments';

    protected $fillable = [
        'placement_id', 'assessment_type', 'score', 'max_score', 'comments',
        'assessed_by', 'assessor_name', 'assessed_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'max_score' => 'float',
            'assessed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<AttachmentPlacement, $this> */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(AttachmentPlacement::class, 'placement_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
