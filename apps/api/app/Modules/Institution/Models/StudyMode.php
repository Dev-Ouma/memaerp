<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;

final class StudyMode extends BaseModel
{
    use Auditable;

    protected $table = 'institution.study_modes';

    protected $fillable = ['institution_id', 'code', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
