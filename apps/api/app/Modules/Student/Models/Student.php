<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Admission\Models\Application;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\StudyMode;
use App\Platform\Concerns\Auditable;
use App\Platform\Contracts\ScopeAware;
use App\Platform\Models\BaseModel;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Student extends BaseModel implements ScopeAware
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'student.students';

    public const STATUSES = [
        'ACTIVE', 'ON_LEAVE', 'SUSPENDED', 'GRADUATED', 'WITHDRAWN',
    ];

    protected $fillable = [
        'institution_id',
        'person_id',
        'application_id',
        'programme_id',
        'curriculum_version_id',
        'campus_id',
        'department_id',
        'faculty_id',
        'admission_year_id',
        'intake_id',
        'study_mode_id',
        'student_number',
        'current_year_level',
        'current_semester',
        'academic_standing',
        'status',
        'photo_url',
        'digital_id_token',
        'digital_id_issued_at',
        'digital_id_status',
        'matriculated_on',
    ];

    /**
     * How a user's authorization scope maps onto this table.
     *
     * There is deliberately NO Scope::SELF entry. A student's own access to their own record is
     * a different question, answered by the student portal's own ownership check against
     * `person_id`; mapping SELF to a column here would let any self-scoped grant — a lecturer's,
     * say — match student rows it has no business seeing.
     *
     * @return array<string, string>
     */
    public function scopeColumns(): array
    {
        return [
            Scope::DEPARTMENT => 'department_id',
            Scope::FACULTY => 'faculty_id',
            Scope::CAMPUS => 'campus_id',
        ];
    }

    protected function casts(): array
    {
        return [
            'current_year_level' => 'integer',
            'current_semester' => 'integer',
            'matriculated_on' => 'date',
            'digital_id_issued_at' => 'immutable_datetime',
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

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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

    /** @return BelongsTo<Campus, $this> */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function admissionYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'admission_year_id');
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

    /** @return HasMany<TermRegistration, $this> */
    public function termRegistrations(): HasMany
    {
        return $this->hasMany(TermRegistration::class);
    }

    /** @return HasMany<StudentDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    /** @return HasMany<NextOfKin, $this> */
    public function nextOfKin(): HasMany
    {
        return $this->hasMany(NextOfKin::class);
    }

    /** @return HasMany<StudentStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class);
    }

    /** @return HasMany<MatriculationLog, $this> */
    public function matriculationLogs(): HasMany
    {
        return $this->hasMany(MatriculationLog::class);
    }
}
