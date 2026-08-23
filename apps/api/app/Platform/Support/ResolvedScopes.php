<?php

declare(strict_types=1);

namespace App\Platform\Support;

/**
 * A user's scope grants for one permission, expanded to concrete institution ids.
 *
 * "Faculty of Science" expands to every department under it, because a Dean's authority over the
 * faculty must reach the departments inside it — but that expansion is a database fact about the
 * org structure, not something the Scope value object can know on its own.
 */
final readonly class ResolvedScopes
{
    /**
     * @param  list<string>  $campusIds
     * @param  list<string>  $facultyIds
     * @param  list<string>  $departmentIds
     */
    public function __construct(
        public bool $institutionWide,
        public bool $includesSelf,
        public array $campusIds = [],
        public array $facultyIds = [],
        public array $departmentIds = [],
    ) {}

    public static function none(): self
    {
        return new self(institutionWide: false, includesSelf: false);
    }

    public static function institutionWide(): self
    {
        return new self(institutionWide: true, includesSelf: true);
    }

    /**
     * True when the user holds the permission nowhere at all. Callers MUST branch on this
     * explicitly: an empty scope set means deny, and silently applying no filter would return
     * every row in the table.
     */
    public function isEmpty(): bool
    {
        return ! $this->institutionWide
            && ! $this->includesSelf
            && $this->campusIds === []
            && $this->facultyIds === []
            && $this->departmentIds === [];
    }
}
