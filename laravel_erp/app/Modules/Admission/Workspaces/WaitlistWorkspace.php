<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Waitlist desk: who is holding, in what order, and how much room has opened up
 * for them in the offerings they are waiting on.
 */
final class WaitlistWorkspace extends Workspace
{
    public function stats(): array
    {
        $waitlisted = $this->waitlisted();
        $total = (clone $waitlisted)->count();

        $vacancies = (int) DB::table('programme_offerings')
            ->selectRaw('coalesce(sum(greatest(capacity - coalesce(confirmed_seats, 0), 0)), 0) as free')
            ->value('free');

        $promoted = DB::table('application_status_history')
            ->where('from_status', 'WAITLISTED')
            ->whereNotIn('to_status', ['WAITLISTED', 'WITHDRAWN', 'REJECTED'])
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        $avgWaitSeconds = (float) (clone $waitlisted)
            ->avg(DB::raw('extract(epoch from (now() - coalesce(a.decision_at, a.submitted_at, a.created_at)))'));

        return [
            'totalWaitlisted' => $total,
            'availableVacancies' => $vacancies,
            'promotedThisWeek' => $promoted,
            'avgWaitDays' => $avgWaitSeconds > 0 ? round($avgWaitSeconds / 86400, 1) : 0,
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->waitlisted()
            ->leftJoin('decisions as wd', function ($join): void {
                $join->on('wd.admission_application_id', '=', 'a.id')->where('wd.outcome', '=', 'WAITLIST');
            })
            ->select([
                'a.id as application_id', 'a.application_number', 'a.eligibility_score',
                'a.decision_at', 'a.submitted_at', 'a.created_at',
                'u.name as applicant_name', 'c.name as programme', 'po.capacity',
                DB::raw('max(wd.decided_at) as waitlisted_at'),
                DB::raw('max(wd.reason_code) as reason_code'),
                DB::raw('rank() over (partition by po.id order by a.eligibility_score desc nulls last, a.application_number asc) as waitlist_rank'),
            ])
            ->groupBy('a.id', 'a.application_number', 'a.eligibility_score', 'a.decision_at', 'a.submitted_at',
                'a.created_at', 'u.name', 'c.name', 'po.capacity', 'po.id');

        if (($offering = $filters['offering'] ?? null) !== null && $offering !== '') {
            $query->where('po.id', $offering);
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderBy('c.name')->orderByRaw('a.eligibility_score desc nulls last')
            ->paginate(20)
            ->through(function (object $row): array {
                $waitlistedAt = $row->waitlisted_at ?? $row->decision_at ?? $row->submitted_at ?? $row->created_at;

                return [
                    'id' => $row->application_id,
                    'application_id' => $row->application_id,
                    'app_no' => $row->application_number,
                    'applicant_name' => $row->applicant_name,
                    'programme' => $row->programme,
                    'waitlist_rank' => (int) $row->waitlist_rank,
                    'cluster_score' => $row->eligibility_score !== null ? (string) round((float) $row->eligibility_score, 2) : '—',
                    'date_waitlisted' => $waitlistedAt !== null ? date('d M Y', strtotime((string) $waitlistedAt)) : '—',
                    'reason' => $row->reason_code !== null
                        ? ucwords(str_replace('_', ' ', (string) $row->reason_code))
                        : 'Quota exhausted',
                    'status' => 'Holding',
                ];
            });
    }

    private function waitlisted(): Builder
    {
        return $this->applications()->where('a.status', 'WAITLISTED');
    }

    public function offerings(): array
    {
        return $this->waitlisted()->distinct()->orderBy('c.name')->pluck('c.name', 'po.id')->all();
    }
}
