<?php

declare(strict_types=1);

namespace App\Modules\Admission\Setups;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupUsage;
use App\Models\Admission\AdminSetupVersion;
use App\Modules\Platform\Api\ApiException;
use Illuminate\Support\Facades\Cache;

final class SetupResolver
{
    public function active(string $key, ?\DateTimeInterface $at = null): AdminSetupVersion
    {
        $date = ($at ?? now())->format('Y-m-d');

        return Cache::remember("admission.setup.{$key}.{$date}", now()->addMinutes(10), function () use ($key, $date): AdminSetupVersion {
            $definition = AdminSetupDefinition::query()->where('setup_key', $key)->first();
            $version = $definition?->versions()->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $date)
                ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date))
                ->orderByDesc('version')->first();
            if ($version === null) {
                throw ApiException::make(503, 'CONFIGURATION_MISSING', 'Required admission configuration is unavailable.', "No active effective version exists for {$key}.");
            }

            return $version;
        });
    }

    public function use(string $key, string $consumerType, string $consumerId, string $purpose): AdminSetupVersion
    {
        $version = $this->active($key);
        AdminSetupUsage::firstOrCreate(['admin_setup_version_id' => $version->id, 'consumer_type' => $consumerType, 'consumer_id' => $consumerId, 'purpose' => $purpose],
            ['used_at' => now(), 'correlation_id' => correlation_id()]);

        return $version;
    }

    public function forget(string $key): void
    {
        Cache::forget("admission.setup.{$key}.".now()->format('Y-m-d'));
    }
}
