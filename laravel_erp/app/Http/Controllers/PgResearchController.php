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
            'totalAllocated' => PgResearchCandidate::whereHas(
                'allocations', fn ($q) => $q->where('role', 'LEAD')->where('status', 'ACTIVE'),
            )->count(),
            'unassigned' => PgResearchCandidate::whereDoesntHave(
                'allocations', fn ($q) => $q->where('role', 'LEAD')->where('status', 'ACTIVE'),
            )->count(),
            'phdCandidates' => PgResearchCandidate::where('degree_level', 'PHD')->count(),
            'mscCandidates' => PgResearchCandidate::where('degree_level', 'MASTERS')->count(),
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
            'activeRoles' => PgSupervisor::where('is_active', true)->count(),
            'maxQuota' => (int) PgSupervisor::where('is_active', true)->sum('max_load'),
            'assignedLoad' => \App\Models\PgResearch\PgSupervisorAllocation::where('status', 'ACTIVE')->count(),
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
            'totalProposals' => PgProposal::count(),
            'underReaderReview' => PgProposal::where('status', 'UNDER_REVIEW')->count(),
            'approved' => PgProposal::where('status', 'APPROVED')->count(),
            'revisionRequired' => PgProposal::where('status', 'REVISION_REQUIRED')->count(),
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
            'totalSeminars' => PgSeminar::count(),
            'departmental' => PgSeminar::where('seminar_type', 'PROPOSAL')->count(),
            'preDefence' => PgSeminar::where('seminar_type', 'PRE_DEFENCE')->count(),
            'scheduled' => PgSeminar::where('status', 'SCHEDULED')->count(),
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
            'totalReports' => PgProgressReport::count(),
            'formSubmitted' => PgProgressReport::where('status', 'SUBMITTED')->count(),
            'approved' => PgProgressReport::where('status', 'APPROVED')->count(),
            'returned' => PgProgressReport::where('status', 'RETURNED')->count(),
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
            'totalScans' => PgPlagiarismScan::count(),
            'fullyCleared' => PgPlagiarismScan::whereIn('status', ['PASSED', 'CLEARED_BY_OVERRIDE'])->count(),
            'flagged' => PgPlagiarismScan::where('status', 'FLAGGED')->count(),
            'averageSimilarity' => round((float) (PgPlagiarismScan::avg('similarity_index') ?? 0), 2),
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
            'total' => PgDefenceRequest::count(),
            'pending' => PgDefenceRequest::where('status', 'PENDING')->count(),
            'cleared' => PgDefenceRequest::where('status', 'APPROVED')->count(),
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
            'assigned' => PgExaminer::whereIn('status', ['APPOINTED', 'NOMINATED'])->count(),
            'evaluationsReceived' => \App\Models\PgResearch\PgExaminerReport::count(),
            'avgScore' => round((float) (\App\Models\PgResearch\PgExaminerReport::avg('score') ?? 0), 2),
            'overdue' => PgExaminer::where('status', 'APPOINTED')
                ->whereDate('appointed_on', '<', now()->subDays(42))->count(),
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
            'scheduled' => PgVivaExamination::where('status', 'SCHEDULED')->count(),
            'completed' => PgVivaExamination::where('status', 'HELD')->count(),
            'passRate' => $this->passRate(),
            'pendingVerdict' => PgVivaExamination::where('status', 'SCHEDULED')
                ->whereDate('scheduled_for', '<', now())->count(),
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
            'totalMarks' => PgThesisMark::count(),
            'approved' => PgThesisMark::where('status', 'RATIFIED')->count(),
            'distinctions' => PgThesisMark::where('final_grade', 'Distinction')->count(),
            'avgScore' => round((float) (PgThesisMark::avg('composite_score') ?? 0), 2),
        ];

        return view('pg-research.thesis-marks-approval', [
            'stats' => $stats,
            'marks' => $marks,
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
            'approved' => PgThesisResubmission::where('status', 'ACCEPTED')->count(),
            'minorRevisions' => PgThesisResubmission::where('status', 'AWAITING')->count(),
        ];

        return view('pg-research.thesis-resubmission', [
            'stats' => $stats,
            'submissions' => $submissions,
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
            'totalPublications' => PgPublication::count(),
            'verified' => PgPublication::where('status', 'ACCEPTED')->count(),
            'pending' => PgPublication::whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->count(),
            'rejected' => PgPublication::where('status', 'REJECTED')->count(),
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
            ]);

        $stats = [
            'totalRecords' => PgLegacyMigration::count(),
            'migrated' => PgLegacyMigration::whereIn('status', ['IMPORTED', 'VERIFIED'])->count(),
            'interim' => PgLegacyMigration::where('status', 'IMPORTED')->count(),
            'pending' => PgLegacyMigration::whereIn('status', ['PENDING', 'FAILED'])->count(),
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
