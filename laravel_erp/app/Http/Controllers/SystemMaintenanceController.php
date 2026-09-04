<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SystemBackup;
use App\Models\SystemBroadcast;
use App\Models\SystemMaintenanceConfig;
use App\Models\SystemVersion;
use App\Modules\Platform\Audit\AuditRecorder;
use App\Modules\Platform\Modules\ModuleCatalogue;
use App\Services\SystemBackupService;
use App\Services\SystemHealthService;
use App\Services\SystemUpgradeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SystemMaintenanceController extends Controller
{
    public function index(Request $request, SystemHealthService $healthService, SystemUpgradeService $upgrades): View
    {
        $this->authorizeAdmin($request);

        $config = SystemMaintenanceConfig::query()->first() ?? new SystemMaintenanceConfig([
            'is_lockdown' => false,
            'lockdown_type' => 'read_only',
            'locked_modules' => [],
        ]);
        $backups = SystemBackup::latest()->paginate(10, ['*'], 'backups_page');
        $versions = SystemVersion::latest('installed_at')->get();
        $currentVersion = $upgrades->current();
        $metrics = $healthService->metrics();

        $specs = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os_version' => PHP_OS.' ('.php_uname('r').')',
            'database_type' => DB::connection()->getDriverName(),
            'server_software' => $request->server('SERVER_SOFTWARE', php_sapi_name()),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time').' seconds',
        ];

        $health = $metrics;

        $logPath = storage_path('logs/laravel.log');
        $errorLogs = '';
        if (is_file($logPath) && ($fp = @fopen($logPath, 'rb')) !== false) {
            $size = (int) @filesize($logPath);
            $bytesToRead = min($size, 65536);
            if ($bytesToRead > 0) {
                fseek($fp, -$bytesToRead, SEEK_END);
                $chunk = (string) fread($fp, $bytesToRead);
                $lines = explode("\n", $chunk);
                $errorLogs = implode("\n", array_slice($lines, -60));
            }
            fclose($fp);
        }

        $modules = [];
        foreach (ModuleCatalogue::all() as $key => $definition) {
            $modules[$key] = $definition['name'];
        }

        $recentLogs = collect();
        try {
            $recentLogs = DB::table('audit_events')
                ->orderBy('occurred_at', 'desc')
                ->limit(15)
                ->get();
        } catch (\Throwable) {
            $recentLogs = collect();
        }

        $broadcasts = SystemBroadcast::query()->latest()->limit(20)->get();
        $consoleLog = $metrics['console_log'];

        return view('admin.setups.system-maintenance', compact(
            'config',
            'backups',
            'versions',
            'currentVersion',
            'specs',
            'health',
            'errorLogs',
            'modules',
            'recentLogs',
            'broadcasts',
            'consoleLog',
        ));
    }

    public function toggleLockdown(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'is_lockdown' => ['nullable', 'boolean'],
            'lockdown_type' => ['required', 'string', 'in:read_only,offline'],
            'ip_whitelist' => ['nullable', 'string', 'max:500'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'scheduled_start' => ['nullable', 'date'],
            'scheduled_end' => ['nullable', 'date', 'after:scheduled_start'],
            'locked_modules' => ['nullable', 'array'],
            'locked_modules.*' => ['string'],
        ]);

        $config = SystemMaintenanceConfig::query()->first() ?? new SystemMaintenanceConfig;

        $config->is_lockdown = (bool) ($validated['is_lockdown'] ?? false);
        $config->lockdown_type = $validated['lockdown_type'];
        $config->ip_whitelist = $validated['ip_whitelist'] ?? '';
        $config->maintenance_message = $validated['maintenance_message'] ?? '';
        $config->scheduled_start = ($validated['scheduled_start'] ?? null) ? CarbonImmutable::parse($validated['scheduled_start']) : null;
        $config->scheduled_end = ($validated['scheduled_end'] ?? null) ? CarbonImmutable::parse($validated['scheduled_end']) : null;
        $config->locked_modules = $validated['locked_modules'] ?? [];
        $config->save();

        app(AuditRecorder::class)->record('system.maintenance.lockdown_updated', [
            'actor_user_id' => $request->user()->id,
            'actor_role' => $request->user()->activeRole(),
            'subject_type' => SystemMaintenanceConfig::class,
            'subject_id' => $config->id,
            'after' => $config->toArray(),
        ]);

        return back()->with('success', 'System maintenance and lockdown configuration updated.');
    }

    public function clearCache(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $target = $request->input('target', 'all');

        try {
            if ($target === 'config' || $target === 'all') {
                Artisan::call('config:clear');
            }
            if ($target === 'route' || $target === 'all') {
                Artisan::call('route:clear');
            }
            if ($target === 'view' || $target === 'all') {
                Artisan::call('view:clear');
            }
            if ($target === 'app' || $target === 'all') {
                Artisan::call('cache:clear');
            }

            app(SystemHealthService::class)->recordConsole('Cache cleared: '.$target);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully for target: '.ucwords((string) $target),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: '.$e->getMessage(),
            ], 500);
        }
    }

    public function runOptimization(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        try {
            if (DB::connection()->getDriverName() === 'pgsql' && ! app()->runningUnitTests()) {
                DB::statement('VACUUM ANALYZE');
            }

            $config = SystemMaintenanceConfig::query()->first();
            if ($config) {
                $config->last_optimize_at = now();
                $config->save();
            }

            app(SystemHealthService::class)->recordConsole('Database optimize completed.');

            return response()->json([
                'success' => true,
                'message' => 'Database tables optimized and execution profiles updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database optimization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function triggerBackup(Request $request, SystemBackupService $backups): RedirectResponse
    {
        $this->authorizeAdmin($request);

        try {
            $backup = $backups->create($request->user());

            app(AuditRecorder::class)->record('system.maintenance.backup_created', [
                'actor_user_id' => $request->user()->id,
                'actor_role' => $request->user()->activeRole(),
                'subject_type' => SystemBackup::class,
                'subject_id' => $backup->id,
                'after' => ['filename' => $backup->filename, 'file_size' => $backup->file_size],
            ]);

            app(SystemHealthService::class)->recordConsole('Backup written: '.$backup->filename.' ('.$backup->file_size.' bytes)');

            return back()->with('success', "Database backup created successfully: {$backup->filename}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to generate system backup: '.$e->getMessage());
        }
    }

    public function downloadBackup(Request $request, SystemBackup $backup, SystemBackupService $backups): StreamedResponse
    {
        $this->authorizeAdmin($request);

        return $backups->download($backup);
    }

    public function restoreBackup(Request $request, SystemBackup $backup, SystemBackupService $backups): JsonResponse
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        try {
            $result = $backups->restore($backup);

            app(AuditRecorder::class)->record('system.maintenance.backup_restored', [
                'actor_user_id' => $request->user()->id,
                'actor_role' => $request->user()->activeRole(),
                'subject_type' => SystemBackup::class,
                'subject_id' => $backup->id,
                'after' => $result,
            ]);

            app(SystemHealthService::class)->recordConsole($result['message']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'executed' => $result['executed'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function applyUpgrade(Request $request, SystemUpgradeService $upgrades): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:major,minor,patch'],
            'changelog' => ['nullable', 'string', 'max:2000'],
        ]);

        $version = $upgrades->apply(
            $validated['type'] ?? 'patch',
            $validated['changelog'] ?? 'OpsCenter live upgrade: migrate --force and version pointer update.',
        );

        app(AuditRecorder::class)->record('system.maintenance.upgrade_applied', [
            'actor_user_id' => $request->user()->id,
            'actor_role' => $request->user()->activeRole(),
            'subject_type' => SystemVersion::class,
            'subject_id' => $version->id,
            'after' => ['version' => $version->version, 'type' => $version->type],
        ]);

        app(SystemHealthService::class)->recordConsole('Upgrade applied: '.$version->version);

        return response()->json([
            'success' => true,
            'version' => $version->version,
            'output' => $version->changelog,
            'message' => 'System upgraded to version '.$version->version.'.',
        ]);
    }

    public function triggerRollback(Request $request, SystemVersion $version, SystemUpgradeService $upgrades): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $upgrades->rollback($version);

        app(AuditRecorder::class)->record('system.maintenance.version_rolled_back', [
            'actor_user_id' => $request->user()->id,
            'actor_role' => $request->user()->activeRole(),
            'subject_type' => SystemVersion::class,
            'subject_id' => $version->id,
            'after' => ['version' => $version->version],
        ]);

        return back()->with('success', "System version rolled back from {$version->version}. Database migrations are not reversed.");
    }

    public function sendBroadcast(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $broadcast = SystemBroadcast::create([
            'message' => $validated['message'],
            'created_by' => $request->user()->id,
            'expires_at' => $validated['expires_at'] ?? now()->addHours(12),
        ]);

        app(AuditRecorder::class)->record('system.maintenance.broadcast_sent', [
            'actor_user_id' => $request->user()->id,
            'actor_role' => $request->user()->activeRole(),
            'subject_type' => SystemBroadcast::class,
            'subject_id' => $broadcast->id,
            'after' => ['message' => $broadcast->message],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast saved and shown on every signed-in ERP session.',
            'broadcast' => [
                'id' => $broadcast->id,
                'message' => $broadcast->message,
            ],
        ]);
    }

    public function runCron(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        Artisan::call('schedule:run');
        $scheduleOutput = trim(Artisan::output());
        Artisan::call('maintenance:heartbeat');
        $heartbeat = trim(Artisan::output());

        $output = trim($scheduleOutput."\n".$heartbeat);
        Cache::put('maintenance:last_cron_at', now()->toIso8601String(), now()->addDays(7));
        Cache::put('maintenance:last_cron_output', $output, now()->addDays(7));
        app(SystemHealthService::class)->recordConsole('Cron run: '.($output !== '' ? $output : 'no due events'));

        return response()->json([
            'success' => true,
            'message' => 'Scheduler ran.',
            'output' => $output !== '' ? $output : 'No scheduled events were due.',
        ]);
    }

    public function syncCodebase(Request $request, SystemHealthService $health): JsonResponse
    {
        $this->authorizeAdmin($request);

        $status = $health->codebaseStatus();
        $health->recordConsole('Codebase status: '.$status['latest'].' dirty='.($status['dirty'] ? 'yes' : 'no'));

        return response()->json([
            'success' => $status['ok'],
            'message' => $status['ok']
                ? 'HEAD '.$status['head'].' — '.$status['status']
                : 'Git is not available in this working directory.',
            'status' => $status,
        ]);
    }

    public function cloudMirror(Request $request, SystemHealthService $health): JsonResponse
    {
        $this->authorizeAdmin($request);

        $status = $health->cloudMirrorStatus();
        $health->recordConsole($status['message']);

        return response()->json([
            'success' => $status['ok'],
            'message' => $status['message'],
        ], $status['ok'] ? 200 : 422);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
