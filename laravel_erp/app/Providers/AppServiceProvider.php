<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', static fn (User $user): bool => $user->isAdmin());
        foreach (array_keys(PermissionCatalogue::permissions()) as $permission) {
            Gate::define($permission, static fn (User $user): bool => app(AccessControl::class)->allows($user, $permission));
        }
    }
}
