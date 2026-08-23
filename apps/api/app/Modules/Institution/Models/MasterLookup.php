<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;

final class MasterLookup extends BaseModel
{
    use Auditable;

    protected $table = 'institution.master_lookups';

    protected $fillable = ['institution_id', 'type', 'code', 'name', 'metadata', 'display_order', 'is_active', 'effective_from', 'effective_to'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'display_order' => 'integer', 'is_active' => 'boolean', 'effective_from' => 'date', 'effective_to' => 'date'];
    }
}
