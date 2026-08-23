<?php

declare(strict_types=1);

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Contracts\AuditRecorder as AuditRecorderContract;
use App\Modules\Audit\Services\AuditRecorder;
use Illuminate\Support\ServiceProvider;

final class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Scoped, not singleton: a queue worker handles many jobs in one process, and the
        // recorder holds the request context. A singleton would attribute job #2 to job #1's user.
        $this->app->scoped(AuditRecorderContract::class, AuditRecorder::class);
        $this->app->scoped(AuditRecorder::class);
    }
}
