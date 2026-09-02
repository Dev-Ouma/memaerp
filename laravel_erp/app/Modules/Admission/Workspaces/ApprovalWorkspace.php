<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Approval chain desk. Each application carries an ordered ladder of
 * approval_steps; the screen collapses that ladder into the two signatures that
 * gate an admission.
 */
final class ApprovalWorkspace extends Workspace
{
    public const ROLE_DEAN = 'DEAN';

    public const ROLE_BOARD = 'SENATE_BOARD';

    /** The ladder created when an application enters the approval gate. */
    public const LADDER = [self::ROLE_DEAN, self::ROLE_BOARD];

    public function stats(): array
    {
        $steps = DB::table('approval_steps');

        return [
            'awaitingSignoff' => $this->applications()->where('a.status', 'APPROVAL_PENDING')->count(),
            'approvedThisSession' => (clone $steps)->where('status', 'APPROVED')->whereDate('acted_at', today())->count(),
            'conditionalAdmissions' => $this->applications()->where('a.status', 'ADMITTED_CONDITIONAL')->count(),
            'rejectedVerdicts' => (clone $steps)->where('status', 'REJECTED')->count(),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $step = fn (string $role): string => "max(s.status) filter (where s.role_code = '{$role}')";

        $query = $this->pending()
            ->leftJoin('approval_steps as s', 's.admission_application_id', '=', 'a.id')
            ->groupBy('a.id', 'a.application_number', 'a.status', 'u.name', 'c.name', 'ai.name')
            ->select([
                'a.id as application_id', 'a.application_number', 'a.status as application_status',
                'u.name as applicant_name', 'c.name as programme', 'ai.name as intake_name',
                DB::raw($step(self::ROLE_DEAN).' as dean_status'),
                DB::raw($step(self::ROLE_BOARD).' as board_status'),
                DB::raw('max(s.acted_at) as last_action_at'),
            ]);

        if (($stage = $filters['stage'] ?? null) !== null && $stage !== '') {
            $query->havingRaw($step($stage === 'board' ? self::ROLE_BOARD : self::ROLE_DEAN)." = 'PENDING'");
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByRaw('max(s.acted_at) desc nulls first')->orderBy('a.application_number')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->application_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'intake_name' => $row->intake_name ?? 'Unassigned intake',
                'dean_recommendation' => $this->verdict($row->dean_status),
                'board_resolution' => $this->verdict($row->board_status),
                'status' => ucwords(strtolower(str_replace('_', ' ', $row->application_status))),
            ]);
    }

    /** Applications sitting at, or already past, the approval gate. */
    private function pending(): Builder
    {
        return $this->applications()->whereIn('a.status', ['APPROVAL_PENDING', 'ADMITTED_CONDITIONAL', 'ADMITTED']);
    }

    private function verdict(?string $status): string
    {
        return match ($status) {
            'APPROVED' => 'Recommended',
            'REJECTED' => 'Not recommended',
            'SKIPPED' => 'Skipped',
            'PENDING' => 'Awaiting signature',
            default => 'Not started',
        };
    }
}
