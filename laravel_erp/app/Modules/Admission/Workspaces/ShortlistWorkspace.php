<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Merit ranking desk. Rank is derived rather than stored: it is only meaningful
 * relative to the current cohort, so persisting it would go stale the moment a
 * score is moderated.
 */
final class ShortlistWorkspace extends Workspace
{
    public function stats(): array
    {
        $shortlisted = $this->shortlisted();
        $total = (clone $shortlisted)->count();

        // Quota is the capacity of the offerings that actually have a shortlist,
        // not the capacity of the whole catalogue.
        $quota = (int) DB::table('programme_offerings')
            ->whereIn('id', (clone $shortlisted)->distinct()->pluck('po.id'))
            ->sum('capacity');

        $cutOff = (clone $shortlisted)->min('a.eligibility_score');

        return [
            'totalShortlisted' => $total,
            'targetQuota' => $quota,
            'quotaFillRate' => $this->percentage($total, $quota).'%',
            'cutOffMean' => $cutOff !== null ? (string) round((float) $cutOff, 2) : 'Not set',
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $query = $this->shortlisted()
            ->leftJoin('education_history as eh', function ($join): void {
                $join->on('eh.admission_application_id', '=', 'a.id')->where('eh.is_highest', '=', true);
            })
            ->select([
                'a.id as application_id', 'a.application_number', 'a.status as application_status',
                'a.eligibility_score', 'u.name as applicant_name', 'c.name as programme',
                'po.capacity', 'eh.mean_grade',
                DB::raw('rank() over (partition by po.id order by a.eligibility_score desc nulls last, a.application_number asc) as merit_rank'),
            ]);

        if (($offering = $filters['offering'] ?? null) !== null && $offering !== '') {
            $query->where('po.id', $offering);
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderBy('c.name')->orderByRaw('a.eligibility_score desc nulls last')->orderBy('a.application_number')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->application_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'mean_grade' => $row->mean_grade ?? '—',
                'cluster_points' => $row->eligibility_score !== null ? (string) round((float) $row->eligibility_score, 2) : '—',
                'rank' => (int) $row->merit_rank,
                'selection_quota' => (int) ($row->capacity ?? 0),
                'status' => (int) $row->merit_rank <= (int) ($row->capacity ?? 0) ? 'Within Quota' : 'Above Quota Line',
            ]);
    }

    /**
     * An application is shortlisted when its most recent SHORTLIST decision did
     * not reject it, or when the workflow has already carried it past that gate.
     */
    private function shortlisted(): Builder
    {
        return $this->applications()
            ->where(fn (Builder $q) => $q
                ->whereIn('a.status', ['SHORTLISTED', 'APPROVAL_PENDING'])
                ->orWhereExists(fn (Builder $sub) => $sub->from('decisions as d')
                    ->whereColumn('d.admission_application_id', 'a.id')
                    ->where('d.decision_type', 'SHORTLIST')
                    ->where('d.outcome', '!=', 'REJECT')));
    }

    /** Offerings that currently carry a shortlist, for the filter. */
    public function offerings(): array
    {
        return $this->shortlisted()->distinct()->orderBy('c.name')->pluck('c.name', 'po.id')->all();
    }
}
