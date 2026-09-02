<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Academic review desk: submitted reviews with their per-criterion breakdown.
 * The three columns the screen shows map to fixed rubric criterion codes so a
 * rubric can gain criteria without breaking the table.
 */
final class ReviewWorkspace extends Workspace
{
    public const CRITERION_ACADEMIC_MERIT = 'ACADEMIC_MERIT';

    public const CRITERION_PREREQUISITES = 'PREREQUISITES';

    public const CRITERION_SOP_INTERVIEW = 'SOP_INTERVIEW';

    public function stats(): array
    {
        $reviews = DB::table('application_reviews')->where('status', 'SUBMITTED');

        return [
            'inReview' => DB::table('review_assignments')->whereIn('status', ['PENDING', 'IN_PROGRESS'])->count(),
            'activeReviewers' => (int) DB::table('review_assignments')
                ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                ->distinct()->count('assignee_id'),
            'completedToday' => (clone $reviews)->whereDate('created_at', today())->count(),
            'avgScore' => round((float) (clone $reviews)->avg(DB::raw('coalesce(moderated_score, weighted_score, total_score, score)')), 1),
        ];
    }

    public function rows(array $filters): LengthAwarePaginator
    {
        $criterion = fn (string $code): string => "max(rs.raw_score) filter (where sc.code = '{$code}')";

        $query = $this->applications()
            ->join('application_reviews as r', 'r.admission_application_id', '=', 'a.id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'r.reviewer_id')
            ->leftJoin('review_scores as rs', 'rs.application_review_id', '=', 'r.id')
            ->leftJoin('scoring_criteria as sc', 'sc.id', '=', 'rs.scoring_criteria_id')
            ->groupBy('r.id', 'a.id', 'a.application_number', 'u.name', 'c.name', 'dept.name', 'reviewer.name')
            ->select([
                'r.id as review_id', 'r.recommendation', 'r.stage', 'r.created_at as reviewed_at',
                'a.id as application_id', 'a.application_number',
                'u.name as applicant_name', 'c.name as programme',
                'dept.name as department', 'reviewer.name as reviewer_name',
                DB::raw($criterion(self::CRITERION_ACADEMIC_MERIT).' as academic_merit'),
                DB::raw($criterion(self::CRITERION_PREREQUISITES).' as prereq_score'),
                DB::raw($criterion(self::CRITERION_SOP_INTERVIEW).' as sop_interview'),
                DB::raw('max(coalesce(r.moderated_score, r.weighted_score, r.total_score, r.score)) as total_score'),
            ]);

        if (($recommendation = $filters['recommendation'] ?? null) !== null && $recommendation !== '') {
            $query->where('r.recommendation', $recommendation);
        }
        if (($stage = $filters['stage'] ?? null) !== null && $stage !== '') {
            $query->where('r.stage', $stage);
        }
        $this->applySearch($query, $filters['q'] ?? null);

        return $query->orderByDesc('r.created_at')
            ->paginate(20)
            ->through(fn (object $row): array => [
                'id' => $row->review_id,
                'application_id' => $row->application_id,
                'app_no' => $row->application_number,
                'applicant_name' => $row->applicant_name,
                'programme' => $row->programme,
                'department' => $row->department ?? 'Unassigned',
                'reviewer_name' => $row->reviewer_name ?? 'Unassigned',
                'academic_merit' => $this->score($row->academic_merit),
                'prereq_score' => $this->score($row->prereq_score),
                'sop_interview' => $this->score($row->sop_interview),
                'total_score' => $this->score($row->total_score),
                'recommendation' => $row->recommendation ?? 'Pending',
            ]);
    }

    private function score(mixed $value): string
    {
        return $value === null ? '—' : (string) round((float) $value, 1);
    }
}
