<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Modules\Iam\Models\User;
use App\Platform\Contracts\ScopeAware;
use App\Platform\Support\ResolvedScopes;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * The single place where "may this user do this?" is answered.
 *
 * Two entry points, and the difference between them is the whole design:
 *
 *   - {@see allows()} answers about ONE known record. Use it for writes.
 *   - {@see scopeQuery()} constrains a QUERY before it runs. Use it for reads.
 *
 * Reads must never be authorized by fetching rows and filtering them afterwards. Post-fetch
 * filtering leaks through pagination counts, aggregate totals and export row counts even when
 * the rows themselves are stripped — a lecturer paging a "students" list learns exactly how many
 * students exist outside their department. Scope belongs in the WHERE clause.
 */
final readonly class AccessControl
{
    public function __construct(private ScopeResolver $resolver) {}

    /**
     * Does the user hold $permission over the scope that $record sits in?
     *
     * Returns false — never throws — for an unauthenticated or inactive user, so that callers
     * can treat the answer uniformly.
     */
    public function allows(?User $user, string $permission, ?Model $record = null): bool
    {
        if ($user === null || ! $user->canAuthenticate()) {
            return false;
        }

        $scopes = $this->resolver->resolve($user, $permission);

        if ($scopes->isEmpty()) {
            return false;
        }

        // Permission held institution-wide: no record-level narrowing to do.
        if ($scopes->institutionWide) {
            return $record === null || $this->belongsToSameInstitution($user, $record);
        }

        // Permission held only in narrower scopes, but the caller asked about the capability in
        // the abstract. Answer yes: they hold it somewhere. The record-level call still decides.
        if ($record === null) {
            return true;
        }

        if (! $record instanceof ScopeAware) {
            throw new LogicException(sprintf(
                '%s must implement %s to be authorized by scope. Without it, a narrowly-scoped '
                .'grant cannot be checked against the record and would silently pass.',
                $record::class,
                ScopeAware::class,
            ));
        }

        if (! $this->belongsToSameInstitution($user, $record)) {
            return false;
        }

        return $this->recordMatchesScopes($user, $record, $scopes);
    }

    /** Inverse of {@see allows()}, for readability at call sites. */
    public function denies(?User $user, string $permission, ?Model $record = null): bool
    {
        return ! $this->allows($user, $permission, $record);
    }

    /**
     * Constrain a query to the rows the user may see under $permission.
     *
     * When the user holds the permission nowhere, this applies `whereRaw('1 = 0')` rather than
     * leaving the query untouched. An unfiltered query is the failure mode this method exists to
     * prevent: it would return the entire table to someone with no grant at all.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeQuery(Builder $query, ?User $user, string $permission): Builder
    {
        if ($user === null || ! $user->canAuthenticate()) {
            return $query->whereRaw('1 = 0');
        }

        $scopes = $this->resolver->resolve($user, $permission);

        if ($scopes->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();

        if ($scopes->institutionWide) {
            return $this->constrainToInstitution($query, $user, $model);
        }

        if (! $model instanceof ScopeAware) {
            throw new LogicException(sprintf(
                '%s must implement %s to be scope-filtered.',
                $model::class,
                ScopeAware::class,
            ));
        }

        $columns = $model->scopeColumns();
        $this->constrainToInstitution($query, $user, $model);

        return $query->where(function (Builder $outer) use ($columns, $scopes, $user): void {
            $matched = false;

            if ($scopes->departmentIds !== [] && isset($columns[Scope::DEPARTMENT])) {
                $outer->orWhereIn($columns[Scope::DEPARTMENT], $scopes->departmentIds);
                $matched = true;
            }

            if ($scopes->facultyIds !== [] && isset($columns[Scope::FACULTY])) {
                $outer->orWhereIn($columns[Scope::FACULTY], $scopes->facultyIds);
                $matched = true;
            }

            if ($scopes->campusIds !== [] && isset($columns[Scope::CAMPUS])) {
                $outer->orWhereIn($columns[Scope::CAMPUS], $scopes->campusIds);
                $matched = true;
            }

            if ($scopes->includesSelf && isset($columns[Scope::SELF])) {
                $outer->orWhere($columns[Scope::SELF], $user->getKey());
                $matched = true;
            }

            // The user's grants exist but none of them map onto a column this model exposes —
            // e.g. a department-scoped grant against a model that only knows about campuses.
            // That is a no-match, not a free pass.
            if (! $matched) {
                $outer->whereRaw('1 = 0');
            }
        });
    }

    private function recordMatchesScopes(User $user, Model&ScopeAware $record, ResolvedScopes $scopes): bool
    {
        $columns = $record->scopeColumns();

        if ($scopes->includesSelf
            && isset($columns[Scope::SELF])
            && $record->getAttribute($columns[Scope::SELF]) === $user->getKey()) {
            return true;
        }

        foreach ([
            Scope::DEPARTMENT => $scopes->departmentIds,
            Scope::FACULTY => $scopes->facultyIds,
            Scope::CAMPUS => $scopes->campusIds,
        ] as $type => $ids) {
            if ($ids === [] || ! isset($columns[$type])) {
                continue;
            }

            if (in_array($record->getAttribute($columns[$type]), $ids, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tenant isolation, applied independently of scope. Even an institution-wide grant is
     * institution-wide within ONE institution.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function constrainToInstitution(Builder $query, User $user, Model $model): Builder
    {
        if (! $this->hasInstitutionColumn($model)) {
            return $query;
        }

        return $query->where(
            $model->qualifyColumn('institution_id'),
            $user->institution_id,
        );
    }

    private function belongsToSameInstitution(User $user, Model $record): bool
    {
        if (! $this->hasInstitutionColumn($record)) {
            return true;
        }

        return $record->getAttribute('institution_id') === $user->institution_id;
    }

    private function hasInstitutionColumn(Model $model): bool
    {
        return in_array('institution_id', $model->getFillable(), true)
            || $model->getAttribute('institution_id') !== null;
    }
}
