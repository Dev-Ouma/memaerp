<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeletionActionRequest;
use App\Models\DeletionRecord;
use App\Models\Platform\LegalHold;
use App\Models\Platform\RetentionRule;
use App\Models\User;
use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RecycleBinService
{
    public function __construct(private AuditRecorder $audit) {}

    public function delete(Model $record, User $actor, string $entityType, string $reason, string $location): DeletionRecord
    {
        if (! in_array(SoftDeletes::class, class_uses_recursive($record), true)) {
            throw ValidationException::withMessages(['record' => 'This record type does not support recoverable deletion.']);
        }

        return DB::transaction(function () use ($record, $actor, $entityType, $reason, $location): DeletionRecord {
            $snapshot = $record->attributesToArray();
            $retention = RetentionRule::query()
                ->where('subject_type', 'curriculum_master_data')
                ->where('is_active', true)
                ->whereIn('status', ['ACTIVE', 'SCHEDULED'])
                ->whereDate('effective_from', '<=', now())
                ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', now()))
                ->orderByDesc('version')
                ->first();

            $record->delete();

            $deletion = DeletionRecord::create([
                'entity_type' => $entityType,
                'model_type' => $record::class,
                'record_id' => (string) $record->getKey(),
                'deleted_by' => $actor->id,
                'deleted_by_role' => $actor->activeRole(),
                'reason' => trim($reason),
                'original_location' => $location,
                'snapshot' => $snapshot,
                'deleted_at' => now(),
                'purge_after' => $retention ? now()->addMonthsNoOverflow($retention->retention_months) : null,
                'retention_rule_id' => $retention?->id,
                'status' => 'deleted',
            ]);

            $this->audit->record('record.soft_deleted', [
                'actor_role' => $actor->activeRole(),
                'subject_type' => $record::class,
                'subject_id' => $record->getKey(),
                'before' => $snapshot,
                'after' => ['deletion_record_id' => $deletion->id, 'reason' => $reason],
                'classification' => 'confidential',
            ]);

            return $deletion;
        });
    }

    public function restore(DeletionRecord $deletion, User $actor): void
    {
        DB::transaction(function () use ($deletion, $actor): void {
            $this->assertPending($deletion);
            $record = $this->trashedModel($deletion);
            $this->assertNoActiveConflict($record);
            $record->restore();
            $deletion->update(['status' => 'restored', 'restored_at' => now(), 'restored_by' => $actor->id]);
            $this->audit->record('record.restored', [
                'actor_role' => $actor->activeRole(),
                'subject_type' => $deletion->model_type,
                'subject_id' => $deletion->record_id,
                'before' => ['deleted' => true],
                'after' => ['deleted' => false, 'deletion_record_id' => $deletion->id],
                'classification' => 'confidential',
            ]);
        });
    }

    public function requestPurge(DeletionRecord $deletion, User $actor, string $reason): DeletionActionRequest
    {
        $this->assertPending($deletion);
        $this->assertPurgeAllowed($deletion);

        return DeletionActionRequest::firstOrCreate(
            ['deletion_record_id' => $deletion->id, 'action' => 'purge', 'status' => 'pending'],
            ['requested_by' => $actor->id, 'reason' => trim($reason)],
        );
    }

    public function approvePurge(DeletionActionRequest $request, User $checker, string $note): void
    {
        if ($request->status !== 'pending' || $request->action !== 'purge') {
            throw ValidationException::withMessages(['request' => 'This purge request is no longer pending.']);
        }
        if ((int) $request->requested_by === (int) $checker->id) {
            throw ValidationException::withMessages(['checker' => 'The purge requester cannot approve their own request.']);
        }

        DB::transaction(function () use ($request, $checker, $note): void {
            $deletion = DeletionRecord::query()->lockForUpdate()->findOrFail($request->deletion_record_id);
            $this->assertPending($deletion);
            $this->assertPurgeAllowed($deletion);
            $record = $this->trashedModel($deletion);
            $record->forceDelete();
            $deletion->update(['status' => 'purged', 'purged_at' => now(), 'purged_by' => $checker->id]);
            $request->update(['status' => 'approved', 'decided_by' => $checker->id, 'decided_at' => now(), 'decision_note' => trim($note)]);
            $this->audit->record('record.permanently_purged', [
                'actor_role' => $checker->activeRole(),
                'subject_type' => $deletion->model_type,
                'subject_id' => $deletion->record_id,
                'before' => $deletion->snapshot,
                'after' => ['deletion_record_id' => $deletion->id, 'purge_request_id' => $request->id],
                'classification' => 'restricted',
            ]);
        });
    }

    private function assertPending(DeletionRecord $deletion): void
    {
        if ($deletion->status !== 'deleted') {
            throw ValidationException::withMessages(['record' => 'This deletion record is no longer active.']);
        }
    }

    private function assertPurgeAllowed(DeletionRecord $deletion): void
    {
        if ($deletion->purge_after === null || $deletion->purge_after->isFuture()) {
            throw ValidationException::withMessages(['retention' => 'The approved retention period has not elapsed.']);
        }
        $held = LegalHold::query()->where('subject_type', $deletion->model_type)
            ->where('subject_id', $deletion->record_id)->whereNull('released_at')->exists();
        if ($held) {
            throw ValidationException::withMessages(['legal_hold' => 'This record is subject to an active legal hold.']);
        }
    }

    private function trashedModel(DeletionRecord $deletion): Model
    {
        $class = $deletion->model_type;
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            throw ValidationException::withMessages(['record' => 'The original record type is unavailable.']);
        }

        return $class::onlyTrashed()->findOrFail($deletion->record_id);
    }

    private function assertNoActiveConflict(Model $trashed): void
    {
        foreach (['code', 'unit_code'] as $key) {
            $value = $trashed->getAttribute($key);
            if ($value !== null && $trashed->newQuery()->where($key, $value)->exists()) {
                throw ValidationException::withMessages(['conflict' => "An active record already uses {$key} '{$value}'."]);
            }
        }
    }
}
