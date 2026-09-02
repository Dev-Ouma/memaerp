<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeletionActionRequest;
use App\Models\DeletionRecord;
use App\Models\Platform\AuditEvent;
use App\Models\Platform\LegalHold;
use App\Models\Platform\RetentionRule;
use App\Modules\Platform\Audit\AuditRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GovernanceAdminController extends Controller
{
    public function index(Request $request): View
    {
        $rules = RetentionRule::query()->orderBy('subject_type')->orderByDesc('version')->paginate(15, ['*'], 'rules_page');
        $activeHolds = LegalHold::query()->whereNull('released_at')->latest('placed_at')->paginate(15, ['*'], 'holds_page');
        $pendingPurges = DeletionActionRequest::query()->with(['deletionRecord', 'requester'])
            ->where('action', 'purge')->where('status', 'pending')->oldest()->paginate(15, ['*'], 'purges_page');
        $stats = [
            'activeRules' => RetentionRule::query()->whereIn('status', ['ACTIVE', 'SCHEDULED'])->where('is_active', true)
                ->whereDate('effective_from', '<=', now())->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', now()))->count(),
            'activeHolds' => LegalHold::query()->whereNull('released_at')->count(),
            'pendingPurges' => DeletionActionRequest::query()->where('action', 'purge')->where('status', 'pending')->count(),
            'auditEvents' => AuditEvent::query()->count(),
        ];

        return view('admin.governance.index', compact('rules', 'activeHolds', 'pendingPurges', 'stats'));
    }

    public function storeRetentionRule(Request $request, AuditRecorder $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9_-]+$/'],
            'subject_type' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
            'retention_months' => ['required', 'integer', 'min:1', 'max:1200'],
            'disposal_action' => ['required', Rule::in(['ARCHIVE', 'PURGE', 'ANONYMISE'])],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $data, $audit): void {
            $latest = RetentionRule::query()->where('code', $data['code'])->lockForUpdate()->orderByDesc('version')->first();
            $effectiveFrom = CarbonImmutable::parse($data['effective_from'])->startOfDay();
            if ($latest?->effective_from && $effectiveFrom->lessThanOrEqualTo($latest->effective_from)) {
                abort(422, 'A new retention version must start after the previous version.');
            }
            if ($latest && ($latest->effective_to === null || $latest->effective_to->greaterThanOrEqualTo($effectiveFrom))) {
                $latest->update([
                    'effective_to' => $effectiveFrom->copy()->subDay(),
                    'status' => $effectiveFrom->isFuture() ? $latest->status : 'INACTIVE',
                ]);
            }

            $rule = RetentionRule::create([
                ...$data,
                'version' => ($latest?->version ?? 0) + 1,
                'status' => $effectiveFrom->isFuture() ? 'SCHEDULED' : 'ACTIVE',
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
            $audit->record('retention_rule.version_published', [
                'actor_role' => $request->user()->activeRole(), 'subject_type' => RetentionRule::class,
                'subject_id' => $rule->id, 'before' => $latest?->toArray(), 'after' => $rule->toArray(),
                'classification' => 'confidential',
            ]);
        });

        return back()->with('success', 'Effective-dated retention version published.');
    }

    public function placeHold(Request $request, AuditRecorder $audit): RedirectResponse
    {
        $data = $request->validate([
            'deletion_record_id' => ['required', 'uuid', 'exists:deletion_records,id'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);
        $deletion = DeletionRecord::query()->findOrFail($data['deletion_record_id']);
        $exists = LegalHold::query()->where('subject_type', $deletion->model_type)
            ->where('subject_id', $deletion->record_id)->whereNull('released_at')->exists();
        abort_if($exists, 422, 'An active legal hold already exists for this record.');

        $hold = LegalHold::create([
            'subject_type' => $deletion->model_type, 'subject_id' => $deletion->record_id,
            'reason' => $data['reason'], 'placed_by' => $request->user()->id, 'placed_at' => now(),
        ]);
        $audit->record('legal_hold.placed', [
            'actor_role' => $request->user()->activeRole(), 'subject_type' => $deletion->model_type,
            'subject_id' => $deletion->record_id, 'after' => $hold->toArray(), 'classification' => 'restricted',
        ]);

        return back()->with('success', 'Legal hold placed. Permanent purge is now blocked.');
    }

    public function releaseHold(Request $request, LegalHold $hold, AuditRecorder $audit): RedirectResponse
    {
        $data = $request->validate(['release_reason' => ['required', 'string', 'min:10', 'max:500']]);
        abort_if($hold->released_at !== null, 422, 'This legal hold has already been released.');
        $before = $hold->toArray();
        $hold->update(['released_by' => $request->user()->id, 'released_at' => now()]);
        $audit->record('legal_hold.released', [
            'actor_role' => $request->user()->activeRole(), 'subject_type' => $hold->subject_type,
            'subject_id' => $hold->subject_id, 'before' => $before,
            'after' => [...$hold->fresh()->toArray(), 'release_reason' => $data['release_reason']], 'classification' => 'restricted',
        ]);

        return back()->with('success', 'Legal hold released with an audited reason.');
    }

    public function audit(Request $request): View
    {
        $data = $request->validate([
            'action' => ['nullable', 'string', 'max:120'], 'subject_type' => ['nullable', 'string', 'max:120'],
            'actor_user_id' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $events = AuditEvent::query()
            ->when($data['action'] ?? null, fn ($query, $value) => $query->where('action', 'ilike', "%{$value}%"))
            ->when($data['subject_type'] ?? null, fn ($query, $value) => $query->where('subject_type', 'ilike', "%{$value}%"))
            ->when($data['actor_user_id'] ?? null, fn ($query, $value) => $query->where('actor_user_id', $value))
            ->when($data['from'] ?? null, fn ($query, $value) => $query->whereDate('occurred_at', '>=', $value))
            ->when($data['to'] ?? null, fn ($query, $value) => $query->whereDate('occurred_at', '<=', $value))
            ->orderByDesc('sequence_no')->paginate(25)->withQueryString();

        return view('admin.governance.audit', compact('events'));
    }
}
