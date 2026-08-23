<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\StudyMode;
use App\Modules\Student\Models\MatriculationLog;
use App\Modules\Student\Models\Person;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Application extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    public const STATUSES = [
        'DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'SHORTLISTED',
        'ADMITTED', 'REJECTED', 'ACCEPTED', 'EXPIRED', 'MATRICULATED',
    ];

    protected $table = 'admission.applications';

    protected $fillable = [
        'institution_id',
        'person_id',
        'prospect_id',
        'programme_id',
        'campus_id',
        'academic_year_id',
        'intake_id',
        'study_mode_id',
        'application_number',
        'status',
        'submitted_at',
        'is_fee_paid',
        'qualification_score',
        'secondary_school_name',
        'mean_grade',
        'kcse_index_number',
        'entry_path',
        'offer_letter_ref',
        'offer_qr_token',
        'offer_letter_hash',
        'offer_issued_at',
        'offer_expires_at',
        'offer_accepted_at',
        'decision_notes',
        'documents_verified_at',
        'documents_verified_by',
        'application_fee_amount',
        'application_fee_currency',
    ];

    protected function casts(): array
    {
        return [
            'is_fee_paid' => 'boolean',
            'qualification_score' => 'decimal:2',
            'application_fee_amount' => 'decimal:2',
            'submitted_at' => 'immutable_datetime',
            'offer_issued_at' => 'immutable_datetime',
            'offer_expires_at' => 'immutable_datetime',
            'offer_accepted_at' => 'immutable_datetime',
            'documents_verified_at' => 'immutable_datetime',
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

    /** @return BelongsTo<Prospect, $this> */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
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

    /** @return BelongsTo<Intake, $this> */
    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    /** @return BelongsTo<StudyMode, $this> */
    public function studyMode(): BelongsTo
    {
        return $this->belongsTo(StudyMode::class);
    }

    /** @return BelongsTo<User, $this> */
    public function documentsVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'documents_verified_by');
    }

    /** @return HasMany<ApplicationDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /** @return HasMany<ApplicationPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(ApplicationPayment::class);
    }

    /** @return HasMany<ApplicationReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class);
    }

    /** @return HasOne<MatriculationLog, $this> */
    public function matriculationLog(): HasOne
    {
        return $this->hasOne(MatriculationLog::class);
    }
}
