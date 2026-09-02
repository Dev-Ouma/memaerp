<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Document vetting desk: one row per application, aggregating the state of the
 * evidence bundle rather than the individual files.
 */
final class DocumentVerificationWorkspace extends Workspace
{
    public function stats(): array
    {
        $documents = DB::table('application_documents')->whereNull('deleted_at');

        $pending = (clone $documents)->where('verification_status', 'PENDING')->count();
        $verified = (clone $documents)->where('verification_status', 'VERIFIED')->count();
        $rejected = (clone $documents)->where('verification_status', 'REJECTED')->count();
        $decided = $verified + $rejected;

        return [
            'pendingVerification' => $pending,
            'verifiedToday' => DB::table('document_verifications')
                ->where('outcome', 'VERIFIED')
                ->whereDate('verified_at', today())
                ->count(),
            'authenticityRate' => $this->percentage($verified, $decided).'%',
            // Anything the scanner quarantined or a verifier bounced back needs
            // an external awarding-body check before it can move.
            'knecEscalations' => (clone $documents)
                ->where(fn ($q) => $q->whereIn('scan_status', ['FLAGGED', 'QUARANTINED'])
                    ->orWhere('verification_status', 'REJECTED'))
                ->count(),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->applications()
            ->leftJoin('application_documents as d', function ($join): void {
                $join->on('d.admission_application_id', '=', 'a.id')->whereNull('d.deleted_at');
            })
            ->leftJoin('education_history as eh', function ($join): void {
                $join->on('eh.admission_application_id', '=', 'a.id')->where('eh.is_highest', '=', true);
            })
            ->whereIn('a.status', self::LIVE_STATUSES)
            ->groupBy('a.id', 'a.application_number', 'a.status', 'u.name', 'c.name', 'eh.qualification_name', 'eh.mean_grade')
            ->select([
                'a.id as application_id', 'a.application_number', 'a.status as application_status',
                'u.name as applicant_name', 'c.name as programme',
                'eh.qualification_name', 'eh.mean_grade',
                DB::raw('count(d.id) as docs_total'),
                DB::raw("count(d.id) filter (where d.verification_status = 'VERIFIED') as docs_verified"),
                DB::raw("count(d.id) filter (where d.verification_status = 'REJECTED') as docs_rejected"),
                DB::raw("count(d.id) filter (where d.scan_status in ('FLAGGED','QUARANTINED')) as docs_flagged"),
                DB::raw('max(d.updated_at) as last_activity'),
            ]);

        if (($status = $filters['status'] ?? null) !== null && $status !== '') {
            $query->havingRaw(match ($status) {
                'Verified' => "count(d.id) > 0 and count(d.id) filter (where d.verification_status = 'VERIFIED') = count(d.id)",
                'Rejected' => "count(d.id) filter (where d.verification_status = 'REJECTED') > 0",
                'Flagged' => "count(d.id) filter (where d.scan_status in ('FLAGGED','QUARANTINED')) > 0",
                default => "count(d.id) filter (where d.verification_status = 'PENDING') > 0",
            });
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByRaw('max(d.updated_at) desc nulls last')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->application_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'qualification' => $row->qualification_name !== null
                    ? $row->qualification_name.($row->mean_grade !== null ? ' — '.$row->mean_grade : '')
                    : 'Not declared',
                'docs_uploaded' => (int) $row->docs_verified.' / '.(int) $row->docs_total,
                'authenticity_check' => $this->authenticityLabel($row),
                'status' => $this->bundleStatus($row),
            ]);
    }

    private function authenticityLabel(object $row): string
    {
        return match (true) {
            (int) $row->docs_flagged > 0 => 'Scanner flagged',
            (int) $row->docs_total === 0 => 'Nothing uploaded',
            (int) $row->docs_verified === (int) $row->docs_total => 'Authenticated',
            default => 'Awaiting check',
        };
    }

    private function bundleStatus(object $row): string
    {
        return match (true) {
            (int) $row->docs_total === 0 => 'No Documents',
            (int) $row->docs_rejected > 0 => 'Rejected',
            (int) $row->docs_verified === (int) $row->docs_total => 'Verified',
            (int) $row->docs_verified > 0 => 'Partially Verified',
            default => 'Pending Verification',
        };
    }
}
