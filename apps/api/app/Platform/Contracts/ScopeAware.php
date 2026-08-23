<?php

declare(strict_types=1);

namespace App\Platform\Contracts;

/**
 * Implemented by any model whose rows belong to a slice of the institution and can therefore be
 * filtered by a user's authorization scope.
 *
 * A model that cannot answer "which column of mine identifies the department this row belongs
 * to?" cannot be scope-filtered, and the access control layer will refuse to guess.
 */
interface ScopeAware
{
    /**
     * Map each scope type this model understands to the column carrying that scope's id.
     *
     * Example for a course offering:
     *   [
     *     Scope::DEPARTMENT => 'department_id',
     *     Scope::FACULTY    => 'faculty_id',
     *     Scope::CAMPUS     => 'campus_id',
     *     Scope::SELF       => 'lecturer_user_id',
     *   ]
     *
     * Types omitted here are ones this model cannot be filtered by. A grant at an omitted type
     * matches NOTHING on this model rather than matching everything.
     *
     * @return array<string, string>
     */
    public function scopeColumns(): array;
}
