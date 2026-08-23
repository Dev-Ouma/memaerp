<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Unit extends BaseModel
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'institution.units';

    protected $fillable = [
        'institution_id', 'department_id', 'code', 'name', 'type', 'head_of_unit_id',
        'status', 'resolution_reference', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
