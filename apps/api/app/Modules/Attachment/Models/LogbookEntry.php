<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LogbookEntry extends BaseModel
{
    protected $table = 'attachment.logbook_entries';

    protected $fillable = [
        'placement_id', 'week_number', 'week_start', 'activities_summary',
        'skills_learned', 'hours_logged', 'status', 'submitted_at',
        'endorsed_at', 'host_comment',
    ];

    protected function casts(): array
    {
        return [
            'week_number' => 'integer',
            'week_start' => 'date',
            'hours_logged' => 'float',
            'submitted_at' => 'immutable_datetime',
            'endorsed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<AttachmentPlacement, $this> */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(AttachmentPlacement::class, 'placement_id');
    }
}
