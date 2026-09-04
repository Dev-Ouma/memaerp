<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ModuleRecord;
use App\Models\ModuleState;
use App\Models\SystemMaintenanceConfig;
use App\Modules\Platform\Rbac\AccessControl;
use App\Modules\Platform\Rbac\ModuleWritePermission;
use App\Support\SoftStatsBag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class OperationalRecordService
{
    /**
     * @param  list<array{key: string, op: string, field?: string, needle?: string}>  $plan
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @param  array<string, mixed>  $extra
     */
    public function screen(
        Request $request,
        string $view,
        string $module,
        string $kind,
        string $listVariable,
        array $plan,
        array $fields,
        array $extra = [],
        string $statsKey = 'stats',
    ): View {
        $rows = $this->rows($module, $kind, $request);
        $computedStats = new SoftStatsBag($this->stats($rows, $plan));
        $payload = [
            $listVariable => $rows,
            'stats' => $computedStats,
            'search' => $request->query('search', $request->query('q')),
            'status' => $request->query('status'),
            'operationalCreate' => [
                'module' => $module,
                'kind' => $kind,
                'fields' => $fields,
                'title' => 'Add database record',
            ],
        ];
        if ($statsKey !== 'stats') {
            $payload[$statsKey] = $computedStats;
        }
        if (isset($extra['stats']) && is_array($extra['stats'])) {
            $payload['stats'] = new SoftStatsBag(array_merge($computedStats->all(), $extra['stats']));
            if ($statsKey !== 'stats') {
                $payload[$statsKey] = $payload['stats'];
            }
            unset($extra['stats']);
        }

        return view($view, array_merge($extra, $payload));
    }

    /** @return list<array<string, mixed>> */
    public function rows(string $module, string $kind, ?Request $request = null): array
    {
        $records = ModuleRecord::query()->where('module', $module)->where('kind', $kind)->latest()->get()
            ->map(fn (ModuleRecord $record): array => $record->toViewRow());
        if ($request?->filled('status')) {
            $needle = strtolower((string) $request->query('status'));
            $records = $records->filter(function (array $row) use ($needle): bool {
                return str_contains(strtolower((string) ($row['status'] ?? '')), $needle)
                    || str_contains(strtolower((string) ($row['status_type'] ?? '')), $needle);
            });
        }
        if ($request?->filled('search')) {
            $needle = strtolower((string) $request->query('search'));
            $records = $records->filter(fn (array $row): bool => str_contains(strtolower((string) json_encode($row)), $needle));
        }

        return $records->values()->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<array{key: string, op: string, field?: string, needle?: string}>  $plan
     * @return array<string, mixed>
     */
    public function stats(array $rows, array $plan): array
    {
        $collection = collect($rows);
        $stats = [];
        foreach ($plan as $item) {
            $stats[$item['key']] = match ($item['op']) {
                'count' => $collection->count(),
                'count_match' => $collection->filter(fn (array $row): bool => $this->matches($row, $item['field'] ?? 'status', $item['needle'] ?? ''))->count(),
                'sum' => $collection->sum(fn (array $row): float => $this->parseMoney($row[$item['field'] ?? 'amount'] ?? 0)),
                'sum_money' => $this->money($collection->sum(fn (array $row): float => $this->parseMoney($row[$item['field'] ?? 'amount'] ?? 0))),
                'percent_match' => $this->percent(
                    $collection->filter(fn (array $row): bool => $this->matches($row, $item['field'] ?? 'status', $item['needle'] ?? ''))->count(),
                    max(1, $collection->count()),
                ),
                'avg_days' => number_format((float) $collection->avg(fn (array $row): float => (float) ($row[$item['field'] ?? 'days'] ?? 0)), 1).' Days',
                default => 0,
            };
        }

        return $stats;
    }

    public function store(Request $request, string $module, string $kind): ModuleRecord
    {
        $this->assertModule($request, $module);
        $payload = $request->except(['_token', '_method', 'module', 'kind']);
        $title = trim((string) ($payload['name'] ?? $payload['title'] ?? $payload['programme'] ?? $payload['student_name'] ?? $payload['applicant_name'] ?? $payload['full_name'] ?? $payload['staff_name'] ?? 'Record'));
        $code = strtoupper(trim((string) ($payload['code'] ?? $payload['account_no'] ?? $payload['app_no'] ?? $payload['reg_no'] ?? $payload['ref'] ?? $payload['provider_code'] ?? $payload['programme'] ?? '')));
        if ($code === '') {
            $code = strtoupper(Str::slug($kind, '-')).'-'.now()->format('YmdHis');
        }
        $status = trim((string) ($payload['status'] ?? 'Active'));
        $record = ModuleRecord::create([
            'module' => $module,
            'kind' => $kind,
            'code' => $code,
            'status' => $status,
            'title' => $title !== '' ? $title : $code,
            'party_name' => $payload['party_name'] ?? $payload['student_name'] ?? $payload['applicant_name'] ?? $payload['name'] ?? null,
            'party_ref' => $payload['party_ref'] ?? $payload['reg_no'] ?? $payload['admission_number'] ?? $payload['app_no'] ?? null,
            'programme' => $payload['programme'] ?? $payload['programme_name'] ?? null,
            'department' => $payload['department'] ?? null,
            'amount' => $this->parseMoney($payload['amount'] ?? $payload['trimester_revenue'] ?? $payload['invoiced_amount'] ?? null) ?: null,
            'starts_on' => $payload['start_date'] ?? $payload['starts_on'] ?? $payload['notification_date'] ?? null,
            'ends_on' => $payload['end_date'] ?? $payload['ends_on'] ?? null,
            'occurred_on' => $payload['occurred_on'] ?? $payload['date'] ?? null,
            'fields' => $payload,
            'created_by' => $request->user()?->id,
        ]);
        AuditLog::record('operational.record_created', $record, null, $record->toArray());

        return $record;
    }

    public function updateStatus(Request $request, ModuleRecord $record, string $status): ModuleRecord
    {
        $this->assertModule($request, $record->module);
        $before = $record->toArray();
        $fields = $record->fields ?? [];
        $fields['status'] = $status;
        $fields['status_type'] = $request->string('status_type')->toString() ?: strtolower($status);
        $record->update([
            'status' => $status,
            'fields' => $fields,
        ]);
        AuditLog::record('operational.record_transitioned', $record, $before, $record->fresh()?->toArray());

        return $record->refresh();
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function parseMoney(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) str_replace([',', 'KES', ' '], '', (string) $value);
    }

    private function matches(array $row, string $field, string $needle): bool
    {
        return str_contains(strtolower((string) ($row[$field] ?? '')), strtolower($needle));
    }

    private function percent(int $part, int $whole): string
    {
        return round(($part / max(1, $whole)) * 100, 2).'%';
    }

    private function assertModule(Request $request, string $module): void
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        try {
            $permission = ModuleWritePermission::forModule($module);
        } catch (\InvalidArgumentException) {
            abort(403, "Writes are not permitted for module [{$module}].");
        }

        abort_unless(
            app(AccessControl::class)->allows($user, $permission),
            403,
            'You do not hold the required permission to write this module.',
        );

        // Platform admins may still write during maintenance recovery.
        if ($user->isAdmin()) {
            return;
        }

        abort_unless(ModuleState::isActive($module), 503, 'This module is currently disabled.');

        $config = SystemMaintenanceConfig::query()->latest('updated_at')->first();
        $locked = is_array($config?->locked_modules) ? $config->locked_modules : [];
        abort_unless(! in_array($module, $locked, true), 503, 'This module is locked for maintenance.');
    }
}
