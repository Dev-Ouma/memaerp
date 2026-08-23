<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Room extends BaseModel
{
    use SoftDeletes;

    protected $table = 'course.rooms';

    protected $fillable = [
        'institution_id', 'campus_id', 'code', 'name', 'capacity', 'room_type', 'accessibility', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'accessibility' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Campus, $this> */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /** @return HasMany<TeachingSlot, $this> */
    public function teachingSlots(): HasMany
    {
        return $this->hasMany(TeachingSlot::class);
    }
}
