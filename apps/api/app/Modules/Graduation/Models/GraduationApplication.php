<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Models;

use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class GraduationApplication extends BaseModel
{
    use Auditable;

    protected $table = 'graduation.applications';

    protected $fillable = [
        'institution_id', 'student_id', 'programme_id', 'curriculum_version_id',
        'status', 'audit_credits_required', 'audit_credits_earned', 'audit_passed',
        'applied_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'audit_credits_required' => 'decimal:2',
            'audit_credits_earned' => 'decimal:2',
            'audit_passed' => 'boolean',
            'applied_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Programme, $this> */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function curriculumVersion(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class);
    }

    /** @return HasMany<GraduationClearanceCheckpoint, $this> */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(GraduationClearanceCheckpoint::class, 'graduation_application_id');
    }

    /** @return HasMany<Certificate, $this> */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'graduation_application_id');
    }
}
