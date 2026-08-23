<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A term carries the calendar windows that gate behaviour across the system. Registration does
 * not open because an administrator flipped a switch — it opens because the term says so. Any
 * module needing "is this action allowed right now?" asks the term, never a boolean elsewhere.
 *
 * @property CarbonImmutable|null $registration_opens_at
 * @property CarbonImmutable|null $registration_closes_at
 * @property CarbonImmutable|null $add_drop_closes_at
 * @property CarbonImmutable|null $marks_entry_opens_at
 * @property CarbonImmutable|null $marks_entry_closes_at
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable $ends_on
 */
final class Term extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'institution.terms';

    protected $fillable = [
        'institution_id', 'academic_year_id', 'code', 'name', 'sequence',
        'starts_on', 'ends_on',
        'registration_opens_at', 'registration_closes_at', 'add_drop_closes_at',
        'marks_entry_opens_at', 'marks_entry_closes_at',
        'fee_payment_closes_at', 'exam_starts_on', 'exam_ends_on',
        'study_mode_code', 'term_type', 'status', 'published_at', 'is_current',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'registration_opens_at' => 'immutable_datetime',
            'registration_closes_at' => 'immutable_datetime',
            'add_drop_closes_at' => 'immutable_datetime',
            'marks_entry_opens_at' => 'immutable_datetime',
            'marks_entry_closes_at' => 'immutable_datetime',
            'fee_payment_closes_at' => 'immutable_datetime',
            'exam_starts_on' => 'date',
            'exam_ends_on' => 'date',
            'published_at' => 'immutable_datetime',
            'sequence' => 'integer',
            'is_current' => 'boolean',
        ];
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function registrationIsOpen(?CarbonImmutable $at = null): bool
    {
        return $this->windowIsOpen(
            $this->registration_opens_at,
            $this->registration_closes_at,
            $at,
        );
    }

    public function addDropIsOpen(?CarbonImmutable $at = null): bool
    {
        return $this->windowIsOpen(
            $this->registration_opens_at,
            $this->add_drop_closes_at,
            $at,
        );
    }

    public function marksEntryIsOpen(?CarbonImmutable $at = null): bool
    {
        return $this->windowIsOpen(
            $this->marks_entry_opens_at,
            $this->marks_entry_closes_at,
            $at,
        );
    }

    /**
     * A window with no configured bounds is CLOSED, not open. Absent configuration must never
     * read as permission — that is how marks get entered a year after a term ended.
     */
    private function windowIsOpen(
        ?CarbonImmutable $opens,
        ?CarbonImmutable $closes,
        ?CarbonImmutable $at,
    ): bool {
        if ($opens === null || $closes === null) {
            return false;
        }

        $at ??= CarbonImmutable::now();

        return $at->greaterThanOrEqualTo($opens) && $at->lessThanOrEqualTo($closes);
    }

    /** @param Builder<$this> $query */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_current', true);
    }
}
