<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Admissions audit trail. audit_logs is append-only at the database level, so
 * this workspace only ever reads; the integrity indicator reports whether those
 * guarantees are still installed rather than asserting them.
 */
final class AuditWorkspace extends Workspace
{
    /** Actions that change an admission outcome or expose protected data. */
    private const HIGH_SEVERITY = ['revoked', 'deleted', 'purged', 'overridden', 'waived', 'impersonat'];

    private const MEDIUM_SEVERITY = ['status_changed', 'verified', 'converted', 'offer', 'payment', 'transition'];

    public function stats(): array
    {
        $logs = $this->admissionLogs();

        return [
            'totalAuditEvents' => (clone $logs)->count(),
            'eventsToday' => (clone $logs)->whereDate('al.occurred_at', today())->count(),
            'actorsActive' => (int) (clone $logs)->whereDate('al.occurred_at', '>=', today()->subDays(7))
                ->distinct()->count('al.actor_user_id'),
            'integrityChain' => $this->appendOnlyGuaranteed() ? 'Append-only enforced' : 'Guards missing',
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->admissionLogs()
            ->leftJoin('users as actor', 'actor.id', '=', 'al.actor_user_id')
            ->leftJoin('admission_applications as app', function ($join): void {
                $join->on('app.id', '=', DB::raw('nullif(al.subject_id, \'\')::uuid'))
                    ->where('al.subject_type', '=', 'App\\Models\\AdmissionApplication');
            })
            ->select([
                'al.id', 'al.action', 'al.subject_type', 'al.subject_id', 'al.ip_address',
                'al.occurred_at', 'al.after', 'actor.name as actor_name', 'actor.role as actor_role',
                'app.application_number',
            ]);

        if (($action = $filters['action'] ?? null) !== null && $action !== '') {
            $query->where('al.action', $action);
        }
        if (($severity = $filters['severity'] ?? null) !== null && $severity !== '') {
            $clauses = $severity === 'High' ? self::HIGH_SEVERITY : self::MEDIUM_SEVERITY;
            $query->where(function ($q) use ($clauses): void {
                foreach ($clauses as $needle) {
                    $q->orWhere('al.action', 'ilike', '%'.$needle.'%');
                }
            });
        }
        if (($term = trim((string) ($filters['q'] ?? ''))) !== '') {
            $query->where(function ($q) use ($term): void {
                $q->where('al.action', 'ilike', '%'.$term.'%')
                    ->orWhere('actor.name', 'ilike', '%'.$term.'%')
                    ->orWhere('app.application_number', 'ilike', '%'.$term.'%');
            });
        }

        return $query->orderByDesc('al.occurred_at')
            ->paginate(25)
            ->through(fn (object $row): array => [
                'id' => $row->id,
                'timestamp' => date('d M Y H:i:s', strtotime((string) $row->occurred_at)),
                'actor' => $row->actor_name !== null
                    ? $row->actor_name.($row->actor_role !== null ? ' ('.ucfirst((string) $row->actor_role).')' : '')
                    : 'System',
                'action' => $row->action,
                'app_no' => $row->application_number ?? '—',
                'description' => $this->describe($row),
                'ip_address' => (string) ($row->ip_address ?? '—'),
                'severity' => $this->severity((string) $row->action),
            ]);
    }

    public function actions(): array
    {
        return $this->admissionLogs()->distinct()->orderBy('al.action')->pluck('al.action')->all();
    }

    private function admissionLogs(): Builder
    {
        return DB::table('audit_logs as al')->where('al.action', 'ilike', 'admission%');
    }

    /** Renders the recorded "after" payload as the sentence staff read. */
    private function describe(object $row): string
    {
        $after = json_decode((string) ($row->after ?? '{}'), true);
        if (! is_array($after) || $after === []) {
            return ucwords(str_replace(['admission.', '_'], ['', ' '], (string) $row->action));
        }

        $parts = [];
        foreach ($after as $key => $value) {
            if (is_scalar($value)) {
                $parts[] = str_replace('_', ' ', (string) $key).': '.$value;
            }
        }

        return $parts === [] ? (string) $row->action : implode(', ', array_slice($parts, 0, 3));
    }

    private function severity(string $action): string
    {
        foreach (self::HIGH_SEVERITY as $needle) {
            if (str_contains($action, $needle)) {
                return 'High';
            }
        }
        foreach (self::MEDIUM_SEVERITY as $needle) {
            if (str_contains($action, $needle)) {
                return 'Medium';
            }
        }

        return 'Low';
    }

    /** The append-only promise is a pair of database triggers; verify they exist. */
    private function appendOnlyGuaranteed(): bool
    {
        return DB::table('pg_trigger as t')
            ->join('pg_class as c', 'c.oid', '=', 't.tgrelid')
            ->where('c.relname', 'audit_logs')
            ->where('t.tgisinternal', false)
            ->count() >= 2;
    }
}
