<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Prospect extends BaseModel
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'admission.prospects';

    protected $fillable = [
        'institution_id', 'full_name', 'email', 'phone', 'source', 'campaign_code',
        'programme_interest_id', 'status', 'notes', 'converted_at',
    ];

    protected function casts(): array
    {
        return ['converted_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Programme, $this> */
    public function programmeInterest(): BelongsTo
    {
        return $this->belongsTo(Programme::class, 'programme_interest_id');
    }
}
