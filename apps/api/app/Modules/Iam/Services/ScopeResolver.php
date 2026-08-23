<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Platform\Support\ResolvedScopes;
use App\Platform\Support\Scope;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Expands a user's scope grants for one permission into concrete institution ids.
 *
 * A grant of "Faculty of Science" must reach every department beneath it — but which departments
 * those are is a fact about the org structure stored in the database, so the expansion happens
 * here rather than in the {@see Scope} value object.
 *
 * Results are cached per user+permission for a short window. The cache key embeds
 * `iam.users.access_version` — a counter the DATABASE bumps via trigger whenever a role
 * assignment or a role's permission set changes.
 *
 * Invalidating from Eloquent observers instead would leave a hole: a mass `update()`, a raw SQL
 * statement or a psql session changes grants without firing a model event, and the user would
 * keep their old access until the TTL lapsed. Revocation that depends on which tool made the
 * change is not revocation. The version is already on the user row every request loads, so
 * reading it costs nothing, and a bump orphans every cached permission for that user at once.
 */
final readonly class ScopeResolver
{
    private const int CACHE_TTL_SECONDS = 60;

    public function __construct(private Cache $cache) {}

    public function resolve(User $user, string $permission): ResolvedScopes
    {
        return $this->cache->remember(
            $this->cacheKey($user, $permission),
            self::CACHE_TTL_SECONDS,
            fn (): ResolvedScopes => $this->compute($user, $permission),
        );
    }

    private function compute(User $user, string $permission): ResolvedScopes
    {
        $grants = $user->scopesFor($permission);

        if ($grants === []) {
            return ResolvedScopes::none();
        }

        foreach ($grants as $grant) {
            // One institution-wide grant subsumes every narrower one; stop looking.
            if ($grant->isInstitutionWide()) {
                return ResolvedScopes::institutionWide();
            }
        }

        $campusIds = [];
        $facultyIds = [];
        $departmentIds = [];
        $includesSelf = false;

        foreach ($grants as $grant) {
            match ($grant->type) {
                Scope::SELF => $includesSelf = true,
                Scope::CAMPUS => $campusIds[] = $grant->id,
                Scope::FACULTY => $facultyIds[] = $grant->id,
                Scope::DEPARTMENT => $departmentIds[] = $grant->id,
                default => null,
            };
        }

        // Downward expansion: campus -> its faculties -> their departments.
        if ($campusIds !== []) {
            $facultyIds = array_merge($facultyIds, Faculty::query()
                ->whereIn('campus_id', $campusIds)
                ->pluck('id')
                ->all());
        }

        if ($facultyIds !== []) {
            $departmentIds = array_merge($departmentIds, Department::query()
                ->whereIn('faculty_id', $facultyIds)
                ->pluck('id')
                ->all());
        }

        return new ResolvedScopes(
            institutionWide: false,
            includesSelf: $includesSelf,
            campusIds: array_values(array_unique($campusIds)),
            facultyIds: array_values(array_unique($facultyIds)),
            departmentIds: array_values(array_unique($departmentIds)),
        );
    }

    private function cacheKey(User $user, string $permission): string
    {
        return sprintf(
            'iam:scopes:%s:v%d:%s',
            $user->getKey(),
            $user->access_version,
            $permission,
        );
    }
}
