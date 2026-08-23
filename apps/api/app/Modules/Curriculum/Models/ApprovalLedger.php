<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApprovalLedger extends BaseModel
{
    public const UPDATED_AT = null;

    protected $table = 'curriculum.approval_ledger';

    protected $fillable = ['institution_id', 'curriculum_version_id', 'previous_hash', 'entry_hash', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class, 'curriculum_version_id');
    }
}
