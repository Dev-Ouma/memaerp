<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemVersion;
use Illuminate\Support\Facades\Artisan;

final class SystemUpgradeService
{
    /**
     * @param  'major'|'minor'|'patch'  $type
     */
    public function apply(string $type, string $changelog): SystemVersion
    {
        $latest = SystemVersion::query()->latest('installed_at')->first();
        $next = $this->bump($latest?->version ?? '1.0.0', $type);

        $migrateOutput = '';
        if (! app()->runningUnitTests()) {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
        }

        SystemVersion::query()->where('is_current', true)->update(['is_current' => false]);

        $notes = trim($changelog);
        if ($migrateOutput !== '') {
            $notes .= "\n\nMigrate:\n".$migrateOutput;
        }

        return SystemVersion::create([
            'version' => $next,
            'type' => $type,
            'changelog' => $notes,
            'installed_at' => now(),
            'is_current' => true,
        ]);
    }

    public function rollback(SystemVersion $version): SystemVersion
    {
        $version->update([
            'rolled_back_at' => now(),
            'is_current' => false,
        ]);

        $previous = SystemVersion::query()
            ->whereNull('rolled_back_at')
            ->where('id', '!=', $version->id)
            ->latest('installed_at')
            ->first();

        if ($previous) {
            SystemVersion::query()->where('is_current', true)->update(['is_current' => false]);
            $previous->update(['is_current' => true]);
        }

        return $version->fresh();
    }

    public function current(): ?SystemVersion
    {
        return SystemVersion::query()->where('is_current', true)->latest('installed_at')->first()
            ?? SystemVersion::query()->whereNull('rolled_back_at')->latest('installed_at')->first();
    }

    /**
     * @param  'major'|'minor'|'patch'  $type
     */
    private function bump(string $version, string $type): string
    {
        $parts = array_map('intval', explode('.', preg_replace('/[^0-9.]/', '', $version) ?: '1.0.0'));
        $major = $parts[0] ?? 1;
        $minor = $parts[1] ?? 0;
        $patch = $parts[2] ?? 0;

        return match ($type) {
            'major' => ($major + 1).'.0.0',
            'minor' => $major.'.'.($minor + 1).'.0',
            default => $major.'.'.$minor.'.'.($patch + 1),
        };
    }
}
