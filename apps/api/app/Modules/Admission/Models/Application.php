<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Application extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'admission.applications';

    protected $fillable = [
        'institution_id',
        'person_id',
        'programme_id',
        'campus_id',
        'academic_year_id',
        'application_number',
        'status',
        'is_fee_paid',
        'qualification_score',
        'secondary_school_name',
        'mean_grade',
        'offer_letter_ref',
        'offer_issued_at',
        'offer_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_fee_paid' => 'boolean',
            'qualification_score' => 'decimal:2',
            'offer_issued_at' => 'immutable_datetime',
            'offer_accepted_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** @return BelongsTo<Programme, $this> */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /** @return BelongsTo<Campus, $this> */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
