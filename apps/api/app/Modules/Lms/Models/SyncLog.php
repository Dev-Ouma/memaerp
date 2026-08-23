<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SyncLog extends BaseModel
{
    protected $table = 'lms.sync_logs';

    protected $fillable = [
        'institution_id', 'sync_type', 'entity_id', 'direction', 'status', 'error_message', 'synced_at',
    ];

    protected function casts(): array
    {
        return ['synced_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
