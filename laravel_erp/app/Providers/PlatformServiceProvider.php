<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Platform\Audit\AuditRecorder;
use App\Modules\Platform\Idempotency\IdempotencyStore;
use App\Modules\Platform\Numbering\NumberGenerator;
use App\Modules\Platform\Outbox\OutboxPublisher;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Storage\ClamAvScanner;
use App\Modules\Platform\Storage\MalwareScanner;
use App\Modules\Platform\Storage\NullMalwareScanner;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // AccessControl caches a user's grants per request; a singleton keeps one authorisation
        // decision from costing a query per check on a list endpoint.
        $this->app->singleton(AccessControl::class);
        $this->app->singleton(AuditRecorder::class);
        $this->app->singleton(OutboxPublisher::class);
        $this->app->singleton(NumberGenerator::class);
        $this->app->singleton(IdempotencyStore::class);

        $this->app->singleton(MalwareScanner::class, static function (): MalwareScanner {
            $config = (array) config('admission.scanner');

            return match ($config['driver'] ?? 'null') {
                'clamav' => new ClamAvScanner(
                    (string) $config['clamav']['socket'],
                    (int) $config['clamav']['timeout'],
                ),
                default => new NullMalwareScanner,
            };
        });
    }
}
