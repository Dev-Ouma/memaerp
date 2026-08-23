<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A role a person holds, with the number the institution knows them by in that role.
 *
 * The identifier is unique per (institution, type) — one admission number, one student number,
 * one staff number — but a person may hold several identities at once, and identities END rather
 * than being deleted. A graduate's student number stays on file forever; its status becomes
 * `completed`. That history is what makes transcript reissue and alumni tracing possible.
 */
final class PersonIdentity extends BaseModel
{
    use Auditable;
    use HasFactory;

    public const string TYPE_APPLICANT = 'applicant';

    public const string TYPE_STUDENT = 'student';

    public const string TYPE_EMPLOYEE = 'employee';

    public const string TYPE_ALUMNUS = 'alumnus';

    public const string STATUS_ACTIVE = 'active';

    public const string STATUS_SUSPENDED = 'suspended';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_WITHDRAWN = 'withdrawn';

    protected $table = 'student.person_identities';

    protected $fillable = [
        'institution_id', 'person_id', 'identity_type', 'identifier',
        'status', 'started_on', 'ended_on', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @param Builder<$this> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /** @param Builder<$this> $query */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('identity_type', $type);
    }
}
