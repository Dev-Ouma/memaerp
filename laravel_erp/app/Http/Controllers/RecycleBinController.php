<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeletionActionRequest;
use App\Models\DeletionRecord;
use App\Services\RecycleBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RecycleBinController extends Controller
{
    private const ENTITY_LABELS = [
        'school' => ['label' => 'Academic School', 'icon' => 'building-2', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'department' => ['label' => 'Department', 'icon' => 'network', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
        'programme' => ['label' => 'Degree Programme', 'icon' => 'graduation-cap', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
        'course_unit' => ['label' => 'Course Unit', 'icon' => 'book-open', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200'],
        'cohort_year' => ['label' => 'Academic Year', 'icon' => 'calendar', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200'],
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::ENTITY_LABELS))],
            'search' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'string', 'in:deleted_at,purge_after,entity_type'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);
        $selectedType = $validated['type'] ?? null;
        $search = trim($validated['search'] ?? '');
        $query = DeletionRecord::query()->where('status', 'deleted');
        if ($selectedType !== null) {
            $query->where('entity_type', $selectedType);
        }
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('entity_type', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereRaw('CAST(snapshot AS TEXT) LIKE ?', ["%{$search}%"]);
            });
        }

        $items = $query->orderBy($validated['sort'] ?? 'deleted_at', $validated['direction'] ?? 'desc')
            ->paginate((int) ($validated['per_page'] ?? 25))->withQueryString();
        $items->through(function (DeletionRecord $record): array {
            $ui = self::ENTITY_LABELS[$record->entity_type] ?? ['label' => $record->entity_type, 'icon' => 'file', 'badge' => 'bg-slate-100 text-slate-800 border-slate-200'];
            $snapshot = $record->snapshot;

            return [
                'id' => $record->id, 'type' => $record->entity_type, 'type_label' => $ui['label'],
                'type_icon' => $ui['icon'], 'type_badge' => $ui['badge'],
                'title' => $snapshot['name'] ?? $snapshot['title'] ?? $snapshot['unit_title'] ?? 'Untitled Item',
                'code' => $snapshot['code'] ?? $snapshot['unit_code'] ?? '#'.$record->record_id,
                'deleted_at' => $record->deleted_at->format('d M Y, h:i A'),
                'days_left' => $record->purge_after ? max(0, (int) now()->diffInDays($record->purge_after, false)) : null,
                'snapshot' => $snapshot, 'reason' => $record->reason, 'deleted_by_role' => $record->deleted_by_role,
            ];
        });

        $counts = DeletionRecord::query()->where('status', 'deleted')->select('entity_type', DB::raw('count(*) as aggregate'))
            ->groupBy('entity_type')->pluck('aggregate', 'entity_type');
        $typeBreakdown = [];
        foreach (self::ENTITY_LABELS as $key => $ui) {
            $typeBreakdown[$key] = ['count' => (int) ($counts[$key] ?? 0), 'label' => $ui['label'], 'icon' => $ui['icon']];
        }
        $stats = [
            'totalDeleted' => (int) $counts->sum(), 'storageReclaimed' => 'Not estimated',
            'retentionPolicy' => 'Database governed',
            'expiringSoon' => DeletionRecord::query()->where('status', 'deleted')->whereBetween('purge_after', [now(), now()->addDays(7)])->count(),
        ];

        return view('admin.setups.recycle-bin', compact('stats', 'items', 'typeBreakdown', 'selectedType', 'search'));
    }

    public function restore(Request $request, DeletionRecord $deletion, RecycleBinService $service): RedirectResponse|JsonResponse
    {
        $service->restore($deletion, $request->user());

        return $this->respond($request, 'Record restored with its original attributes.');
    }

    public function requestPurge(Request $request, DeletionRecord $deletion, RecycleBinService $service): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);
        $action = $service->requestPurge($deletion, $request->user(), $validated['reason']);

        return $this->respond($request, "Purge request {$action->id} is awaiting an independent checker.");
    }

    public function approvePurge(Request $request, DeletionActionRequest $action, RecycleBinService $service): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['decision_note' => ['required', 'string', 'min:10', 'max:500']]);
        $service->approvePurge($action, $request->user(), $validated['decision_note']);

        return $this->respond($request, 'Permanent purge approved and recorded in the audit trail.');
    }

    public function emptyBin(): never
    {
        abort(403, 'Bulk permanent deletion is disabled. Each record requires retention and maker-checker review.');
    }

    public function restoreAll(): never
    {
        abort(422, 'Bulk restoration is disabled because each record requires dependency and conflict checks.');
    }

    private function respond(Request $request, string $message): RedirectResponse|JsonResponse
    {
        return $request->wantsJson() ? response()->json(['success' => true, 'message' => $message]) : redirect()->back()->with('success', $message);
    }
}
