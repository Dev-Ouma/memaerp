<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemBroadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;

final class SystemHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $diskTotal = disk_total_space(base_path()) ?: 1;
        $diskFree = disk_free_space(base_path()) ?: 0;
        $diskUsed = max(0, $diskTotal - $diskFree);
        $diskPercentage = round(($diskUsed / $diskTotal) * 100, 1);

        $ramUsed = memory_get_usage(true);
        $ramLimit = $this->iniBytes((string) ini_get('memory_limit'));
        $ramPercentage = $ramLimit > 0 ? round(($ramUsed / $ramLimit) * 100, 1) : 0.0;

        $load = function_exists('sys_getloadavg') ? (float) (sys_getloadavg()[0] ?? 0) : 0.0;
        $cores = $this->cpuCount();
        $cpuPercentage = (int) min(100, round(($load / max(1, $cores)) * 100));

        $dbStatus = 'Connected';
        try {
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            $dbStatus = 'Disconnected: '.$e->getMessage();
        }

        $healthScore = (int) max(0, min(100, round(
            (100 - min($diskPercentage, 100)) * 0.4
            + (100 - min($cpuPercentage, 100)) * 0.35
            + (100 - min($ramPercentage, 100)) * 0.25
        )));

        $status = $dbStatus === 'Connected' && $diskPercentage < 90 && $cpuPercentage < 90
            ? ($healthScore >= 70 ? 'healthy' : 'degraded')
            : 'critical';

        return [
            'cpu_percentage' => $cpuPercentage,
            'cpu_load' => round($load, 2),
            'cpu_cores' => $cores,
            'ram_used' => (int) round($ramUsed / 1048576),
            'ram_limit' => (int) round($ramLimit / 1048576),
            'ram_percentage' => $ramPercentage,
            'disk_total' => round($diskTotal / 1073741824, 1).' GB',
            'disk_used' => round($diskUsed / 1073741824, 1).' GB',
            'disk_percentage' => $diskPercentage,
            'db_status' => $dbStatus,
            'db_healthy' => $dbStatus === 'Connected',
            'uptime' => $this->uptimeLabel(),
            'active_users' => $this->activeSessionCount(),
            'health_score' => $healthScore,
            'status' => $status,
            'last_cron_at' => Cache::get('maintenance:last_cron_at'),
            'last_cron_output' => Cache::get('maintenance:last_cron_output', ''),
            'console_log' => Cache::get('maintenance:console_log', 'Ready. No cloud transfer in progress.'),
            'broadcasts' => SystemBroadcast::query()->active()->latest()->limit(8)->get(),
        ];
    }

    public function recordConsole(string $line): void
    {
        $existing = (string) Cache::get('maintenance:console_log', '');
        $next = now()->toDateTimeString().'  '.$line."\n".$existing;
        Cache::put('maintenance:console_log', mb_substr($next, 0, 8000), now()->addDays(7));
    }

    public function codebaseStatus(): array
    {
        $root = base_path();
        $head = Process::run(['git', '-C', $root, 'rev-parse', '--short', 'HEAD']);
        $log = Process::run(['git', '-C', $root, 'log', '-1', '--pretty=%h %s']);
        $status = Process::run(['git', '-C', $root, 'status', '--porcelain']);

        return [
            'ok' => $head->successful(),
            'head' => trim($head->output()),
            'latest' => trim($log->output()),
            'dirty' => trim($status->output()) !== '',
            'status' => trim($status->output()) === '' ? 'working tree clean' : trim($status->output()),
        ];
    }

    public function cloudMirrorStatus(): array
    {
        $rclone = Process::run(['which', 'rclone']);
        if (! $rclone->successful()) {
            return [
                'ok' => false,
                'message' => 'rclone is not installed on this host. Cloud mirror is unavailable until the binary is present.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'rclone found at '.trim($rclone->output()).'. No remote is configured in this ERP, so a live GDrive sync was not started.',
        ];
    }

    private function iniBytes(string $value): int
    {
        if ($value === '' || $value === '-1') {
            return 0;
        }
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1073741824,
            'm' => $number * 1048576,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function cpuCount(): int
    {
        $mac = Process::run(['sysctl', '-n', 'hw.ncpu']);
        if ($mac->successful() && (int) trim($mac->output()) > 0) {
            return (int) trim($mac->output());
        }
        $linux = Process::run(['nproc']);
        if ($linux->successful() && (int) trim($linux->output()) > 0) {
            return (int) trim($linux->output());
        }

        return 1;
    }

    private function uptimeLabel(): string
    {
        $seconds = 0;
        if (is_readable('/proc/uptime')) {
            $seconds = (int) floatval(explode(' ', (string) file_get_contents('/proc/uptime'))[0] ?? 0);
        } elseif (is_file(base_path('vendor/autoload.php'))) {
            $seconds = max(0, time() - (int) filemtime(base_path('vendor/autoload.php')));
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        if ($days > 0) {
            return $days.'d '.$hours.'h';
        }

        return $hours.'h '.intdiv($seconds % 3600, 60).'m';
    }

    private function activeSessionCount(): int
    {
        try {
            if (! Schema::hasTable('sessions')) {
                return 1;
            }

            return (int) DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                ->count();
        } catch (\Throwable) {
            return 1;
        }
    }
}
