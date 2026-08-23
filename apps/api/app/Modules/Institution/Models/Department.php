<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Department extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'institution.departments';

    protected $fillable = ['institution_id', 'faculty_id', 'code', 'name', 'cost_centre', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Faculty, $this> */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
