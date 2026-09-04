<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SystemMaintenanceConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class MaintenanceHeartbeatCommand extends Command
{
    protected $signature = 'maintenance:heartbeat';

    protected $description = 'Record that the scheduler ran and stamp the OpsCenter last-cron timestamp.';

    public function handle(): int
    {
        $ranAt = now();
        Cache::put('maintenance:last_cron_at', $ranAt->toIso8601String(), now()->addDays(7));
        Cache::put('maintenance:last_cron_output', 'heartbeat ok '.$ranAt->toDateTimeString(), now()->addDays(7));

        $config = SystemMaintenanceConfig::query()->first();
        if ($config) {
            $config->last_cron_run_at = $ranAt;
            $config->save();
        }

        $this->info('Maintenance heartbeat recorded at '.$ranAt->toDateTimeString());

        return self::SUCCESS;
    }
}
