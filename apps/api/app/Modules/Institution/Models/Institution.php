<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The tenant root. Today there is exactly one row (Mema University); the column exists on every
 * other table from day one so that becoming multi-tenant is a routing change, not a migration of
 * every table in the system.
 */
final class Institution extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'institution.institutions';

    protected $fillable = [
        'code', 'name', 'legal_name', 'registration_number', 'domain',
        'branding', 'contact', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'contact' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Campus, $this> */
    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    /** @return HasMany<Faculty, $this> */
    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    /** @return HasMany<AcademicYear, $this> */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }
}
