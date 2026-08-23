<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Platform\Concerns\Auditable;
use App\Platform\Contracts\ScopeAware;
use App\Platform\Models\BaseModel;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CourseOffering extends BaseModel implements ScopeAware
{
    use Auditable;

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
        'waitlist_count',
        'workload_credits',
        'delivery_mode',
        'status',
        'is_open_for_enrollment',
        'closed_at',
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
            'waitlist_count' => 'integer',
            'workload_credits' => 'integer',
            'is_open_for_enrollment' => 'boolean',
            'closed_at' => 'immutable_datetime',
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

    /** @return HasMany<OfferingAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(OfferingAllocation::class, 'course_offering_id');
    }

    /** @return HasMany<OfferingWaitlist, $this> */
    public function waitlist(): HasMany
    {
        return $this->hasMany(OfferingWaitlist::class, 'course_offering_id');
    }
}
