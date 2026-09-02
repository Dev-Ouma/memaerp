<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PgResearch\PgAppeal;
use App\Models\PgResearch\PgAppealCategory;
use App\Models\PgResearch\PgAppealPeriod;
use App\Models\PgResearch\PgDefenceRequest;
use App\Models\PgResearch\PgEligibilityWaiver;
use App\Models\PgResearch\PgExaminer;
use App\Models\PgResearch\PgLegacyMigration;
use App\Models\PgResearch\PgPlagiarismScan;
use App\Models\PgResearch\PgProgressReport;
use App\Models\PgResearch\PgProposal;
use App\Models\PgResearch\PgPublication;
use App\Models\PgResearch\PgResearchCandidate;
use App\Models\PgResearch\PgSeminar;
use App\Models\PgResearch\PgSupervisor;
use App\Models\PgResearch\PgSupervisorAllocation;
use App\Models\PgResearch\PgThesisMark;
use App\Models\PgResearch\PgThesisResubmission;
use App\Models\PgResearch\PgVivaExamination;
use App\Services\PgResearchWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * The write side of the postgraduate research screens. Every control the user
 * can press on a pg-research page lands in exactly one method here, delegates
 * the state change to PgResearchWorkflow, and reports the real outcome.
 */
final class PgResearchActionController extends Controller
{
    public function __construct(private readonly PgResearchWorkflow $workflow) {}

    // ------------------------------------------------- Eligibility & waivers

    public function storeCandidate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reg_no' => ['required', 'string', 'max:60', 'unique:pg_research_candidates,reg_no'],
            'candidate_name' => ['required', 'string', 'max:190'],
            'degree_level' => ['required', 'in:MASTERS,PHD'],
            'programme_title' => ['required', 'string', 'max:190'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'coursework_units_total' => ['nullable', 'integer', 'min:0', 'max:60'],
            'coursework_units_passed' => ['nullable', 'integer', 'min:0', 'max:60', 'lte:coursework_units_total'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'fee_balance' => ['nullable', 'numeric', 'min:0'],
            'registration_status' => ['nullable', 'string', 'max:40'],
        ]);

        $candidate = PgResearchCandidate::create([
            ...$data,
            'coursework_units_total' => (int) ($data['coursework_units_total'] ?? 0),
            'coursework_units_passed' => (int) ($data['coursework_units_passed'] ?? 0),
            'fee_balance' => (float) ($data['fee_balance'] ?? 0),
            'registration_status' => $data['registration_status'] ?? 'ACTIVE',
        ]);

        $this->workflow->recomputeEligibility($candidate);

