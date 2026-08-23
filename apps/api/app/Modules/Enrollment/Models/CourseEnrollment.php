<?php

declare(strict_types=1);

namespace App\Modules\Enrollment\Models;

use App\Modules\Course\Contracts\OfferingCapacity;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CourseEnrollment extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'enrollment.course_enrollments';

    protected $fillable = [
        'institution_id',
        'term_registration_id',
        'student_id',
        'course_offering_id',
        'status',
        'is_retake',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_retake' => 'boolean',
            'enrolled_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::created(function (self $enrollment): void {
            if ($enrollment->status === 'ENROLLED') {
                app(OfferingCapacity::class)->increment((string) $enrollment->course_offering_id);
            }
        });
        self::updated(function (self $enrollment): void {
            $previous = $enrollment->getOriginal('status');
            if ($previous === 'ENROLLED' && $enrollment->status === 'DROPPED') {
                app(OfferingCapacity::class)->decrement((string) $enrollment->course_offering_id);
            }
            if ($previous !== 'ENROLLED' && $enrollment->status === 'ENROLLED') {
                app(OfferingCapacity::class)->increment((string) $enrollment->course_offering_id);
            }
        });
        self::deleted(function (self $enrollment): void {
            if ($enrollment->getOriginal('status') === 'ENROLLED' || $enrollment->status === 'ENROLLED') {
                app(OfferingCapacity::class)->decrement((string) $enrollment->course_offering_id);
            }
        });
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<TermRegistration, $this> */
    public function termRegistration(): BelongsTo
    {
        return $this->belongsTo(TermRegistration::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<CourseOffering, $this> */
    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    /** @return HasOne<StudentMark, $this> */
    public function mark(): HasOne
    {
        return $this->hasOne(StudentMark::class);
    }
}
