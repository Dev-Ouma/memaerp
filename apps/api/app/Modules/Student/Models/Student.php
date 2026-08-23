<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Platform\Contracts\ScopeAware;
use App\Platform\Models\BaseModel;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Student extends BaseModel implements ScopeAware
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'student.students';

    protected $fillable = [
        'institution_id',
        'person_id',
        'programme_id',
        'campus_id',
        'department_id',
        'faculty_id',
        'admission_year_id',
        'student_number',
        'current_year_level',
        'current_semester',
        'academic_standing',
        'status',
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
    public function admissionYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'admission_year_id');
    }

    /** @return HasMany<\App\Modules\Enrollment\Models\TermRegistration, $this> */
    public function termRegistrations(): HasMany
    {
        return $this->hasMany(\App\Modules\Enrollment\Models\TermRegistration::class);
    }
}
