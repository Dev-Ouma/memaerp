<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Triage pipeline: every open review assignment, ordered so the work closest to
 * breaching its SLA surfaces first.
 */
final class WorkQueueWorkspace extends Workspace
{
    private const OPEN_STATUSES = ['PENDING', 'IN_PROGRESS', 'DELEGATED'];

    /** Application states that constitute triage work whether or not anyone owns them. */
    private const TRIAGE_STATUSES = ['SUBMITTED', 'UNDER_REVIEW'];

    public function stats(): array
    {
        $open = DB::table('review_assignments')->whereIn('status', self::OPEN_STATUSES);

        $urgent = (clone $open)->whereNotNull('due_at')->where('due_at', '<=', now()->addDay())->count();
        $inReview = (clone $open)->count();

        // Submitted work nobody owns yet is the true triage backlog: it is
        // invisible in the assignment table precisely because it is unassigned.
        $pendingTriage = $this->applications()
            ->whereIn('a.status', ['SUBMITTED', 'UNDER_REVIEW'])
            ->whereNotExists(fn (Builder $q) => $q->from('review_assignments as ra')
                ->whereColumn('ra.admission_application_id', 'a.id')
                ->whereIn('ra.status', self::OPEN_STATUSES))
            ->count();

        $avgSeconds = (float) DB::table('review_assignments')
            ->where('status', 'COMPLETED')
            ->whereNotNull('completed_at')
            ->avg(DB::raw('extract(epoch from (completed_at - created_at))'));

        return [
            'urgentSLA' => $urgent,
            'pendingTriage' => $pendingTriage,
            'inReviewQueue' => $inReview,
            'avgResolutionTime' => $avgSeconds > 0 ? round($avgSeconds / 86400, 1).' Days' : 'No data',
            'reviewerCount' => (int) DB::table('review_assignments')->whereIn('status', self::OPEN_STATUSES)->distinct()->count('assignee_id'),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        // Left-joined, not inner-joined: unassigned triage work is the backlog
        // the Auto-Assign action exists to clear, so it has to be visible here.
        $query = $this->applications()
            ->leftJoin('review_assignments as ra', fn (JoinClause $join) => $join
                ->on('ra.admission_application_id', '=', 'a.id')
                ->whereIn('ra.status', self::OPEN_STATUSES))
            ->leftJoin('users as assignee', 'assignee.id', '=', 'ra.assignee_id')
            ->where(fn (Builder $q) => $q
                ->whereNotNull('ra.id')
                ->orWhereIn('a.status', self::TRIAGE_STATUSES))
            ->select([
                'ra.id as assignment_id', 'ra.status as assignment_status', 'ra.completed_at', 'ra.role_code',
                DB::raw("coalesce(ra.stage, 'triage') as stage"),
                DB::raw('coalesce(ra.priority, 6) as priority'),
                DB::raw('coalesce(ra.due_at, a.sla_due_at) as due_at'),
                'a.id as application_id', 'a.application_number', 'a.status as application_status',
                'u.name as applicant_name', 'c.name as programme', 'assignee.name as assignee_name',
            ]);

        if (($stage = $filters['queue'] ?? null) !== null && $stage !== '') {
            $query->where('ra.stage', $stage);
        }
        if (($priority = $filters['priority'] ?? null) !== null && $priority !== '') {
            $query->whereBetween(DB::raw('coalesce(ra.priority, 6)'), match ($priority) {
                'Urgent' => [0, 2],
                'High' => [3, 5],
                default => [6, 32767],
            });
        }
        $this->applySearch($query, $filters['q'] ?? null);

        // Nulls last keeps un-dated assignments below live SLA work.
        return $query->orderByRaw('coalesce(ra.due_at, a.sla_due_at) asc nulls last')
            ->orderByRaw('coalesce(ra.priority, 6) asc')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->assignment_id ?? $row->application_id,
                'assignment_id' => $row->assignment_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'priority' => $this->priorityLabel((int) $row->priority),
                'queue_type' => $this->stageLabel($row->stage),
                'assigned_to' => $row->assignee_name === null
                    ? 'Unassigned'
                    : $row->assignee_name.($row->role_code !== null ? ' ('.$this->stageLabel($row->role_code).')' : ''),
                'sla_status' => $this->slaStatus($row->due_at, $row->completed_at),
                'status' => ucwords(strtolower(str_replace('_', ' ', $row->application_status))),
            ]);
    }

    /** Distinct stages present in the data, for the queue filter. */
    public function stages(): array
    {
        return DB::table('review_assignments')->distinct()->orderBy('stage')->pluck('stage')
            ->mapWithKeys(fn (string $stage): array => [$stage => $this->stageLabel($stage)])
            ->all();
    }
}
