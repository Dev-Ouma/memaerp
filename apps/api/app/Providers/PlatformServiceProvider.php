<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Audit\Contracts\AuditRecorder as AuditRecorderContract;
use App\Modules\Audit\Services\AuditRecorder;
use App\Platform\Support\RequestContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One context per request/job lifecycle. Everything that needs to know "who, from where,
        // under which correlation id" reads it from here rather than reaching into the request.
        $this->app->scoped(RequestContext::class);
        $this->app->scoped(AuditRecorderContract::class, AuditRecorder::class);
    }

    public function boot(): void
    {
        // Fail loudly outside production rather than degrading quietly.
        //
        // An N+1 that is merely slow in staging is an outage during registration week; and an
        // attribute the query never selected reads as null by default, which is exactly how a
        // scope comparison ends up passing against nothing.
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
