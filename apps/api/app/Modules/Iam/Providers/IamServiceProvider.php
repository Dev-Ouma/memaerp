<?php

declare(strict_types=1);

namespace App\Modules\Iam\Providers;

use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\AccessControl;
use App\Modules\Iam\Services\ScopeResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScopeResolver::class);
        $this->app->singleton(AccessControl::class);
    }

    public function boot(): void
    {
        $this->registerGateBridge();
    }

    /**
     * Route every `$user->can('module.resource.action')` through {@see AccessControl}, so the
     * scope dimension applies even at call sites that only know about Laravel's Gate.
     *
     * `Gate::before` is deliberately NOT used to grant a superuser bypass. A blanket bypass makes
     * every policy in the system untestable for the one account most worth testing.
     */
    private function registerGateBridge(): void
    {
        Gate::after(function (User $user, string $ability, ?bool $result, array $arguments): ?bool {
            // A policy or explicit Gate definition already decided; leave it alone.
            if ($result !== null) {
                return null;
            }

            // Only permission-shaped abilities (module.resource.action) reach here.
            if (substr_count($ability, '.') !== 2) {
                return null;
            }

            $record = $arguments[0] ?? null;

            return app(AccessControl::class)->allows(
                $user,
                $ability,
                $record instanceof Model ? $record : null,
            );
        });
    }
}
