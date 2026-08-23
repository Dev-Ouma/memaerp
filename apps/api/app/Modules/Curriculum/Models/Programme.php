<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Programme extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'curriculum.programmes';

    protected $fillable = [
        'institution_id',
        'department_id',
        'code',
        'name',
        'award_level',
        'duration_years',
        'total_credits_required',
        'is_active',
        'status',
        'qualification_framework_code',
        'accreditation_body',
        'accreditation_reference',
        'accreditation_expires_on',
        'minimum_residency_credits',
    ];

    protected function casts(): array
    {
        return [
            'duration_years' => 'integer',
            'total_credits_required' => 'integer',
            'is_active' => 'boolean',
            'accreditation_expires_on' => 'date',
            'minimum_residency_credits' => 'integer',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<CurriculumVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(CurriculumVersion::class);
    }
}
