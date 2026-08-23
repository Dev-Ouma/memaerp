<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class KuccpsPlacement extends BaseModel
{
    use Auditable;

    protected $table = 'admission.kuccps_placements';

    protected $fillable = [
        'institution_id', 'intake_id', 'kuccps_index', 'applicant_name', 'programme_code',
        'programme_id', 'mean_grade', 'aggregate_points', 'import_batch', 'application_id', 'status',
    ];

    protected function casts(): array
    {
        return ['aggregate_points' => 'decimal:2'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Intake, $this> */
    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    /** @return BelongsTo<Programme, $this> */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
