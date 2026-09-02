<?php

declare(strict_types=1);

namespace App\Modules\Admission\Workspaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Base for the admissions staff workspaces. Each workspace renders one Blade
 * screen, so each concrete class owns exactly the two payloads that screen
 * consumes: a KPI band and a filterable row set.
 */
abstract class Workspace
{
    /** Statuses an application has reached once it is no longer an applicant draft. */
    protected const LIVE_STATUSES = [
        'SUBMITTED', 'UNDER_REVIEW', 'INFO_REQUESTED', 'RETURNED_FOR_CORRECTION', 'VERIFIED',
        'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED_CONDITIONAL', 'ADMITTED', 'WAITLISTED',
        'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED',
    ];

    /** Statuses that mean an offer was made and honoured. */
    protected const ADMITTED_STATUSES = ['ADMITTED', 'ADMITTED_CONDITIONAL', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'];

    /** @return array<string, mixed> */
    abstract public function stats(): array;

    /** @param  array<string, mixed>  $filters */
    abstract public function rows(array $filters): LengthAwarePaginator;

    /**
     * Applications joined to the applicant and programme context every workspace
     * table displays. Aliased so concrete queries stay readable.
     */
    protected function applications(): Builder
    {
        return DB::table('admission_applications as a')
            ->join('applicant_profiles as ap', 'ap.id', '=', 'a.applicant_profile_id')
            ->join('users as u', 'u.id', '=', 'ap.user_id')
            ->join('programme_offerings as po', 'po.id', '=', 'a.programme_offering_id')
            ->join('courses as c', 'c.id', '=', 'po.course_id')
            ->leftJoin('admission_intakes as ai', 'ai.id', '=', 'po.admission_intake_id')
            ->leftJoin('programmes as prog', 'prog.id', '=', 'po.programme_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'prog.department_id')
            ->leftJoin('faculties as fac', 'fac.id', '=', 'dept.faculty_id')
            ->whereNull('a.deleted_at');
    }

    /**
     * Free-text search across the identifiers staff actually type: application
     * number, applicant number and applicant name.
     */
    protected function applySearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('a.application_number', 'ilike', '%'.$term.'%')
                ->orWhere('ap.applicant_number', 'ilike', '%'.$term.'%')
                ->orWhere('u.name', 'ilike', '%'.$term.'%');
        });
    }

    /** Turn a deadline into the phrasing the SLA column renders. */
    protected function slaStatus(?string $dueAt, ?string $completedAt = null): string
    {
        if ($completedAt !== null) {
            return 'Completed';
        }
        if ($dueAt === null) {
            return 'No SLA set';
        }

        $due = Carbon::parse($dueAt);
        if ($due->isPast()) {
            return 'Overdue by '.$due->diffForHumans(now(), ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 1]);
        }
        if ($due->isToday()) {
            return 'Expires today';
        }
        if ($due->diffInHours(now()) > -24) {
            return 'Expires in '.$due->diffForHumans(now(), ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 1]);
        }

        return $due->diffForHumans(now(), ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 1]).' remaining';
    }

    /** review_assignments.priority is a small int; the table renders a word. */
    protected function priorityLabel(?int $priority): string
    {
        return match (true) {
            $priority === null => 'Normal',
            $priority <= 2 => 'Urgent',
            $priority <= 5 => 'High',
            default => 'Normal',
        };
    }

    /** Workflow stage codes read as machine keys; the table renders a desk name. */
    protected function stageLabel(?string $stage): string
    {
        return match ($stage) {
            'triage' => 'Initial Triage',
            'document_verification' => 'Document Verification',
            'eligibility_check' => 'Eligibility Check',
            'department_review' => 'Departmental Review',
            'cluster_verification' => 'Subject Cluster Verification',
            'interview' => 'Pre-Interview Screening',
            'dean_signoff' => 'Dean Sign-off',
            'senate_approval' => 'Senate Approval',
            null, '' => 'Unassigned',
            default => ucwords(str_replace('_', ' ', $stage)),
        };
    }

    protected function percentage(int|float $part, int|float $whole, int $decimals = 1): float
    {
        return $whole > 0 ? round(($part / $whole) * 100, $decimals) : 0.0;
    }
}
