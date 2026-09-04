<?php

namespace App\Providers;

use App\Models\ModuleState;
use App\Models\SystemBroadcast;
use App\Models\User;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // No Gate::before admin bypass — platform.retention.execute and other segregated
        // abilities must stay deny-by-default even for users.role=admin.
        Gate::define('admin', static fn (User $user): bool => $user->isAdmin());
        Gate::define('recycle-bin.view', static fn (User $user): bool => $user->isAdmin() || app(AccessControl::class)->allows($user, 'platform.audit.view'));
        Gate::define('recycle-bin.restore', static fn (User $user): bool => $user->isAdmin() || app(AccessControl::class)->allows($user, 'platform.retention.execute'));
        Gate::define('recycle-bin.purge', static fn (User $user): bool => $user->isAdmin() || app(AccessControl::class)->allows($user, 'platform.retention.execute'));

        foreach (array_keys(PermissionCatalogue::permissions()) as $permission) {
            Gate::define($permission, static fn (User $user): bool => app(AccessControl::class)->allows($user, $permission));
        }

        Blade::if('erpModule', static function (string $key): bool {
            return ModuleState::visibleTo(auth()->user(), $key);
        });

        View::composer('layouts.app', static function ($view): void {
            try {
                $broadcasts = SystemBroadcast::query()->active()->latest()->limit(5)->get();
            } catch (\Throwable) {
                $broadcasts = collect();
            }
            $view->with('erpBroadcasts', $broadcasts);
        });
    }
}