        return back()->with('success', "Candidate {$candidate->reg_no} registered and eligibility evaluated.");
    }

    public function recomputeEligibility(PgResearchCandidate $candidate): RedirectResponse
    {
        $candidate = $this->workflow->recomputeEligibility($candidate);

        return back()->with('success', "Eligibility for {$candidate->reg_no} is now {$candidate->eligibility_status}.");
    }

    public function requestWaiver(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'waiver_type' => ['nullable', 'string', 'max:60'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->requestWaiver($candidate, $data['reason'], $data['waiver_type'] ?? 'R19_PROVISIONAL');

            return "Waiver request lodged for {$candidate->reg_no}.";
        });
    }

    public function decideWaiver(Request $request, PgEligibilityWaiver $waiver): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'notes' => ['nullable', 'string'],
            'expires_on' => ['nullable', 'date', 'after:today'],
        ]);

        return $this->run(function () use ($data, $waiver): string {
            $this->workflow->decideWaiver(
                $waiver,
                $data['decision'] === 'approve',
                $data['notes'] ?? null,
                $data['expires_on'] ?? null,
            );

            return 'Waiver '.($data['decision'] === 'approve' ? 'approved' : 'rejected')
                .'; candidate eligibility recalculated.';
        });
    }

    public function revokeWaiver(Request $request, PgEligibilityWaiver $waiver): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5']]);

        return $this->run(function () use ($data, $waiver): string {
            $this->workflow->revokeWaiver($waiver, $data['reason']);

            return 'Waiver revoked; eligibility recalculated.';
        });
    }

    // ------------------------------------------------------- Supervision

    public function storeSupervisor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_no' => ['required', 'string', 'max:40', 'unique:pg_supervisors,staff_no'],
            'full_name' => ['required', 'string', 'max:190'],
            'academic_rank' => ['required', 'string', 'max:60'],
            'department' => ['nullable', 'string', 'max:190'],
            'specialization' => ['nullable', 'string', 'max:190'],
            'max_load' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $supervisor = PgSupervisor::create([...$data, 'is_active' => true]);

        return back()->with('success', "{$supervisor->full_name} added to the supervision pool.");
    }

    public function toggleSupervisor(PgSupervisor $supervisor): RedirectResponse
    {
        $supervisor->update(['is_active' => ! $supervisor->is_active]);

        return back()->with(
            'success',
            "{$supervisor->full_name} is now ".($supervisor->is_active ? 'active' : 'inactive').'.',
        );
    }

    public function allocateSupervisor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'supervisor_id' => ['required', 'exists:pg_supervisors,id'],
            'role' => ['required', 'in:LEAD,CO,EXTERNAL'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $supervisor = PgSupervisor::findOrFail($data['supervisor_id']);
            $this->workflow->allocateSupervisor($candidate, $supervisor, $data['role'], $data['notes'] ?? null);

            return "{$supervisor->full_name} allocated to {$candidate->reg_no} as {$data['role']}.";
        });
    }

    public function endAllocation(Request $request, PgSupervisorAllocation $allocation): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5']]);

        return $this->run(function () use ($data, $allocation): string {
            $this->workflow->endAllocation($allocation, $data['reason']);

            return 'Supervision allocation ended.';
        });
    }

    // ---------------------------------------------------------- Proposals

    public function submitProposal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'title' => ['required', 'string', 'max:190'],
            'abstract' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $proposal = $this->workflow->submitProposal($candidate, $data['title'], $data['abstract'] ?? null);

            return "Proposal v{$proposal->version} submitted for {$candidate->reg_no}.";
        });
    }

    public function appointReader(Request $request, PgProposal $proposal): RedirectResponse
    {
        $data = $request->validate(['reader_id' => ['required', 'exists:pg_supervisors,id']]);

        return $this->run(function () use ($data, $proposal): string {
            $reader = PgSupervisor::findOrFail($data['reader_id']);
            $this->workflow->appointReader($proposal, $reader);

            return "{$reader->full_name} appointed as reader.";
        });
    }

    public function reviewProposal(Request $request, PgProposal $proposal): RedirectResponse
    {
        $data = $request->validate([
            'verdict' => ['required', 'in:APPROVE,MINOR_REVISION,MAJOR_REVISION,REJECT'],
            'comments' => ['required', 'string', 'min:10'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return $this->run(function () use ($data, $proposal): string {
            $this->workflow->recordProposalReview(
                $proposal, $data['verdict'], $data['comments'], isset($data['score']) ? (float) $data['score'] : null,
            );

            return "Reader verdict recorded: {$data['verdict']}.";
        });
    }

    // ----------------------------------------------------------- Seminars

    public function scheduleSeminar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'seminar_type' => ['required', 'in:PROPOSAL,PROGRESS,PRE_DEFENCE'],
            'scheduled_for' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:190'],
            'panel_chair' => ['nullable', 'string', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->scheduleSeminar(
                $candidate, $data['seminar_type'], $data['scheduled_for'], $data['venue'], $data['panel_chair'] ?? null,
            );

            return "Seminar scheduled for {$candidate->reg_no}.";
        });
    }

    public function concludeSeminar(Request $request, PgSeminar $seminar): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:HELD,PASSED,FAILED,DEFERRED,CANCELLED'],
            'outcome_notes' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data, $seminar): string {
            $this->workflow->recordSeminarOutcome($seminar, $data['status'], $data['outcome_notes'] ?? null);

            return "Seminar outcome recorded as {$data['status']}.";
        });
    }

    // --------------------------------------------------- Progress reports

    public function submitProgressReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'period_label' => ['required', 'string', 'max:60'],
            'report_stage' => ['required', 'string', 'max:60'],
            'milestone_summary' => ['required', 'string', 'min:10'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->submitProgressReport(
                $candidate, $data['period_label'], $data['report_stage'], $data['milestone_summary'],
            );

            return "Progress report for {$data['period_label']} submitted.";
        });
    }

    public function decideProgressReport(Request $request, PgProgressReport $report): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:APPROVED,RETURNED,UNDER_REVIEW'],
            'comment' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data, $report): string {
            $this->workflow->decideProgressReport($report, $data['decision'], $data['comment'] ?? null);

            return "Progress report {$data['decision']}.";
        });
    }

    // ------------------------------------------------------- Similarity

    public function recordScan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'document_type' => ['required', 'in:PROPOSAL,THESIS,ARTICLE'],
            'similarity_index' => ['required', 'numeric', 'min:0', 'max:100'],
            'threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'report_reference' => ['nullable', 'string', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $scan = $this->workflow->recordScan(
                $candidate,
                $data['document_type'],
                (float) $data['similarity_index'],
                (float) ($data['threshold'] ?? 15),
                $data['report_reference'] ?? null,
            );

            return "Similarity scan recorded: {$scan->status} at {$scan->similarity_index}%.";
        });
    }

    public function overrideScan(Request $request, PgPlagiarismScan $scan): RedirectResponse
    {
        $data = $request->validate(['notes' => ['required', 'string', 'min:10']]);

        return $this->run(function () use ($data, $scan): string {
            $this->workflow->overrideScan($scan, $data['notes']);

            return 'Similarity flag cleared by documented override.';
        });
    }

    // ---------------------------------------------------------- Defence

    public function requestDefence(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'thesis_title' => ['required', 'string', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->requestDefence($candidate, $data['thesis_title']);

            return "Defence clearance requested for {$candidate->reg_no}.";
        });
    }

    public function decideDefence(Request $request, PgDefenceRequest $defenceRequest): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:APPROVED,RETURNED,REJECTED'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data, $defenceRequest): string {
            $this->workflow->decideDefence($defenceRequest, $data['decision'], $data['notes'] ?? null);

            return "Defence request {$data['decision']}.";
        });
    }

    // --------------------------------------------------------- Examiners

    public function appointExaminer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'examiner_name' => ['required', 'string', 'max:190'],
            'examiner_type' => ['required', 'in:INTERNAL,EXTERNAL,CHAIR'],
            'institution' => ['nullable', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->appointExaminer(
                $candidate,
                $data['examiner_name'],
                $data['examiner_type'],
                $data['institution'] ?? null,
                $data['email'] ?? null,
            );

            return "{$data['examiner_name']} appointed as {$data['examiner_type']} examiner.";
        });
    }

    public function submitExaminerReport(Request $request, PgExaminer $examiner): RedirectResponse
    {
        $data = $request->validate([
            'recommendation' => ['required', 'in:PASS,MINOR,MAJOR,REEXAMINE,FAIL'],
            'remarks' => ['required', 'string', 'min:10'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return $this->run(function () use ($data, $examiner): string {
            $this->workflow->submitExaminerReport(
                $examiner, $data['recommendation'], $data['remarks'], isset($data['score']) ? (float) $data['score'] : null,
            );

            return "Examiner report filed: {$data['recommendation']}.";
        });
    }

    // -------------------------------------------------------------- Viva

    public function scheduleViva(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'scheduled_for' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:190'],
            'chair_name' => ['nullable', 'string', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->scheduleViva($candidate, $data['scheduled_for'], $data['venue'], $data['chair_name'] ?? null);

            return "Viva scheduled for {$candidate->reg_no}.";
        });
    }

    public function recordVivaVerdict(Request $request, PgVivaExamination $viva): RedirectResponse
    {
        $data = $request->validate([
            'verdict' => ['required', 'in:PASS,PASS_MINOR,PASS_MAJOR,REEXAMINE,FAIL'],
            'verdict_notes' => ['required', 'string', 'min:10'],
        ]);

        return $this->run(function () use ($data, $viva): string {
            $this->workflow->recordVivaVerdict($viva, $data['verdict'], $data['verdict_notes']);

            return "Viva verdict recorded: {$data['verdict']}.";
        });
    }

    // ------------------------------------------------------------- Marks

    public function ratifyMark(Request $request, PgThesisMark $mark): RedirectResponse
    {
        $data = $request->validate(['notes' => ['nullable', 'string']]);

        return $this->run(function () use ($data, $mark): string {
            $this->workflow->ratifyMark($mark, $data['notes'] ?? null);

            return "Thesis mark ratified for {$mark->candidate->reg_no}.";
        });
    }

    public function returnMark(Request $request, PgThesisMark $mark): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5']]);

        return $this->run(function () use ($data, $mark): string {
            $this->workflow->returnMark($mark, $data['reason']);

            return 'Thesis mark returned to the examination board.';
        });
    }

    // ---------------------------------------------------- Resubmissions

    public function submitResubmission(Request $request, PgThesisResubmission $resubmission): RedirectResponse
    {
        $data = $request->validate(['corrections_summary' => ['required', 'string', 'min:10']]);

        return $this->run(function () use ($data, $resubmission): string {
            $this->workflow->submitResubmission($resubmission, $data['corrections_summary']);

            return "Corrections filed for cycle {$resubmission->cycle}.";
        });
    }

    public function verifyResubmission(Request $request, PgThesisResubmission $resubmission): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data, $resubmission): string {
            $this->workflow->verifyResubmission($resubmission, $data['decision'] === 'accept', $data['notes'] ?? null);

            return 'Resubmission '.($data['decision'] === 'accept' ? 'accepted' : 'rejected').'.';
        });
    }

    // ----------------------------------------------------- Publications

    public function submitPublication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'article_title' => ['required', 'string', 'max:190'],
            'journal_name' => ['required', 'string', 'max:190'],
            'doi' => ['nullable', 'string', 'max:190'],
            'indexed_in' => ['nullable', 'string', 'max:190'],
        ]);

        return $this->run(function () use ($data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $this->workflow->submitPublication(
                $candidate, $data['article_title'], $data['journal_name'], $data['doi'] ?? null, $data['indexed_in'] ?? null,
            );

            return 'Publication submitted for review.';
        });
    }

    public function decidePublication(Request $request, PgPublication $publication): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:UNDER_REVIEW,ACCEPTED,REJECTED'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->run(function () use ($data, $publication): string {
            $this->workflow->decidePublication($publication, $data['decision'], $data['notes'] ?? null);

            return "Publication marked {$data['decision']}.";
        });
    }

    // ---------------------------------------------------------- Legacy

    public function stageLegacyRecord(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'batch_reference' => ['required', 'string', 'max:60'],
            'source_module' => ['required', 'string', 'max:60'],
            'source_reference' => ['required', 'string', 'max:100'],
            'target_stage' => ['required', 'in:'.implode(',', PgResearchCandidate::STAGES)],
            'artifacts' => ['nullable', 'string'],
        ]);

        PgLegacyMigration::updateOrCreate(
            ['batch_reference' => $data['batch_reference'], 'source_reference' => $data['source_reference']],
            [...$data, 'status' => 'PENDING'],
        );

        return back()->with('success', "Legacy record {$data['source_reference']} staged for import.");
    }

    public function importLegacyRecord(PgLegacyMigration $migration): RedirectResponse
    {
        $row = $this->workflow->importLegacyRecord($migration);

        return $row->status === 'IMPORTED'
            ? back()->with('success', "Record {$row->source_reference} imported.")
            : back()->with('error', "Import failed: {$row->error_message}");
    }

    public function importLegacyBatch(Request $request): RedirectResponse
    {
        $data = $request->validate(['batch_reference' => ['required', 'string', 'max:60']]);

        $rows = PgLegacyMigration::where('batch_reference', $data['batch_reference'])
            ->whereIn('status', ['PENDING', 'FAILED'])
            ->get();

        $imported = $rows->filter(fn (PgLegacyMigration $r) => $this->workflow->importLegacyRecord($r)->status === 'IMPORTED')->count();
        $failed = $rows->count() - $imported;

        return back()->with(
            $failed === 0 ? 'success' : 'info',
            "Batch {$data['batch_reference']}: {$imported} imported, {$failed} failed.",
        );
    }

    public function verifyLegacyRecord(PgLegacyMigration $migration): RedirectResponse
    {
        return $this->run(function () use ($migration): string {
            $this->workflow->verifyLegacyRecord($migration);

            return "Record {$migration->source_reference} verified.";
        });
    }

    // ---------------------------------------------------------- Appeals

    public function storeAppealCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:pg_appeal_categories,code'],
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'applies_to' => ['required', 'in:MARKS,VIVA,SUPERVISION,PROGRESSION,OTHER'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'sla_days' => ['required', 'integer', 'min:1', 'max:180'],
            'requires_evidence' => ['nullable', 'boolean'],
        ]);

        $category = PgAppealCategory::create([
            ...$data,
            'code' => strtoupper($data['code']),
            'fee_amount' => (float) ($data['fee_amount'] ?? 0),
            'requires_evidence' => $request->boolean('requires_evidence'),
            'is_active' => true,
        ]);

        return back()->with('success', "Appeal category {$category->code} created.");
    }

    public function updateAppealCategory(Request $request, PgAppealCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'applies_to' => ['required', 'in:MARKS,VIVA,SUPERVISION,PROGRESSION,OTHER'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'sla_days' => ['required', 'integer', 'min:1', 'max:180'],
            'requires_evidence' => ['nullable', 'boolean'],
        ]);

        $category->update([
            ...$data,
            'fee_amount' => (float) ($data['fee_amount'] ?? 0),
            'requires_evidence' => $request->boolean('requires_evidence'),
        ]);

        return back()->with('success', "Appeal category {$category->code} updated.");
    }

    public function toggleAppealCategory(PgAppealCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with(
            'success',
            "Category {$category->code} ".($category->is_active ? 'activated' : 'deactivated').'.',
        );
    }

    public function storeAppealPeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:pg_appeal_categories,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'term_label' => ['required', 'string', 'max:40'],
            'opens_on' => ['required', 'date'],
            'closes_on' => ['required', 'date', 'after_or_equal:opens_on'],
            'notes' => ['nullable', 'string'],
        ]);

        $period = PgAppealPeriod::create([...$data, 'status' => 'DRAFT']);

        return back()->with('success', "Appeal window {$period->term_label} created as a draft.");
    }

    public function openAppealPeriod(PgAppealPeriod $period): RedirectResponse
    {
        return $this->run(function () use ($period): string {
            $this->workflow->openAppealPeriod($period);

            return "Appeal window {$period->term_label} is now open for submissions.";
        });
    }

    public function closeAppealPeriod(PgAppealPeriod $period): RedirectResponse
    {
        return $this->run(function () use ($period): string {
            $this->workflow->closeAppealPeriod($period);

            return "Appeal window {$period->term_label} closed.";
        });
    }

    public function lodgeAppeal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:pg_research_candidates,id'],
            'category_id' => ['required', 'exists:pg_appeal_categories,id'],
            'grounds' => ['required', 'string', 'min:20'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:10240'],
        ]);

        return $this->run(function () use ($request, $data): string {
            $candidate = PgResearchCandidate::findOrFail($data['candidate_id']);
            $path = $request->hasFile('evidence')
                ? $request->file('evidence')->store('pg-appeals', 'local')
                : null;

            $appeal = $this->workflow->lodgeAppeal($candidate, (int) $data['category_id'], $data['grounds'], $path);

            return "Appeal {$appeal->reference} lodged.";
        });
    }

    public function assignAppeal(Request $request, PgAppeal $appeal): RedirectResponse
    {
        $data = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        return $this->run(function () use ($data, $appeal): string {
            $this->workflow->assignAppeal($appeal, (int) $data['assigned_to']);

            return "Appeal {$appeal->reference} assigned for review.";
        });
    }

    public function decideAppeal(Request $request, PgAppeal $appeal): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:UPHELD,DISMISSED,SENT_BACK,WITHDRAWN'],
            'notes' => ['required', 'string', 'min:10'],
        ]);

        return $this->run(function () use ($data, $appeal): string {
            $this->workflow->decideAppeal($appeal, $data['decision'], $data['notes']);

            return "Appeal {$appeal->reference} marked {$data['decision']}.";
        });
    }

    /**
     * Workflow guards throw when a precondition fails; surface that as a real
     * error on the screen instead of a 500 or a fake success toast.
     */
    private function run(callable $operation): RedirectResponse
    {
        try {
            return back()->with('success', $operation());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
