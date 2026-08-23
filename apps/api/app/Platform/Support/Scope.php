<?php

declare(strict_types=1);

namespace App\Platform\Support;

use InvalidArgumentException;
use Stringable;

/**
 * The scope dimension of authorization: WHERE a role applies.
 *
 * Authorization in this system has three orthogonal dimensions and all three must be satisfied:
 *
 *   1. permission — may this action be performed at all?           (examination.marks.approve)
 *   2. role       — does the user hold a role granting it?         (Head of Department)
 *   3. scope      — over which slice of the institution?           (Department: Computer Science)
 *
 * Holding `examination.marks.approve` does not mean approving ANY marks; it means approving
 * marks within scope. A Head of Computer Science approving Nursing marks is not a permission
 * failure — the permission is present — it is a scope failure, and scope is the dimension most
 * RBAC implementations quietly omit.
 *
 * Scopes nest: INSTITUTION contains CAMPUS contains FACULTY contains DEPARTMENT. SELF is
 * deliberately outside that hierarchy — it is the narrowest possible scope and contains nothing.
 */
final readonly class Scope implements Stringable
{
    public const string INSTITUTION = 'institution';

    public const string CAMPUS = 'campus';

    public const string FACULTY = 'faculty';

    public const string DEPARTMENT = 'department';

    public const string SELF = 'self';

    /** Broadest first. Position in this list is the containment order. */
    public const array HIERARCHY = [
        self::INSTITUTION,
        self::CAMPUS,
        self::FACULTY,
        self::DEPARTMENT,
    ];

    public const array TYPES = [
        self::INSTITUTION,
        self::CAMPUS,
        self::FACULTY,
        self::DEPARTMENT,
        self::SELF,
    ];

    public function __construct(
        public string $type,
        public ?string $id = null,
    ) {
        if (! in_array($this->type, self::TYPES, true)) {
            throw new InvalidArgumentException("Unknown scope type [{$this->type}].");
        }

        // An institution-wide or self grant needs no target id; anything narrower does, or it is
        // an unbounded grant wearing a narrow label.
        if ($this->id === null && ! in_array($this->type, [self::INSTITUTION, self::SELF], true)) {
            throw new InvalidArgumentException("Scope type [{$this->type}] requires a target id.");
        }
    }

    public static function institution(): self
    {
        return new self(self::INSTITUTION);
    }

    public static function self(): self
    {
        return new self(self::SELF);
    }

    public static function campus(string $id): self
    {
        return new self(self::CAMPUS, $id);
    }

    public static function faculty(string $id): self
    {
        return new self(self::FACULTY, $id);
    }

    public static function department(string $id): self
    {
        return new self(self::DEPARTMENT, $id);
    }

    public function isInstitutionWide(): bool
    {
        return $this->type === self::INSTITUTION;
    }

    public function isSelf(): bool
    {
        return $this->type === self::SELF;
    }

    /**
     * Is this scope at least as broad as $other — i.e. does holding this grant cover $other?
     *
     * Note this compares scope TYPES only. Whether department X actually sits inside faculty Y
     * is a database question, answered by {@see ScopeResolver}, because it depends on the org
     * structure at a point in time. This method answers the cheap half.
     */
    public function contains(self $other): bool
    {
        if ($this->isSelf()) {
            return $other->isSelf();
        }

        if ($this->isInstitutionWide()) {
            return ! $other->isSelf();
        }

        $mine = array_search($this->type, self::HIERARCHY, true);
        $theirs = array_search($other->type, self::HIERARCHY, true);

        if ($mine === false || $theirs === false) {
            return false;
        }

        if ($mine === $theirs) {
            return $this->id === $other->id;
        }

        return $mine < $theirs;
    }

    public function equals(self $other): bool
    {
        return $this->type === $other->type && $this->id === $other->id;
    }

    public function __toString(): string
    {
        return $this->id === null ? $this->type : "{$this->type}:{$this->id}";
    }
}
