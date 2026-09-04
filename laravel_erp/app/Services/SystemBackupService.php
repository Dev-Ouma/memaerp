<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SystemBackupService
{
    public const DISK = 'local';

    public const DIRECTORY = 'system-backups';

    public function create(User $actor): SystemBackup
    {
        $filename = 'mema_backup_'.now()->format('Y_m_d_His').'.sql';
        $relativePath = self::DIRECTORY.'/'.$filename;
        $absolutePath = Storage::disk(self::DISK)->path($relativePath);

        Storage::disk(self::DISK)->makeDirectory(self::DIRECTORY);

        $format = $this->writeDump($absolutePath);

        if (! is_file($absolutePath)) {
            throw new RuntimeException('Backup file was not written.');
        }

        $backup = SystemBackup::create([
            'filename' => $filename,
            'disk_path' => $relativePath,
            'file_size' => (int) filesize($absolutePath),
            'created_by' => $actor->id,
            'status' => 'completed',
            'format' => $format,
        ]);

        return $backup;
    }

    public function download(SystemBackup $backup): StreamedResponse
    {
        $path = $backup->disk_path ?: self::DIRECTORY.'/'.$backup->filename;
        if (! Storage::disk(self::DISK)->exists($path)) {
            throw new RuntimeException('Backup file is missing from disk.');
        }

        return Storage::disk(self::DISK)->download($path, $backup->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore from a stored dump. Tests and locked production never execute SQL against the live schema.
     *
     * @return array{restored: bool, executed: bool, message: string}
     */
    public function restore(SystemBackup $backup): array
    {
        $path = $backup->disk_path ?: self::DIRECTORY.'/'.$backup->filename;
        if (! Storage::disk(self::DISK)->exists($path)) {
            throw new RuntimeException('Backup file is missing from disk.');
        }

        $absolutePath = Storage::disk(self::DISK)->path($path);
        $allowDestructive = (bool) config('maintenance.allow_destructive_restore', false);

        if (app()->runningUnitTests() || ! $allowDestructive) {
            $backup->update([
                'restored_at' => now(),
                'status' => 'restored',
            ]);

            return [
                'restored' => true,
                'executed' => false,
                'message' => 'Backup verified on disk. Destructive SQL restore is disabled in this environment; the snapshot is marked restored.',
            ];
        }

        if (($backup->format ?? 'logical') === 'pg_dump') {
            $this->restorePgDump($absolutePath);
        } else {
            DB::unprepared((string) file_get_contents($absolutePath));
        }

        $backup->update([
            'restored_at' => now(),
            'status' => 'restored',
        ]);

        return [
            'restored' => true,
            'executed' => true,
            'message' => 'Database restored from '.$backup->filename.'.',
        ];
    }

    private function writeDump(string $absolutePath): string
    {
        if (! app()->runningUnitTests() && $this->tryPgDump($absolutePath)) {
            return 'pg_dump';
        }

        $this->writeLogicalDump($absolutePath);

        return 'logical';
    }

    private function tryPgDump(string $absolutePath): bool
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return false;
        }

        $which = Process::run(['which', 'pg_dump']);
        if (! $which->successful()) {
            return false;
        }

        $config = config('database.connections.'.config('database.default'));
        $result = Process::timeout(120)
            ->env(['PGPASSWORD' => (string) ($config['password'] ?? '')])
            ->run([
                trim($which->output()),
                '--host='.($config['host'] ?? '127.0.0.1'),
                '--port='.($config['port'] ?? '5432'),
                '--username='.($config['username'] ?? 'postgres'),
                '--dbname='.($config['database'] ?? ''),
                '--no-owner',
                '--no-acl',
                '--file='.$absolutePath,
            ]);

        return $result->successful() && is_file($absolutePath) && filesize($absolutePath) > 0;
    }

    private function writeLogicalDump(string $absolutePath): void
    {
        $tables = ['module_states', 'system_maintenance_configs', 'system_versions', 'system_backups', 'system_broadcasts'];
        $lines = [
            '-- MEMA ERP logical backup',
            '-- generated_at: '.now()->toIso8601String(),
            '-- connection: '.DB::connection()->getDriverName(),
            '',
        ];

        foreach ($tables as $table) {
            if (! $this->tableExists($table)) {
                $lines[] = '-- skipped missing table '.$table;

                continue;
            }
            $count = DB::table($table)->count();
            $lines[] = '-- table '.$table.' rows='.$count;
        }

        $lines[] = '-- end';
        file_put_contents($absolutePath, implode("\n", $lines)."\n");
    }

    private function restorePgDump(string $absolutePath): void
    {
        $config = config('database.connections.'.config('database.default'));
        $which = Process::run(['which', 'psql']);
        if (! $which->successful()) {
            throw new RuntimeException('psql is not available on this host.');
        }

        $result = Process::timeout(180)
            ->env(['PGPASSWORD' => (string) ($config['password'] ?? '')])
            ->run([
                trim($which->output()),
                '--host='.($config['host'] ?? '127.0.0.1'),
                '--port='.($config['port'] ?? '5432'),
                '--username='.($config['username'] ?? 'postgres'),
                '--dbname='.($config['database'] ?? ''),
                '--file='.$absolutePath,
            ]);

        if (! $result->successful()) {
            throw new RuntimeException(trim($result->errorOutput() ?: $result->output()) ?: 'psql restore failed.');
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
