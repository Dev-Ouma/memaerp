<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** Offer register: every letter issued, its deadline and the applicant's answer. */
final class OfferWorkspace extends Workspace
{
    public function stats(): array
    {
        $offers = DB::table('admission_offers');

        return [
            'totalOffersIssued' => (clone $offers)->count(),
            'acceptedOffers' => (clone $offers)->where('status', 'ACCEPTED')->count(),
            'pendingResponse' => (clone $offers)->whereIn('status', ['ISSUED', 'PENDING', 'SENT'])->whereNull('responded_at')->count(),
            'declinedRevoked' => (clone $offers)->whereIn('status', ['DECLINED', 'REVOKED', 'EXPIRED'])->count(),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->applications()
            ->join('admission_offers as o', 'o.admission_application_id', '=', 'a.id')
            ->select([
                'o.id as offer_id', 'o.offer_number', 'o.status as offer_status', 'o.issued_at',
                'o.expires_at', 'o.verification_token', 'o.responded_at',
                'a.id as application_id', 'a.application_number',
                'u.name as applicant_name', 'c.name as programme',
            ]);

        if (($status = $filters['status'] ?? null) !== null && $status !== '') {
            $query->where('o.status', $status);
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByRaw('o.issued_at desc nulls last')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->offer_id,
                'offer_id' => $row->offer_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'offer_number' => $row->offer_number,
                'issued_at' => $row->issued_at !== null ? date('d M Y', strtotime((string) $row->issued_at)) : 'Not issued',
                'deadline' => $row->expires_at !== null ? date('d M Y', strtotime((string) $row->expires_at)) : 'No deadline',
                // Only the prefix is shown: the full token is the credential that
                // authenticates a letter at the public verification endpoint.
                'verification_token' => substr((string) $row->verification_token, 0, 12).'…',
                'status' => ucwords(strtolower((string) $row->offer_status)),
            ]);
    }

    public function statuses(): array
    {
        return DB::table('admission_offers')->distinct()->orderBy('status')->pluck('status')->all();
    }
}
