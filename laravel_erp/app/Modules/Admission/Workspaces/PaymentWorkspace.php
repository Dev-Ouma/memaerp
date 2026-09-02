<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Application-fee ledger. Rows are driven by payment attempts because that is
 * the complete record of intent and outcome; the confirmed provider transaction
 * is joined on where one exists.
 */
final class PaymentWorkspace extends Workspace
{
    private const SETTLED = ['PAID', 'WAIVED'];

    public function stats(): array
    {
        $attempts = DB::table('application_payment_attempts');

        $paid = (clone $attempts)->where('status', 'PAID');
        $paidCount = (clone $paid)->count();
        $mpesaCount = (clone $paid)->where(fn ($q) => $q->where('channel', 'ilike', 'MPESA%')->orWhere('provider', 'ilike', 'MPESA%'))->count();

        return [
            'totalPaidRevenue' => (float) (clone $paid)->sum('amount'),
            'paidTransactions' => $paidCount,
            'mpesaPercentage' => $this->percentage($mpesaCount, $paidCount).'%',
            'waivedApplications' => (clone $attempts)->where('status', 'WAIVED')->count()
                + DB::table('payment_waivers')->where('status', 'ACTIVE')->count(),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->applications()
            ->join('application_payment_attempts as pa', 'pa.admission_application_id', '=', 'a.id')
            ->leftJoin('payment_transactions as pt', 'pt.application_payment_attempt_id', '=', 'pa.id')
            ->select([
                'pa.id as attempt_id', 'pa.reference', 'pa.channel', 'pa.provider', 'pa.amount', 'pa.currency',
                'pa.status as attempt_status', 'pa.paid_at', 'pa.created_at as attempted_at',
                'pa.payer_account_masked', 'pa.payer_msisdn_masked', 'pa.receipt_number',
                'pt.provider_transaction_ref', 'pt.reconciliation_state',
                'a.id as application_id', 'a.application_number',
                'u.name as applicant_name', 'c.name as programme',
            ]);

        if (($status = $filters['status'] ?? null) !== null && $status !== '') {
            $query->where('pa.status', $status);
        }
        if (($channel = $filters['channel'] ?? null) !== null && $channel !== '') {
            $query->where('pa.channel', $channel);
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByRaw('coalesce(pa.paid_at, pa.created_at) desc')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->attempt_id,
                'attempt_id' => $row->attempt_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'transaction_ref' => $row->provider_transaction_ref ?? $row->reference,
                'account_ref' => $row->payer_account_masked ?? $row->payer_msisdn_masked ?? $row->receipt_number ?? '—',
                'channel' => $row->channel ?? $row->provider ?? 'Unknown',
                'amount' => number_format((float) $row->amount, 2).' '.($row->currency ?? 'KES'),
                'timestamp' => date('d M Y H:i', strtotime((string) ($row->paid_at ?? $row->attempted_at))),
                'status' => ucwords(strtolower((string) $row->attempt_status)),
            ]);
    }

    public function channels(): array
    {
        return DB::table('application_payment_attempts')->whereNotNull('channel')
            ->distinct()->orderBy('channel')->pluck('channel')->all();
    }

    public function settledStatuses(): array
    {
        return self::SETTLED;
    }
}
