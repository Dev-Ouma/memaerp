<?php

declare(strict_types=1);

namespace App\Modules\Enrollment\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TermRegistration extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'enrollment.term_registrations';

    protected $fillable = [
        'institution_id',
        'student_id',
        'term_id',
        'year_level',
        'semester',
        'financial_clearance_status',
        'status',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'year_level' => 'integer',
            'semester' => 'integer',
            'financial_clearance_status' => 'boolean',
            'registered_at' => 'immutable_datetime',
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

    /** @return BelongsTo<Term, $this> */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /** @return HasMany<CourseEnrollment, $this> */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
