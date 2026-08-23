<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Intake extends BaseModel
{
    use Auditable;

    protected $table = 'institution.intakes';

    protected $fillable = ['institution_id', 'academic_year_id', 'code', 'name', 'opens_on', 'closes_on', 'reporting_on', 'status'];

    protected function casts(): array
    {
        return ['opens_on' => 'date', 'closes_on' => 'date', 'reporting_on' => 'date'];
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
