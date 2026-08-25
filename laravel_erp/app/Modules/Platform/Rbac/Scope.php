<?php

declare(strict_types=1);

namespace App\Modules\Platform\Rbac;

/**
 * Where a permission applies.
 *
 * A resource carries several scope dimensions at once — an application belongs to an institution, a
 * campus, a faculty, a department, a programme, an offering and an intake. A role grant names one of
 * them. Access is allowed when the grant's dimension matches the corresponding value on the resource,
 * which lets a departmental reviewer see their department's applications without listing every
 * programme, and without being able to see a neighbouring department's.
 */
final class Scope
{
    /** @param array<string, string|int|null> $dimensions */
    private function __construct(public readonly array $dimensions) {}

    /** @param array<string, string|int|null> $dimensions */
    public static function of(array $dimensions): self
    {
        return new self(array_filter(
            $dimensions,
            static fn ($value, string $key): bool => $value !== null && in_array($key, PermissionCatalogue::SCOPE_TYPES, true),
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    /** A check with no resource in hand — the permission itself is the whole question. */
    public static function none(): self
    {
        return new self([]);
    }

    public function matches(string $grantScopeType, ?string $grantScopeId): bool
    {
        // An institution-wide grant with no specific id covers everything beneath it.
        if ($grantScopeId === null) {
            return $grantScopeType === 'institution' || ! array_key_exists($grantScopeType, $this->dimensions);
        }

        if ($this->dimensions === []) {
            // No resource context: a narrowed grant still proves the user holds the permission
            // somewhere, which is what a bare capability check asks.
            return true;
        }

        return array_key_exists($grantScopeType, $this->dimensions)
            && (string) $this->dimensions[$grantScopeType] === $grantScopeId;
    }
}
