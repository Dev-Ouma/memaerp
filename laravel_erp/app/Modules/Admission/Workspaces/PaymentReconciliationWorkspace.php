<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** Reconciliation register: provider statements matched against the fee ledger. */
final class PaymentReconciliationWorkspace extends Workspace
{
    public function stats(): array
    {
        $runs = DB::table('payment_reconciliations');
        $matched = (int) (clone $runs)->sum('matched_count');
        $unmatched = (int) (clone $runs)->sum('unmatched_count');

        return [
            'reconciledTotal' => (float) (clone $runs)->sum('ledger_total'),
            'reconciliationRate' => $this->percentage($matched, $matched + $unmatched).'%',
            'unmatchedDeposits' => $unmatched,
            'pendingDiscrepancies' => DB::table('payment_reconciliation_exceptions')->where('status', 'OPEN')->count(),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = DB::table('payment_reconciliations as pr')
            ->leftJoin('users as runner', 'runner.id', '=', 'pr.run_by')
            ->select([
                'pr.id', 'pr.provider', 'pr.statement_reference', 'pr.period_start', 'pr.period_end',
                'pr.matched_count', 'pr.unmatched_count', 'pr.exception_count',
                'pr.provider_total', 'pr.ledger_total', 'pr.status', 'pr.run_at',
                'runner.name as run_by_name',
            ]);

        if (($provider = $filters['provider'] ?? null) !== null && $provider !== '') {
            $query->where('pr.provider', $provider);
        }
        if (($status = $filters['status'] ?? null) !== null && $status !== '') {
            $query->where('pr.status', $status);
        }
        if (($term = trim((string) ($filters['q'] ?? ''))) !== '') {
            $query->where('pr.statement_reference', 'ilike', '%'.$term.'%');
        }

        return $query->orderByDesc('pr.run_at')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->id,
                'batch_id' => $row->statement_reference ?? strtoupper(substr((string) $row->id, 0, 8)),
                'period' => date('d M', strtotime((string) $row->period_start)).' – '.date('d M Y', strtotime((string) $row->period_end)),
                'source' => $row->provider,
                'erp_sum' => number_format((float) $row->ledger_total, 2),
                'bank_sum' => number_format((float) $row->provider_total, 2),
                'variance' => number_format((float) $row->provider_total - (float) $row->ledger_total, 2),
                'matched_count' => (int) $row->matched_count,
                'unmatched_count' => (int) $row->unmatched_count,
                'status' => ucwords(strtolower((string) $row->status)),
            ]);
    }

    public function providers(): array
    {
        return DB::table('payment_reconciliations')->distinct()->orderBy('provider')->pluck('provider')->all();
    }
}
