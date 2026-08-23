<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Campus extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'institution.campuses';

    protected $fillable = ['institution_id', 'code', 'name', 'town', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['address' => 'array', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<Faculty, $this> */
    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }
}
