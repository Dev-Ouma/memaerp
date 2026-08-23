<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Platform\Contracts\ScopeAware;
use App\Platform\Models\BaseModel;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CourseOffering extends BaseModel implements ScopeAware
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'course.course_offerings';

    protected $fillable = [
        'institution_id',
        'course_id',
        'term_id',
        'campus_id',
        'department_id',
        'faculty_id',
        'lecturer_id',
        'section_code',
        'max_capacity',
        'enrolled_count',
        'delivery_mode',
        'is_open_for_enrollment',
    ];

    /**
     * How a user's authorization scope maps onto this table.
     *
     * SELF resolves to the assigned lecturer: a lecturer's grant covers the offerings they
     * actually teach, not every offering in their department.
     *
     * @return array<string, string>
     */
    public function scopeColumns(): array
    {
        return [
            Scope::DEPARTMENT => 'department_id',
            Scope::FACULTY => 'faculty_id',
            Scope::CAMPUS => 'campus_id',
            Scope::SELF => 'lecturer_id',
        ];
    }

    protected function casts(): array
    {
        return [
            'max_capacity' => 'integer',
            'enrolled_count' => 'integer',
            'is_open_for_enrollment' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<Term, $this> */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /** @return BelongsTo<Campus, $this> */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /** @return BelongsTo<User, $this> */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }
}
