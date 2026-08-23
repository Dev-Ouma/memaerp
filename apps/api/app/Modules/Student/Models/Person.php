<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The canonical person spine.
 *
 * One human being is one row here, for life. An applicant who becomes a student, who is later
 * hired as a tutorial fellow, who eventually becomes an alumnus, is ONE person row with four
 * {@see PersonIdentity} rows — not four unrelated records in four modules that nobody can
 * reconcile. This is why a person row is never hard-deleted: deleting it would orphan a
 * transcript, a payslip and an alumni record simultaneously.
 *
 * Every module that needs a human being points at this table. None of them re-implement names.
 */
final class Person extends BaseModel
{
    use Auditable;
    use HasFactory;

    protected $table = 'student.persons';

    protected $fillable = [
        'institution_id',
        'given_name', 'middle_name', 'family_name',
        'date_of_birth', 'gender', 'nationality',
        'national_id', 'passport_number', 'birth_certificate_number',
        'primary_email', 'primary_phone', 'address',
    ];

    /**
     * Government identifiers are hidden from array/JSON output by default. A resource that needs
     * to expose one must do so deliberately, in the resource class, under its own policy check.
     */
    protected $hidden = ['national_id', 'passport_number', 'birth_certificate_number'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'address' => 'array',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => implode(' ', array_filter([
            $this->given_name,
            $this->middle_name,
            $this->family_name,
        ])));
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<PersonIdentity, $this> */
    public function identities(): HasMany
    {
        return $this->hasMany(PersonIdentity::class);
    }

    /** The active identity of a given kind, if the person currently holds one. */
    public function identityOf(string $type): ?PersonIdentity
    {
        return $this->identities
            ->firstWhere(fn (PersonIdentity $identity): bool => $identity->identity_type === $type
                && $identity->status === PersonIdentity::STATUS_ACTIVE);
    }

    public function isCurrently(string $type): bool
    {
        return $this->identityOf($type) !== null;
    }
}
