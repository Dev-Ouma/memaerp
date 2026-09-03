<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PgResearch\PgAppeal;
use App\Models\PgResearch\PgAppealCategory;
use App\Models\PgResearch\PgAppealPeriod;
use App\Models\PgResearch\PgDefenceRequest;
use App\Models\PgResearch\PgExaminer;
use App\Models\PgResearch\PgLegacyMigration;
use App\Models\PgResearch\PgPlagiarismScan;
use App\Models\PgResearch\PgProgressReport;
use App\Models\PgResearch\PgProposal;
use App\Models\PgResearch\PgPublication;
use App\Models\PgResearch\PgResearchCandidate;
use App\Models\PgResearch\PgSeminar;
use App\Models\PgResearch\PgSupervisor;
use App\Models\PgResearch\PgThesisMark;
use App\Models\PgResearch\PgThesisResubmission;
use App\Models\PgResearch\PgVivaExamination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Read side of the postgraduate research lifecycle. Every screen here is a
 * projection of live tables; all state changes belong to
 * PgResearchActionController and the PgResearchWorkflow service.
 */
final class PgResearchController extends Controller
{
    /** 1. Research Eligibility & Coursework Gating (R19) */
    public function eligibilityGating(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $candidates = PgResearchCandidate::query()
            ->with('waivers')
            ->when($status, fn ($q) => $q->where('eligibility_status', strtoupper((string) $status)))
            ->when($search, fn ($q) => $this->searchCandidate($q, (string) $search))
            ->orderBy('candidate_name')
            ->get()
            ->map(fn (PgResearchCandidate $c): array => [
                'id' => $c->id,
                'student_name' => $c->candidate_name,
                'reg_no' => $c->reg_no,
                'degree_level' => $c->degree_level === 'PHD' ? 'PhD' : 'Master',
                'programme' => $c->programme_title,
                'coursework_status' => $c->coursework_units_total === 0
                    ? 'No coursework registered'
                    : sprintf(
                        '%d/%d units passed%s',
                        $c->coursework_units_passed,
                        $c->coursework_units_total,
                        $c->gpa !== null ? sprintf(' (GPA: %.2f)', (float) $c->gpa) : '',
                    ),
                'fee_status' => $c->feesCleared()
                    ? 'Cleared (KES 0 Balance)'
                    : sprintf('Outstanding (KES %s)', number_format((float) $c->fee_balance, 2)),
                'registration_status' => $c->registration_status,
                'eligibility_verdict' => $this->label($c->eligibility_status),
                'waiver_applied' => $this->waiverLabel($c),
                'pending_waiver_id' => $c->waivers->firstWhere('status', 'PENDING')?->id,
                'approved_waiver_id' => $c->waivers->firstWhere('status', 'APPROVED')?->id,
            ]);

        $stats = [
            'totalPostgrads' => PgResearchCandidate::count(),
            'fullyEligible' => PgResearchCandidate::where('eligibility_status', 'ELIGIBLE')->count(),
            'provisionalWaivers' => PgResearchCandidate::where('eligibility_status', 'PROVISIONAL')->count(),
            'courseworkPending' => PgResearchCandidate::whereIn('eligibility_status', ['PENDING', 'BLOCKED'])->count(),
        ];

        return view('pg-research.eligibility-gating', [
            'stats' => $stats,
            'candidates' => $candidates,
            'status' => $status,
            'search' => $search,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 2. Supervisor Allocation */
    public function supervisorAllocation(Request $request): View
    {
        $search = $request->query('search');

        $allocations = PgResearchCandidate::query()
            ->with(['allocations.supervisor'])
            ->when($search, fn ($q) => $this->searchCandidate($q, (string) $search))
            ->orderBy('candidate_name')
            ->get()
            ->map(function (PgResearchCandidate $c): array {
                $active = $c->allocations->where('status', 'ACTIVE');
                $lead = $active->firstWhere('role', 'LEAD');
                $co = $active->firstWhere('role', 'CO');
                $external = $active->firstWhere('role', 'EXTERNAL');

                return [
                    'id' => $c->id,
                    'student_name' => $c->candidate_name,
                    'reg_no' => $c->reg_no,
                    'degree_level' => $c->degree_level === 'PHD' ? 'PhD' : 'Master',
                    'supervisor_1' => $lead?->supervisor->full_name ?? 'Unassigned',
                    'supervisor_2' => $co?->supervisor->full_name ?? '—',
                    'optional_mentor' => $external?->supervisor->full_name ?? '—',
                    'status' => $lead ? 'Allocated' : 'Awaiting Allocation',
                    'lead_allocation_id' => $lead?->id,
                ];
            });

        $stats = [
            'allocatedScholars' => PgResearchCandidate::whereHas(
                'allocations', fn ($q) => $q->where('role', 'LEAD')->where('status', 'ACTIVE'),
            )->count(),
            'unassignedScholars' => PgResearchCandidate::whereDoesntHave(
                'allocations', fn ($q) => $q->where('role', 'LEAD')->where('status', 'ACTIVE'),
            )->count(),
            'phdTwoSupervisorRatio' => $this->policyRatio('PHD', 2),
            'mscOneSupervisorRatio' => $this->policyRatio('MASTERS', 1),
        ];

        return view('pg-research.supervisor-allocation', [
            'stats' => $stats,
            'allocations' => $allocations,
            'search' => $search,
            'supervisors' => $this->supervisorOptions(),
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 3. Supervisor Roles & Capacity */
    public function supervisorRoles(Request $request): View
    {
        $search = $request->query('search');

        $roles = PgSupervisor::query()
            ->withCount(['allocations as active_load' => fn ($q) => $q->where('status', 'ACTIVE')])
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'ilike', "%{$search}%")
                ->orWhere('staff_no', 'ilike', "%{$search}%")
                ->orWhere('department', 'ilike', "%{$search}%")))
            ->orderBy('full_name')
            ->get()
            ->map(fn (PgSupervisor $s): array => [
                'id' => $s->id,
                'role_code' => $s->staff_no,
                'role_title' => $s->full_name,
                'min_qualification' => $s->academic_rank,
                'max_quota' => (string) $s->max_load,
                'sign_off_scope' => $s->specialization ?? $s->department ?? 'General supervision',
                'honorarium_unit' => sprintf('%d of %d slots in use', $s->active_load, $s->max_load),
                'status' => $s->is_active ? 'Active' : 'Inactive',
                'active_load' => $s->active_load,
            ]);

        $stats = [
            'totalRoles' => PgSupervisor::count(),
            'activeSupervisors' => PgSupervisor::where('is_active', true)->count(),
            'activeScholars' => PgResearchCandidate::whereNotIn('stage', ['COMPLETE', 'WITHDRAWN'])->count(),
            'maxRatio' => '1 : '.(int) (PgSupervisor::where('is_active', true)->max('max_load') ?? 0),
        ];

        return view('pg-research.supervisor-roles', [
            'stats' => $stats,
            'roles' => $roles,
            'search' => $search,
        ]);
    }

    /** 4. Proposal Reader Review */
    public function proposalReaderReview(Request $request): View
    {
        $status = $request->query('status');

        $proposals = PgProposal::query()
            ->with(['candidate', 'reader', 'reviews' => fn ($q) => $q->latest('reviewed_at')])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('submitted_at')
            ->get()
            ->map(function (PgProposal $p): array {
                $latest = $p->reviews->first();

                return [
                    'id' => $p->id,
                    'candidate_id' => $p->candidate_id,
                    'student_name' => $p->candidate->candidate_name,
                    'reg_no' => $p->candidate->reg_no,
                    'programme' => $p->candidate->programme_title,
                    'proposal_title' => $p->title,
                    'appointed_reader' => $p->reader?->full_name ?? 'Not appointed',
                    'assigned_date' => $p->submitted_at?->format('d M Y') ?? '—',
                    'reader_verdict' => $latest ? $this->label($latest->verdict) : 'Awaiting review',
                    'comments_summary' => $latest->comments ?? 'No reader comments recorded yet.',
                    'status' => $this->label($p->status),
                    'is_open' => in_array($p->status, ['SUBMITTED', 'UNDER_REVIEW'], true),
                ];
            });

        $stats = [
            'proposalsUnderReview' => PgProposal::where('status', 'UNDER_REVIEW')->count(),
            'readerApproved' => PgProposal::where('status', 'APPROVED')->count(),
            'readerRevisions' => PgProposal::where('status', 'REVISION_REQUIRED')->count(),
            'readerTurnaround' => $this->readerTurnaround(),
        ];

        return view('pg-research.proposal-reader-review', [
            'stats' => $stats,
            'proposals' => $proposals,
            'status' => $status,
            'readers' => $this->supervisorOptions(),
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 5. Seminar Presentations */
    public function seminarPresentations(Request $request): View
    {
        $type = $request->query('type');

        $seminars = PgSeminar::query()
            ->with('candidate')
            ->when($type, fn ($q) => $q->where('seminar_type', strtoupper((string) $type)))
            ->orderByDesc('scheduled_for')
            ->get()
            ->map(fn (PgSeminar $s): array => [
                'id' => $s->id,
                'candidate_name' => $s->candidate->candidate_name,
                'reg_no' => $s->candidate->reg_no,
                'programme' => $s->candidate->programme_title,
                'seminar_type' => $this->label($s->seminar_type),
                'presentation_date' => $s->scheduled_for->format('d M Y H:i'),
                'moderator' => $s->panel_chair ?? 'To be confirmed',
                'panel_feedback' => $s->outcome_notes ?? 'Pending panel feedback.',
                'status' => $this->label($s->status),
                'is_open' => $s->status === 'SCHEDULED',
            ]);

        $stats = [
            'seminarsCompleted' => PgSeminar::whereIn('status', ['HELD', 'PASSED', 'FAILED'])->count(),
            'departmentalSeminars' => PgSeminar::whereIn('seminar_type', ['PROPOSAL', 'PROGRESS'])->count(),
            'preDefenseSeminars' => PgSeminar::where('seminar_type', 'PRE_DEFENCE')->count(),
            'attendanceRate' => ($mean = PgSeminar::whereNotNull('attendance_count')->avg('attendance_count')) === null
                ? 'Not recorded'
                : number_format((float) $mean, 1),
        ];

        return view('pg-research.seminar-presentations', [
            'stats' => $stats,
            'seminars' => $seminars,
            'type' => $type,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 6. Progress Reports */
    public function progressReports(Request $request): View
    {
        $status = $request->query('status');

        $reports = PgProgressReport::query()
            ->with('candidate')
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('submitted_at')
            ->get()
            ->map(fn (PgProgressReport $r): array => [
                'id' => $r->id,
                'student_name' => $r->candidate->candidate_name,
                'reg_no' => $r->candidate->reg_no,
                'degree_level' => $r->candidate->degree_level === 'PHD' ? 'PhD' : 'Master',
                'report_stage' => $r->report_stage,
                'submission_date' => $r->submitted_at->format('d M Y'),
                'milestone_summary' => $r->milestone_summary,
                'supervisor_endorsement' => $r->supervisor_comment ?? 'Awaiting supervisor endorsement.',
                'self_service_action' => $this->label($r->status),
                'is_open' => $r->status !== 'APPROVED',
            ]);

        $stats = [
            'totalReportsSubmitted' => PgProgressReport::count(),
            'formACount' => PgProgressReport::where('report_stage', 'ilike', '%INCEPTION%')
                ->orWhere('report_stage', 'ilike', '%PROPOSAL%')->count(),
            'formBCount' => PgProgressReport::where('report_stage', 'ilike', '%FIELDWORK%')
                ->orWhere('report_stage', 'ilike', '%ANALYSIS%')->count(),
            'formCCount' => PgProgressReport::where('report_stage', 'ilike', '%WRITING%')
                ->orWhere('report_stage', 'ilike', '%DRAFT%')->count(),
        ];

        return view('pg-research.progress-reports', [
            'stats' => $stats,
            'reports' => $reports,
            'status' => $status,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 7. Plagiarism / Similarity Checker */
    public function plagiarismChecker(Request $request): View
    {
        $verdict = $request->query('verdict');

        $scans = PgPlagiarismScan::query()
            ->with('candidate')
            ->when($verdict, fn ($q) => $q->where('status', strtoupper((string) $verdict)))
            ->latest('scanned_at')
            ->get()
            ->map(fn (PgPlagiarismScan $s): array => [
                'id' => $s->id,
                'student_name' => $s->candidate->candidate_name,
                'reg_no' => $s->candidate->reg_no,
                'document_title' => $s->candidate->thesis_title ?? 'Untitled manuscript',
                'document_stage' => $this->label($s->document_type),
                'similarity_score' => sprintf('%.2f%%', (float) $s->similarity_index),
                'ai_score' => sprintf('threshold %.2f%%', (float) $s->threshold),
                'ai_breakdown' => $s->review_notes ?? 'No override recorded.',
                'matched_sources' => $s->report_reference ?? 'Report reference not supplied',
                'certificate_no' => $s->report_reference ?? sprintf('SCAN-%06d', $s->id),
                'verdict' => $this->label($s->status),
                'is_flagged' => $s->status === 'FLAGGED',
            ]);

        $stats = [
            'totalScansConducted' => PgPlagiarismScan::count(),
            'fullyClearedDocs' => PgPlagiarismScan::whereIn('status', ['PASSED', 'CLEARED_BY_OVERRIDE'])->count(),
            'flaggedSimilarity' => PgPlagiarismScan::whereColumn('similarity_index', '>', 'threshold')->count(),
            'flaggedAiUsage' => PgPlagiarismScan::whereNotNull('ai_index')
                ->whereColumn('ai_index', '>', 'ai_threshold')->count(),
        ];

        return view('pg-research.plagiarism-checker', [
            'stats' => $stats,
            'scans' => $scans,
            'verdict' => $verdict,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 8. Defence Request & Clearance */
    public function defenceRequestApproval(Request $request): View
    {
        $status = $request->query('status');

        $requests = PgDefenceRequest::query()
            ->with(['candidate.allocations.supervisor', 'scan'])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('requested_at')
            ->get()
            ->map(fn (PgDefenceRequest $r): array => [
                'id' => $r->id,
                'student_name' => $r->candidate->candidate_name,
                'reg_no' => $r->candidate->reg_no,
                'programme' => $r->candidate->programme_title,
                'thesis_title' => $r->thesis_title,
                'lead_supervisor' => $r->candidate->leadSupervisor()?->full_name ?? 'Unassigned',
                'turnitin_score' => $r->scan
                    ? sprintf('%.2f%%', (float) $r->scan->similarity_index)
                    : 'No scan on file',
                'fee_clearance' => $r->candidate->feesCleared()
                    ? 'Cleared'
                    : sprintf('KES %s outstanding', number_format((float) $r->candidate->fee_balance, 2)),
                'publications_count' => (string) $r->candidate->publications()->where('status', 'ACCEPTED')->count(),
                'status' => $this->label($r->status),
                'is_pending' => $r->status === 'PENDING',
            ]);

        $stats = [
            'totalRequests' => PgDefenceRequest::count(),
            'pendingApproval' => PgDefenceRequest::where('status', 'PENDING')->count(),
            'clearedForViva' => PgDefenceRequest::where('status', 'APPROVED')->count(),
            'avgTurnitin' => round((float) (PgPlagiarismScan::where('document_type', 'THESIS')->avg('similarity_index') ?? 0), 2),
        ];

        return view('pg-research.defence-request-approval', [
            'stats' => $stats,
            'requests' => $requests,
            'status' => $status,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 9. Examiner Dashboard */
    public function examinerDashboard(Request $request): View
    {
        $status = $request->query('status');

        $assignments = PgExaminer::query()
            ->with(['candidate', 'report'])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('appointed_on')
            ->get()
            ->map(fn (PgExaminer $e): array => [
                'id' => $e->id,
                'examiner_name' => $e->examiner_name,
                'examiner_type' => $this->label($e->examiner_type),
                'candidate_code' => $e->candidate->reg_no,
                'thesis_title' => $e->candidate->thesis_title ?? 'Untitled thesis',
                'dispatch_date' => $e->appointed_on->format('d M Y'),
                'due_date' => $e->appointed_on->copy()->addDays(42)->format('d M Y'),
                'report_status' => $this->label($e->status),
                'honorarium_status' => $e->report ? 'Payable' : 'Withheld pending report',
                'has_report' => $e->report !== null,
            ]);

        $stats = [
            'assignedManuscripts' => PgExaminer::count(),
            'evaluationsCompleted' => PgExaminer::where('status', 'REPORT_SUBMITTED')->count(),
            'evaluationsPending' => PgExaminer::whereIn('status', ['NOMINATED', 'APPOINTED'])->count(),
            'avgTurnaroundDays' => $this->examinerTurnaround(),
        ];

        return view('pg-research.examiner-dashboard', [
            'stats' => $stats,
            'assignments' => $assignments,
            'status' => $status,
            'defenceCleared' => $this->defenceClearedOptions(),
        ]);
    }

    /** 10. Viva Voce Examination */
    public function vivaExamination(Request $request): View
    {
        $status = $request->query('status');

        $vivas = PgVivaExamination::query()
            ->with(['candidate.examiners'])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->orderByDesc('scheduled_for')
            ->get()
            ->map(function (PgVivaExamination $v): array {
                $examiners = $v->candidate->examiners;

                return [
                    'id' => $v->id,
                    'candidate_name' => $v->candidate->candidate_name,
                    'reg_no' => $v->candidate->reg_no,
                    'degree' => $v->candidate->degree_level === 'PHD' ? 'PhD' : 'Master',
                    'viva_date' => $v->scheduled_for->format('d M Y H:i'),
                    'venue' => $v->venue,
                    'board_chair' => $v->chair_name ?? 'To be confirmed',
                    'internal_examiner' => $examiners->firstWhere('examiner_type', 'INTERNAL')?->examiner_name ?? '—',
                    'external_examiner' => $examiners->firstWhere('examiner_type', 'EXTERNAL')?->examiner_name ?? '—',
                    'status' => $v->verdict ? $this->label($v->verdict) : $this->label($v->status),
                    'is_open' => $v->status === 'SCHEDULED',
                ];
            });

        $stats = [
            'scheduledVivas' => PgVivaExamination::where('status', 'SCHEDULED')->count(),
            'completedThisMonth' => PgVivaExamination::where('status', 'HELD')
                ->whereBetween('verdict_recorded_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'passRate' => $this->passRate(),
            'pendingPanels' => PgResearchCandidate::whereHas(
                'defenceRequests', fn ($q) => $q->where('status', 'APPROVED'),
            )->whereDoesntHave('viva')->count(),
        ];

        return view('pg-research.viva-examination', [
            'stats' => $stats,
            'vivas' => $vivas,
            'status' => $status,
            'readyCandidates' => $this->vivaReadyOptions(),
        ]);
    }

    /** 11. Thesis Marks Approval */
    public function thesisMarksApproval(Request $request): View
    {
        $status = $request->query('status');

        $marks = PgThesisMark::query()
            ->with(['candidate.examiners.report'])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('updated_at')
            ->get()
            ->map(function (PgThesisMark $m): array {
                $reports = $m->candidate->examiners->map(fn ($e) => $e->report)->filter();
                $byType = fn (string $type) => $m->candidate->examiners
                    ->firstWhere('examiner_type', $type)?->report?->score;

                return [
                    'id' => $m->id,
                    'student_name' => $m->candidate->candidate_name,
                    'reg_no' => $m->candidate->reg_no,
                    'programme' => $m->candidate->programme_title,
                    'internal_mark' => $byType('INTERNAL') !== null ? sprintf('%.2f', (float) $byType('INTERNAL')) : '—',
                    'external_mark' => $byType('EXTERNAL') !== null ? sprintf('%.2f', (float) $byType('EXTERNAL')) : '—',
                    'oral_viva_mark' => $m->candidate->viva?->verdict ? $this->label($m->candidate->viva->verdict) : '—',
                    'composite_score' => sprintf('%.2f', (float) $m->composite_score),
                    'final_grade' => $m->final_grade,
                    'senate_status' => $this->label($m->status),
                    'panel_reports' => $reports->count(),
                    'is_pending' => $m->status === 'SUBMITTED',
                ];
            });

        $stats = [
            'marksPendingRatification' => PgThesisMark::where('status', 'SUBMITTED')->count(),
            'approvedBySenate' => PgThesisMark::where('status', 'RATIFIED')->count(),
            'distinctionsAwarded' => PgThesisMark::where('final_grade', 'Distinction')->count(),
            'avgCompositeScore' => number_format((float) (PgThesisMark::avg('composite_score') ?? 0), 2),
        ];

        return view('pg-research.thesis-marks-approval', [
            'stats' => $stats,
            'marksList' => $marks,
            'status' => $status,
        ]);
    }

    /** 12. Thesis Resubmission & Corrections */
    public function thesisResubmission(Request $request): View
    {
        $status = $request->query('status');

        $submissions = PgThesisResubmission::query()
            ->with(['candidate.viva'])
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest('due_on')
            ->get()
            ->map(fn (PgThesisResubmission $s): array => [
                'id' => $s->id,
                'student_name' => $s->candidate->candidate_name,
                'reg_no' => $s->candidate->reg_no,
                'programme' => $s->candidate->programme_title,
                'thesis_title' => $s->candidate->thesis_title ?? 'Untitled thesis',
                'viva_verdict' => $s->candidate->viva?->verdict ? $this->label($s->candidate->viva->verdict) : '—',
                'corrections_matrix' => $s->corrections_summary ?? 'Corrections matrix not yet filed.',
                'resubmitted_at' => $s->submitted_at?->format('d M Y') ?? sprintf('Due %s', $s->due_on->format('d M Y')),
                'examiner_auditor' => $s->verifier?->name ?? 'Awaiting verification',
                'hardbound_copies' => sprintf('Cycle %d', $s->cycle),
                'status' => $this->label($s->status),
                'is_awaiting' => $s->status === 'AWAITING',
                'is_submitted' => in_array($s->status, ['SUBMITTED', 'UNDER_REVIEW'], true),
                'is_overdue' => $s->status === 'AWAITING' && $s->due_on->isPast(),
            ]);

        $stats = [
            'totalResubmissions' => PgThesisResubmission::count(),
            'underReview' => PgThesisResubmission::whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->count(),
            'approvedForBinding' => PgThesisResubmission::where('status', 'ACCEPTED')->count(),
            'revisionsPending' => PgThesisResubmission::whereIn('status', ['AWAITING', 'REJECTED'])->count(),
        ];

        return view('pg-research.thesis-resubmission', [
            'stats' => $stats,
            'resubmissions' => $submissions,
            'status' => $status,
        ]);
    }

    /** 13. Publications Review */
    public function publicationsReview(Request $request): View
    {
        $status = $request->query('status');

        $publications = PgPublication::query()
            ->with('candidate')
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest()
            ->get()
            ->map(fn (PgPublication $p): array => [
                'id' => $p->id,
                'author_name' => $p->candidate->candidate_name,
                'reg_no' => $p->candidate->reg_no,
                'programme' => $p->candidate->programme_title,
                'article_title' => $p->article_title,
                'journal_name' => $p->journal_name,
                'doi_link' => $p->doi ?? 'DOI not supplied',
                'indexing' => $p->indexed_in ?? 'Not indexed',
                'cue_requirement' => $p->candidate->degree_level === 'PHD' ? '2 papers (PhD)' : '1 paper (Masters)',
                'status' => $this->label($p->status),
                'is_open' => ! in_array($p->status, ['ACCEPTED', 'REJECTED'], true),
            ]);

        $stats = [
            'totalArticlesLogged' => PgPublication::count(),
            'verifiedPeerReviewed' => PgPublication::where('status', 'ACCEPTED')->count(),
            'pendingIndexingCheck' => PgPublication::whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->count(),
            'rejectedNonCUE' => PgPublication::where('status', 'REJECTED')->count(),
        ];

        return view('pg-research.publications-review', [
            'stats' => $stats,
            'publications' => $publications,
            'status' => $status,
            'allCandidates' => $this->candidateOptions(),
        ]);
    }

    /** 14. Legacy Data Migration */
    public function legacyMigration(Request $request): View
    {
        $status = $request->query('status');

        $migrations = PgLegacyMigration::query()
            ->with('candidate')
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->latest()
            ->get()
            ->map(fn (PgLegacyMigration $m): array => [
                'id' => $m->id,
                'student_name' => $m->candidate?->candidate_name ?? 'Unmatched record',
                'reg_no' => $m->source_reference,
                'programme' => $m->candidate?->programme_title ?? '—',
                'source_module' => $m->source_module,
                'migrated_artifacts' => $m->artifacts ?? 'No artefacts listed',
                'target_stage' => $m->target_stage,
                'validation_status' => $m->error_message ?? $this->label($m->status),
                'batch' => $m->batch_reference,
                'is_pending' => in_array($m->status, ['PENDING', 'FAILED'], true),
                'is_imported' => $m->status === 'IMPORTED',
                'is_verified' => $m->status === 'VERIFIED',
                'is_failed' => $m->status === 'FAILED',
            ]);

        $stats = [
            'totalLegacyDossiers' => PgLegacyMigration::count(),
            'migratedFromDSC800' => PgLegacyMigration::where('source_module', 'ilike', '%DSC800%')
                ->whereIn('status', ['IMPORTED', 'VERIFIED'])->count(),
            'interimFormsMigrated' => PgLegacyMigration::whereIn('status', ['IMPORTED', 'VERIFIED'])->count(),
            'pendingDataValidation' => PgLegacyMigration::whereIn('status', ['PENDING', 'FAILED'])->count(),
        ];

        return view('pg-research.legacy-migration', [
            'stats' => $stats,
            'migrations' => $migrations,
            'status' => $status,
        ]);
    }

    /** 15. Appeal Period Setup */
    public function appealPeriodSetup(Request $request): View
    {
        $status = $request->query('status');

        $periods = PgAppealPeriod::query()
            ->with(['category'])
            ->withCount('appeals')
            ->when($status, fn ($q) => $q->where('status', strtoupper((string) $status)))
            ->orderByDesc('opens_on')
            ->get()
            ->map(fn (PgAppealPeriod $p): array => [
                'id' => $p->id,
                'window_name' => $p->term_label,
                'academic_year' => $p->academic_year,
                'cohort' => $p->category?->name ?? 'All categories',
                'start_date' => $p->opens_on->format('d M Y'),
                'end_date' => $p->closes_on->format('d M Y'),
                'hearing_date' => $p->closes_on->copy()->addDays(7)->format('d M Y'),
                'status' => $this->label($p->status),
                'appeals_count' => $p->appeals_count,
                'is_draft' => $p->status === 'DRAFT',
                'is_open' => $p->status === 'OPEN',
            ]);

        $stats = [
            'totalWindows' => PgAppealPeriod::count(),
            'open' => PgAppealPeriod::where('status', 'OPEN')->count(),
            'closed' => PgAppealPeriod::where('status', 'CLOSED')->count(),
            'appealsLodged' => PgAppeal::count(),
        ];

        return view('pg-research.appeal-period-setup', [
            'stats' => $stats,
            'periods' => $periods,
            'status' => $status,
            'categories' => PgAppealCategory::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /** 16. Appeal Categories & lodged appeals */
    public function appealCategory(Request $request): View
    {
        $search = $request->query('search');

        $categories = PgAppealCategory::query()
            ->withCount('appeals')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('code', 'ilike', "%{$search}%")))
            ->orderBy('name')
            ->get()
            ->map(fn (PgAppealCategory $c): array => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'tier' => $this->label($c->applies_to),
                'description' => $c->description ?? 'No description recorded.',
                'sla_days' => (string) $c->sla_days,
                'fee' => sprintf('KES %s', number_format((float) $c->fee_amount, 2)),
                'appeals_count' => $c->appeals_count,
                'status' => $c->is_active ? 'Active' : 'Inactive',
                'is_active' => $c->is_active,
            ]);

        $appeals = PgAppeal::query()
            ->with(['candidate', 'category', 'assignee'])
            ->latest('submitted_at')
            ->get()
            ->map(fn (PgAppeal $a): array => [
                'id' => $a->id,
                'reference' => $a->reference,
                'student_name' => $a->candidate->candidate_name,
                'reg_no' => $a->candidate->reg_no,
                'category' => $a->category->name,
                'grounds' => $a->grounds,
                'status' => $this->label($a->status),
                'assignee' => $a->assignee?->name ?? 'Unassigned',
                'submitted_at' => $a->submitted_at->format('d M Y'),
                'due_at' => $a->dueAt()?->format('d M Y') ?? '—',
                'is_overdue' => $a->isOverdue(),
                'is_open' => ! in_array($a->status, PgAppeal::TERMINAL, true),
            ]);

        $stats = [
            'totalCategories' => PgAppealCategory::count(),
            'activeCategories' => PgAppealCategory::where('is_active', true)->count(),
            'appealsLodged' => PgAppeal::count(),
            'appealsOpen' => PgAppeal::whereNotIn('status', PgAppeal::TERMINAL)->count(),
        ];

        return view('pg-research.appeal-category', [
            'stats' => $stats,
            'categories' => $categories,
            'appeals' => $appeals,
            'search' => $search,
            'allCandidates' => $this->candidateOptions(),
            'staff' => \App\Models\User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // --------------------------------------------------------------- Helpers

    /** How many candidates of a degree level meet the supervision policy, as "met/total". */
    private function policyRatio(string $level, int $required): string
    {
        $total = PgResearchCandidate::where('degree_level', $level)->count();

        $met = PgResearchCandidate::where('degree_level', $level)
            ->whereHas('allocations', fn ($q) => $q->where('status', 'ACTIVE'), '>=', $required)
            ->count();

        return sprintf('%d / %d', $met, $total);
    }

    /** Mean days between a proposal reaching a reader and that reader's verdict. */
    private function readerTurnaround(): string
    {
        $days = \App\Models\PgResearch\PgProposalReview::query()
            ->join('pg_proposals', 'pg_proposals.id', '=', 'pg_proposal_reviews.proposal_id')
            ->whereNotNull('pg_proposals.submitted_at')
            ->whereNotNull('pg_proposal_reviews.reviewed_at')
            ->avg(DB::raw('EXTRACT(EPOCH FROM (pg_proposal_reviews.reviewed_at - pg_proposals.submitted_at)) / 86400'));

        return $days === null ? 'No verdicts yet' : number_format((float) $days, 1).' days';
    }

    /** Mean days between an examiner's appointment and their filed report. */
    private function examinerTurnaround(): string
    {
        $days = \App\Models\PgResearch\PgExaminerReport::query()
            ->join('pg_examiners', 'pg_examiners.id', '=', 'pg_examiner_reports.examiner_id')
            ->whereNotNull('pg_examiners.appointed_on')
            ->whereNotNull('pg_examiner_reports.submitted_at')
            ->avg(DB::raw('EXTRACT(EPOCH FROM (pg_examiner_reports.submitted_at - pg_examiners.appointed_on)) / 86400'));

        return $days === null ? 'No reports yet' : number_format((float) $days, 1).' days';
    }

    private function searchCandidate($query, string $term)
    {
        return $query->where(fn ($w) => $w
            ->where('candidate_name', 'ilike', "%{$term}%")
            ->orWhere('reg_no', 'ilike', "%{$term}%")
            ->orWhere('programme_title', 'ilike', "%{$term}%"));
    }

    private function candidateOptions(): Collection
    {
        return PgResearchCandidate::orderBy('candidate_name')
            ->get(['id', 'candidate_name', 'reg_no', 'degree_level']);
    }

    private function supervisorOptions(): Collection
    {
        return PgSupervisor::where('is_active', true)
            ->withCount(['allocations as active_load' => fn ($q) => $q->where('status', 'ACTIVE')])
            ->orderBy('full_name')
            ->get();
    }

    private function defenceClearedOptions(): Collection
    {
        return PgResearchCandidate::whereHas('defenceRequests', fn ($q) => $q->where('status', 'APPROVED'))
            ->orderBy('candidate_name')
            ->get(['id', 'candidate_name', 'reg_no']);
    }

    /** Candidates whose full examiner panel has reported — the only ones a viva may be booked for. */
    private function vivaReadyOptions(): Collection
    {
        return PgResearchCandidate::whereHas('examiners')
            ->whereDoesntHave('examiners', fn ($q) => $q->where('status', '!=', 'REPORT_SUBMITTED'))
            ->orderBy('candidate_name')
            ->get(['id', 'candidate_name', 'reg_no']);
    }

    private function passRate(): float
    {
        $held = PgVivaExamination::whereNotNull('verdict')->count();

        if ($held === 0) {
            return 0.0;
        }

        $passed = PgVivaExamination::whereIn('verdict', ['PASS', 'PASS_MINOR', 'PASS_MAJOR'])->count();

        return round($passed / $held * 100, 1);
    }

    private function waiverLabel(PgResearchCandidate $candidate): string
    {
        $approved = $candidate->waivers->firstWhere('status', 'APPROVED');
        if ($approved) {
            return sprintf(
                '%s approved%s',
                str_replace('_', ' ', $approved->waiver_type),
                $approved->expires_on ? ' until '.$approved->expires_on->format('d M Y') : '',
            );
        }

        if ($candidate->waivers->firstWhere('status', 'PENDING')) {
            return 'Waiver request pending decision';
        }

        return 'No waiver on file';
    }

    private function label(string $value): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $value)));
    }
}
