<?php

declare(strict_types=1);

namespace App\Modules\Admission\Setups;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;

final class SetupManager
{
    public function __construct(private readonly SetupResolver $resolver, private readonly AuditRecorder $audit) {}

    /** @param array<string, mixed> $configuration */
    public function draft(AdminSetupDefinition $definition, array $configuration, string $reason, ?int $actorId): AdminSetupVersion
    {
        return DB::transaction(function () use ($definition, $configuration, $reason, $actorId): AdminSetupVersion {
            AdminSetupDefinition::query()->whereKey($definition->id)->lockForUpdate()->firstOrFail();
            $next = ((int) $definition->versions()->max('version')) + 1;
            $canonical = json_encode($configuration, JSON_THROW_ON_ERROR);
            $version = $definition->versions()->create(['version' => $next, 'status' => 'DRAFT', 'configuration' => $configuration,
                'checksum' => hash('sha256', $canonical), 'created_by' => $actorId, 'change_reason' => $reason]);
            $this->audit->record('admin_setup.version_created', ['subject_type' => AdminSetupVersion::class, 'subject_id' => $version->id,
                'after' => ['setup_key' => $definition->setup_key, 'version' => $next, 'checksum' => $version->checksum]]);

            return $version;
        });
    }

    public function publish(AdminSetupVersion $version, string $from, ?string $to, int $actorId): AdminSetupVersion
    {
        if ($version->status !== 'DRAFT') {
            throw ApiException::conflict('SETUP_NOT_DRAFT', 'Only a draft setup version can be published.');
        }

        return DB::transaction(function () use ($version, $from, $to, $actorId): AdminSetupVersion {
            $overlap = AdminSetupVersion::query()->where('admin_setup_definition_id', $version->admin_setup_definition_id)->where('status', 'ACTIVE')
                ->whereDate('effective_from', '<=', $to ?? '9999-12-31')->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $from))->exists();
            if ($overlap) {
                throw ApiException::conflict('SETUP_EFFECTIVE_DATES_OVERLAP', 'An active setup version already covers that effective period.');
            }
            $version->update(['status' => 'ACTIVE', 'effective_from' => $from, 'effective_to' => $to, 'published_by' => $actorId, 'published_at' => now()]);
            $this->resolver->forget($version->definition->setup_key);
            $this->audit->record('admin_setup.version_published', ['subject_type' => AdminSetupVersion::class, 'subject_id' => $version->id,
                'after' => ['version' => $version->version, 'effective_from' => $from, 'effective_to' => $to]]);

            return $version->refresh();
        });
    }

    public function changeStatus(AdminSetupVersion $version, string $status, int $actorId): AdminSetupVersion
    {
        if ($status === 'ARCHIVED' && $version->usages()->exists()) {
            throw ApiException::conflict('SETUP_IN_USE', 'A setup version referenced by historical transactions cannot be archived.');
        }
        $version->update(['status' => $status, 'archived_by' => $status === 'ARCHIVED' ? $actorId : null, 'archived_at' => $status === 'ARCHIVED' ? now() : null]);
        $this->resolver->forget($version->definition->setup_key);
        $this->audit->record('admin_setup.status_changed', ['subject_type' => AdminSetupVersion::class, 'subject_id' => $version->id, 'after' => ['status' => $status]]);

        return $version->refresh();
    }
}
