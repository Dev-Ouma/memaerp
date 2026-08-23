<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class HostOrganization extends BaseModel
{
    protected $table = 'attachment.host_organizations';

    protected $fillable = [
        'institution_id', 'name', 'industry', 'contact_name', 'contact_email',
        'contact_phone', 'address', 'capacity_per_intake', 'mou_valid_until',
        'is_active', 'quality_rating', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity_per_intake' => 'integer',
            'mou_valid_until' => 'date',
            'is_active' => 'boolean',
            'quality_rating' => 'float',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<AttachmentPlacement, $this> */
    public function placements(): HasMany
    {
        return $this->hasMany(AttachmentPlacement::class, 'host_organization_id');
    }
}
