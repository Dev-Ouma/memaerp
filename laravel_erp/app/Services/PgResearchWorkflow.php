<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PgResearch\PgAppeal;
use App\Models\PgResearch\PgDefenceRequest;
use App\Models\PgResearch\PgEligibilityWaiver;
use App\Models\PgResearch\PgExaminer;
use App\Models\PgResearch\PgExaminerReport;
use App\Models\PgResearch\PgLegacyMigration;
use App\Models\PgResearch\PgPlagiarismScan;
use App\Models\PgResearch\PgProgressReport;
use App\Models\PgResearch\PgProposal;
use App\Models\PgResearch\PgProposalReview;
use App\Models\PgResearch\PgPublication;
use App\Models\PgResearch\PgResearchCandidate;
use App\Models\PgResearch\PgResearchEvent;
use App\Models\PgResearch\PgSeminar;
use App\Models\PgResearch\PgSupervisor;
use App\Models\PgResearch\PgSupervisorAllocation;
use App\Models\PgResearch\PgThesisMark;
use App\Models\PgResearch\PgThesisResubmission;
use App\Models\PgResearch\PgVivaExamination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Owns every state mutation in the postgraduate research lifecycle. Screens read;
 * this service writes. Each transition validates its own preconditions and records
 * an event, so a status in the database always has a traceable cause.
 */
final class PgResearchWorkflow
{
    /**
     * Recompute the eligibility verdict from the facts on the candidate record
     * rather than storing an opinion someone typed. Coursework and fees gate
     * research registration (R19); an approved waiver downgrades a hard block
     * to provisional clearance instead of removing the gate.
     */
    public function recomputeEligibility(PgResearchCandidate $candidate): PgResearchCandidate
    {
        $candidate->loadMissing('waivers');
        $from = $candidate->eligibility_status;

        $verdict = match (true) {
            ! $candidate->feesCleared() && ! $candidate->hasActiveWaiver() => 'BLOCKED',
            $candidate->courseworkComplete() && $candidate->feesCleared() => 'ELIGIBLE',
            $candidate->hasActiveWaiver() => 'PROVISIONAL',
            default => 'PENDING',
        };

        if ($verdict !== $from) {
            $candidate->update(['eligibility_status' => $verdict]);
            $this->record($candidate, $candidate, 'eligibility.recomputed', $from, $verdict);
        }

        return $candidate->refresh();
    }

    public function requestWaiver(PgResearchCandidate $candidate, string $reason, string $type = 'R19_PROVISIONAL'): PgEligibilityWaiver
    {
        return DB::transaction(function () use ($candidate, $reason, $type): PgEligibilityWaiver {
            $waiver = $candidate->waivers()->create([
                'waiver_type' => $type,
                'reason' => $reason,
                'status' => 'PENDING',
                'requested_by' => Auth::id(),
            ]);

            $this->record($candidate, $waiver, 'waiver.requested', null, 'PENDING');

            return $waiver;
        });
    }

    public function decideWaiver(PgEligibilityWaiver $waiver, bool $approve, ?string $notes = null, ?string $expiresOn = null): PgEligibilityWaiver
    {
        $this->guard($waiver->status === 'PENDING', 'Only a pending waiver can be decided.');

        return DB::transaction(function () use ($waiver, $approve, $notes, $expiresOn): PgEligibilityWaiver {
            $from = $waiver->status;
            $waiver->update([
                'status' => $approve ? 'APPROVED' : 'REJECTED',
                'decided_by' => Auth::id(),
                'decided_at' => now(),
                'decision_notes' => $notes,
                'expires_on' => $approve ? $expiresOn : null,
            ]);

            $this->record($waiver->candidate, $waiver, 'waiver.decided', $from, $waiver->status);
            $this->recomputeEligibility($waiver->candidate->refresh());

            return $waiver->refresh();
        });
    }

    public function revokeWaiver(PgEligibilityWaiver $waiver, string $reason): PgEligibilityWaiver
    {
        $this->guard($waiver->status === 'APPROVED', 'Only an approved waiver can be revoked.');

        return DB::transaction(function () use ($waiver, $reason): PgEligibilityWaiver {
            $waiver->update([
                'status' => 'REVOKED',
                'decided_by' => Auth::id(),
                'decided_at' => now(),
                'decision_notes' => $reason,
            ]);

            $this->record($waiver->candidate, $waiver, 'waiver.revoked', 'APPROVED', 'REVOKED');
            $this->recomputeEligibility($waiver->candidate->refresh());

            return $waiver->refresh();
        });
    }

    /**
     * Allocate a supervisor. A candidate carries exactly one active lead at a
     * time, so promoting a new lead ends the incumbent's tenure in the same
     * transaction rather than leaving two rows claiming the same role.
     */
    public function allocateSupervisor(
        PgResearchCandidate $candidate,
        PgSupervisor $supervisor,
        string $role = 'LEAD',
        ?string $notes = null,
    ): PgSupervisorAllocation {
        $this->guard(in_array($role, ['LEAD', 'CO', 'EXTERNAL'], true), 'Unknown supervision role.');
        $this->guard($supervisor->is_active, 'Supervisor is not active.');
        $this->guard(
            $supervisor->activeLoad() < $supervisor->max_load,
            "{$supervisor->full_name} is at full supervision load ({$supervisor->max_load}).",
        );

        return DB::transaction(function () use ($candidate, $supervisor, $role, $notes): PgSupervisorAllocation {
            if ($role === 'LEAD') {
                $candidate->allocations()
                    ->where('role', 'LEAD')
                    ->where('status', 'ACTIVE')
                    ->update(['status' => 'REPLACED', 'ended_on' => now()->toDateString()]);
            }

            $allocation = PgSupervisorAllocation::updateOrCreate(
                ['candidate_id' => $candidate->id, 'supervisor_id' => $supervisor->id, 'role' => $role],
                ['status' => 'ACTIVE', 'assigned_on' => now()->toDateString(), 'ended_on' => null, 'notes' => $notes],
            );

            $this->record($candidate, $allocation, 'supervisor.allocated', null, 'ACTIVE', [
                'supervisor' => $supervisor->full_name,
                'role' => $role,
            ]);

            if ($candidate->stage === 'REGISTERED') {
                $this->advanceStage($candidate, 'PROPOSAL');
            }

            return $allocation;
        });
    }

    public function endAllocation(PgSupervisorAllocation $allocation, string $reason): PgSupervisorAllocation
    {
        $this->guard($allocation->status === 'ACTIVE', 'Only an active allocation can be ended.');

        $allocation->update(['status' => 'ENDED', 'ended_on' => now()->toDateString(), 'notes' => $reason]);
        $this->record($allocation->candidate, $allocation, 'supervisor.ended', 'ACTIVE', 'ENDED');

        return $allocation;
    }

    public function submitProposal(PgResearchCandidate $candidate, string $title, ?string $abstract, ?string $path = null): PgProposal
    {
        $this->guard(
            $candidate->eligibility_status !== 'BLOCKED',
            'A candidate blocked on eligibility cannot submit a proposal.',
        );

        return DB::transaction(function () use ($candidate, $title, $abstract, $path): PgProposal {
            $version = (int) $candidate->proposals()->max('version') + 1;

            $proposal = $candidate->proposals()->create([
                'title' => $title,
                'abstract' => $abstract,
                'version' => $version,
                'status' => 'SUBMITTED',
                'manuscript_path' => $path,
                'submitted_at' => now(),
            ]);

            $candidate->update(['thesis_title' => $title]);
            $this->record($candidate, $proposal, 'proposal.submitted', 'DRAFT', 'SUBMITTED');

            return $proposal;
        });
    }

    public function appointReader(PgProposal $proposal, PgSupervisor $reader): PgProposal
    {
        $this->guard(
            in_array($proposal->status, ['SUBMITTED', 'UNDER_REVIEW'], true),
            'A reader can only be appointed to a submitted proposal.',
        );

        $from = $proposal->status;
        $proposal->update(['reader_id' => $reader->id, 'status' => 'UNDER_REVIEW']);
        $this->record($proposal->candidate, $proposal, 'proposal.reader_appointed', $from, 'UNDER_REVIEW', [
            'reader' => $reader->full_name,
        ]);

        return $proposal->refresh();
    }

    /**
     * Record a reader verdict and move the proposal accordingly. The review row
     * is the evidence; the proposal status is the derived consequence.
     */
    public function recordProposalReview(PgProposal $proposal, string $verdict, string $comments, ?float $score = null): PgProposalReview
    {
        $allowed = ['APPROVE', 'MINOR_REVISION', 'MAJOR_REVISION', 'REJECT'];
        $this->guard(in_array($verdict, $allowed, true), 'Unknown proposal verdict.');
        $this->guard(
            in_array($proposal->status, ['SUBMITTED', 'UNDER_REVIEW'], true),
            'This proposal is not open for review.',
        );

        return DB::transaction(function () use ($proposal, $verdict, $comments, $score): PgProposalReview {
            $review = $proposal->reviews()->create([
                'reader_id' => $proposal->reader_id,
                'verdict' => $verdict,
                'comments' => $comments,
                'score' => $score,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $from = $proposal->status;
            $status = match ($verdict) {
                'APPROVE' => 'APPROVED',
                'REJECT' => 'REJECTED',
                default => 'REVISION_REQUIRED',
            };
            $proposal->update(['status' => $status]);

            $this->record($proposal->candidate, $proposal, 'proposal.reviewed', $from, $status, ['verdict' => $verdict]);

            if ($status === 'APPROVED') {
                $this->advanceStage($proposal->candidate, 'FIELDWORK');
            }

            return $review;
        });
    }

    public function scheduleSeminar(
        PgResearchCandidate $candidate,
        string $type,
        string $scheduledFor,
        string $venue,
        ?string $chair = null,
    ): PgSeminar {
        $seminar = $candidate->seminars()->create([
            'seminar_type' => $type,
            'scheduled_for' => $scheduledFor,
            'venue' => $venue,
            'panel_chair' => $chair,
            'status' => 'SCHEDULED',
        ]);

        $this->record($candidate, $seminar, 'seminar.scheduled', null, 'SCHEDULED');

        return $seminar;
    }

    public function recordSeminarOutcome(PgSeminar $seminar, string $status, ?string $notes = null, ?int $attendance = null): PgSeminar
    {
        $allowed = ['HELD', 'PASSED', 'FAILED', 'DEFERRED', 'CANCELLED'];
        $this->guard(in_array($status, $allowed, true), 'Unknown seminar outcome.');
        $this->guard($seminar->status === 'SCHEDULED', 'Only a scheduled seminar can be concluded.');

        $seminar->update([
            'status' => $status,
            'outcome_notes' => $notes,
            'attendance_count' => $attendance,
            'held_at' => in_array($status, ['HELD', 'PASSED', 'FAILED'], true) ? now() : null,
        ]);

        $this->record($seminar->candidate, $seminar, 'seminar.concluded', 'SCHEDULED', $status);

        return $seminar;
    }

    public function submitProgressReport(
        PgResearchCandidate $candidate,
        string $period,
        string $stage,
        string $summary,
    ): PgProgressReport {
        $report = $candidate->progressReports()->create([
            'period_label' => $period,
            'report_stage' => $stage,
            'milestone_summary' => $summary,
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        $this->record($candidate, $report, 'progress_report.submitted', null, 'SUBMITTED');

        return $report;
    }

    public function decideProgressReport(PgProgressReport $report, string $decision, ?string $comment = null): PgProgressReport
    {
        $allowed = ['APPROVED', 'RETURNED', 'UNDER_REVIEW'];
        $this->guard(in_array($decision, $allowed, true), 'Unknown progress report decision.');
        $this->guard($report->status !== 'APPROVED', 'This report is already approved.');

        $from = $report->status;
        $report->update([
            'status' => $decision,
            'supervisor_comment' => $comment,
            'decided_by' => Auth::id(),
            'decided_at' => now(),
        ]);

        $this->record($report->candidate, $report, 'progress_report.decided', $from, $decision);

        return $report;
    }

    /**
     * A scan passes or is flagged against its own threshold — the verdict is
     * computed from the numbers, never supplied by the caller.
     */
    public function recordScan(
        PgResearchCandidate $candidate,
        string $documentType,
        float $similarity,
        float $threshold = 15.0,
        ?string $reference = null,
        ?float $aiIndex = null,
        float $aiThreshold = 20.0,
    ): PgPlagiarismScan {
        $flagged = $similarity > $threshold || ($aiIndex !== null && $aiIndex > $aiThreshold);

        $scan = $candidate->scans()->create([
            'document_type' => $documentType,
            'similarity_index' => $similarity,
            'threshold' => $threshold,
            'ai_index' => $aiIndex,
            'ai_threshold' => $aiThreshold,
            'status' => $flagged ? 'FLAGGED' : 'PASSED',
            'report_reference' => $reference,
            'scanned_at' => now(),
        ]);

        $this->record($candidate, $scan, 'plagiarism.scanned', null, $scan->status, [
            'similarity' => $similarity,
            'ai_index' => $aiIndex,
        ]);

        return $scan;
    }

    public function overrideScan(PgPlagiarismScan $scan, string $notes): PgPlagiarismScan
    {
        $this->guard($scan->status === 'FLAGGED', 'Only a flagged scan needs an override.');

        $scan->update([
            'status' => 'CLEARED_BY_OVERRIDE',
            'reviewed_by' => Auth::id(),
            'review_notes' => $notes,
        ]);

        $this->record($scan->candidate, $scan, 'plagiarism.overridden', 'FLAGGED', 'CLEARED_BY_OVERRIDE');

        return $scan;
    }

    /**
     * Defence clearance is the gate that must not be bypassed: an unresolved
     * similarity flag blocks the request outright.
     */
    public function requestDefence(PgResearchCandidate $candidate, string $thesisTitle): PgDefenceRequest
    {
        $scan = $candidate->scans()->where('document_type', 'THESIS')->latest('scanned_at')->first();

        $this->guard($scan !== null, 'A thesis similarity scan is required before requesting defence.');
        $this->guard($scan->status !== 'FLAGGED', 'Resolve the flagged similarity report before requesting defence.');
        $this->guard($candidate->eligibility_status !== 'BLOCKED', 'Candidate is blocked on eligibility.');
        $this->guard(
            ! $candidate->defenceRequests()->where('status', 'PENDING')->exists(),
            'A defence request is already pending for this candidate.',
        );

        return DB::transaction(function () use ($candidate, $thesisTitle, $scan): PgDefenceRequest {
            $request = $candidate->defenceRequests()->create([
                'plagiarism_scan_id' => $scan->id,
                'thesis_title' => $thesisTitle,
                'status' => 'PENDING',
                'requested_at' => now(),
            ]);

            $candidate->update(['thesis_title' => $thesisTitle]);
            $this->record($candidate, $request, 'defence.requested', null, 'PENDING');

            return $request;
        });
    }

    public function decideDefence(PgDefenceRequest $request, string $decision, ?string $notes = null): PgDefenceRequest
    {
        $allowed = ['APPROVED', 'RETURNED', 'REJECTED'];
        $this->guard(in_array($decision, $allowed, true), 'Unknown defence decision.');
        $this->guard($request->status === 'PENDING', 'This defence request has already been decided.');

        return DB::transaction(function () use ($request, $decision, $notes): PgDefenceRequest {
            $request->update([
                'status' => $decision,
                'decision_notes' => $notes,
                'decided_by' => Auth::id(),
                'decided_at' => now(),
            ]);

            $this->record($request->candidate, $request, 'defence.decided', 'PENDING', $decision);

            if ($decision === 'APPROVED') {
                $this->advanceStage($request->candidate, 'DEFENCE');
            }

            return $request;
        });
    }

    public function appointExaminer(
        PgResearchCandidate $candidate,
        string $name,
        string $type,
        ?string $institution = null,
        ?string $email = null,
    ): PgExaminer {
        $this->guard(in_array($type, ['INTERNAL', 'EXTERNAL', 'CHAIR'], true), 'Unknown examiner type.');
        $this->guard(
            $candidate->defenceRequests()->where('status', 'APPROVED')->exists(),
            'Examiners can only be appointed after defence clearance is approved.',
        );

        $examiner = $candidate->examiners()->create([
            'examiner_name' => $name,
            'examiner_type' => $type,
            'institution' => $institution,
            'email' => $email,
            'appointed_on' => now()->toDateString(),
            'status' => 'APPOINTED',
        ]);

        $this->record($candidate, $examiner, 'examiner.appointed', 'NOMINATED', 'APPOINTED');

        return $examiner;
    }

    public function submitExaminerReport(
        PgExaminer $examiner,
        string $recommendation,
        string $remarks,
        ?float $score = null,
    ): PgExaminerReport {
        $allowed = ['PASS', 'MINOR', 'MAJOR', 'REEXAMINE', 'FAIL'];
        $this->guard(in_array($recommendation, $allowed, true), 'Unknown examiner recommendation.');
        $this->guard($examiner->report === null, 'This examiner has already filed a report.');

        return DB::transaction(function () use ($examiner, $recommendation, $remarks, $score): PgExaminerReport {
            $report = $examiner->report()->create([
                'candidate_id' => $examiner->candidate_id,
                'recommendation' => $recommendation,
                'score' => $score,
                'remarks' => $remarks,
                'submitted_at' => now(),
            ]);

            $examiner->update(['status' => 'REPORT_SUBMITTED']);
            $this->record($examiner->candidate, $report, 'examiner_report.submitted', 'APPOINTED', 'REPORT_SUBMITTED');

            return $report;
        });
    }

    /**
     * A viva needs the examiner panel's reports in hand; scheduling before they
     * arrive is the failure this gate exists to prevent.
     */
    public function scheduleViva(PgResearchCandidate $candidate, string $scheduledFor, string $venue, ?string $chair = null): PgVivaExamination
    {
        $appointed = $candidate->examiners()->whereIn('status', ['APPOINTED', 'REPORT_SUBMITTED'])->count();
        $reported = $candidate->examiners()->where('status', 'REPORT_SUBMITTED')->count();

        $this->guard($appointed > 0, 'Appoint the examiner panel before scheduling the viva.');
        $this->guard($reported === $appointed, 'All appointed examiners must file reports before the viva is scheduled.');

        return DB::transaction(function () use ($candidate, $scheduledFor, $venue, $chair): PgVivaExamination {
            $viva = PgVivaExamination::create([
                'candidate_id' => $candidate->id,
                'scheduled_for' => $scheduledFor,
                'venue' => $venue,
                'chair_name' => $chair,
                'status' => 'SCHEDULED',
            ]);

            $this->advanceStage($candidate, 'EXAMINATION');
            $this->record($candidate, $viva, 'viva.scheduled', null, 'SCHEDULED');

            return $viva;
        });
    }

    /**
     * The viva verdict drives the rest of the lifecycle: a clean pass produces a
     * ratifiable mark, a corrections verdict opens a resubmission cycle with a
     * real due date, and a re-examination sends the candidate back to writing.
     */
    public function recordVivaVerdict(PgVivaExamination $viva, string $verdict, string $notes): PgVivaExamination
    {
        $this->guard(in_array($verdict, PgVivaExamination::VERDICTS, true), 'Unknown viva verdict.');
        $this->guard($viva->status === 'SCHEDULED', 'This viva has already been concluded.');

        return DB::transaction(function () use ($viva, $verdict, $notes): PgVivaExamination {
            $viva->update([
                'status' => 'HELD',
                'verdict' => $verdict,
                'verdict_notes' => $notes,
                'verdict_recorded_by' => Auth::id(),
                'verdict_recorded_at' => now(),
            ]);

            $candidate = $viva->candidate;
            $this->record($candidate, $viva, 'viva.verdict_recorded', 'SCHEDULED', $verdict);

            if (in_array($verdict, ['PASS_MINOR', 'PASS_MAJOR'], true)) {
                $this->openResubmission($candidate, $verdict === 'PASS_MINOR' ? 90 : 180);
            }

            if ($verdict === 'REEXAMINE') {
                $this->advanceStage($candidate, 'WRITING');
            }

            if ($verdict === 'PASS') {
                $this->seedMarkFromPanel($candidate);
            }

            return $viva;
        });
    }

    public function openResubmission(PgResearchCandidate $candidate, int $dueInDays): PgThesisResubmission
    {
        $cycle = (int) $candidate->resubmissions()->max('cycle') + 1;

        $resubmission = $candidate->resubmissions()->create([
            'cycle' => $cycle,
            'due_on' => now()->addDays($dueInDays)->toDateString(),
            'status' => 'AWAITING',
        ]);

        $this->record($candidate, $resubmission, 'resubmission.opened', null, 'AWAITING', ['cycle' => $cycle]);

        return $resubmission;
    }

    public function submitResubmission(PgThesisResubmission $resubmission, string $summary): PgThesisResubmission
    {
        $this->guard($resubmission->status === 'AWAITING', 'This resubmission cycle is not awaiting a submission.');

        $resubmission->update([
            'status' => 'SUBMITTED',
            'corrections_summary' => $summary,
            'submitted_at' => now(),
        ]);

        $this->record($resubmission->candidate, $resubmission, 'resubmission.submitted', 'AWAITING', 'SUBMITTED');

        return $resubmission;
    }

    public function verifyResubmission(PgThesisResubmission $resubmission, bool $accept, ?string $notes = null): PgThesisResubmission
    {
        $this->guard(
            in_array($resubmission->status, ['SUBMITTED', 'UNDER_REVIEW'], true),
            'Only a submitted resubmission can be verified.',
        );

        return DB::transaction(function () use ($resubmission, $accept, $notes): PgThesisResubmission {
            $from = $resubmission->status;
            $resubmission->update([
                'status' => $accept ? 'ACCEPTED' : 'REJECTED',
                'corrections_summary' => $notes ?? $resubmission->corrections_summary,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            $this->record($resubmission->candidate, $resubmission, 'resubmission.verified', $from, $resubmission->status);

            if ($accept) {
                $this->seedMarkFromPanel($resubmission->candidate);
            }

            return $resubmission;
        });
    }

    /**
     * Derive the composite mark from the examiner panel's scores instead of
     * asking a clerk to retype an average.
     */
    public function seedMarkFromPanel(PgResearchCandidate $candidate): ?PgThesisMark
    {
        $scores = PgExaminerReport::query()
            ->where('candidate_id', $candidate->id)
            ->whereNotNull('score')
            ->pluck('score');

        if ($scores->isEmpty()) {
            return null;
        }

        $composite = round((float) $scores->avg(), 2);

        $mark = PgThesisMark::updateOrCreate(
            ['candidate_id' => $candidate->id],
            ['composite_score' => $composite, 'final_grade' => $this->gradeFor($composite), 'status' => 'SUBMITTED'],
        );

        $this->record($candidate, $mark, 'thesis_mark.computed', null, 'SUBMITTED', ['composite' => $composite]);

        return $mark;
    }

    public function ratifyMark(PgThesisMark $mark, ?string $notes = null): PgThesisMark
    {
        $this->guard($mark->status === 'SUBMITTED', 'Only a submitted mark can be ratified.');

        return DB::transaction(function () use ($mark, $notes): PgThesisMark {
            $mark->update([
                'status' => 'RATIFIED',
                'ratified_by' => Auth::id(),
                'ratified_at' => now(),
                'notes' => $notes,
            ]);

            $this->record($mark->candidate, $mark, 'thesis_mark.ratified', 'SUBMITTED', 'RATIFIED');
            $this->advanceStage($mark->candidate, 'COMPLETE');

            return $mark;
        });
    }

    public function returnMark(PgThesisMark $mark, string $reason): PgThesisMark
    {
        $this->guard($mark->status === 'SUBMITTED', 'Only a submitted mark can be returned.');

        $mark->update(['status' => 'RETURNED', 'notes' => $reason]);
        $this->record($mark->candidate, $mark, 'thesis_mark.returned', 'SUBMITTED', 'RETURNED');

        return $mark;
    }

    public function submitPublication(
        PgResearchCandidate $candidate,
        string $title,
        string $journal,
        ?string $doi = null,
        ?string $indexedIn = null,
    ): PgPublication {
        $publication = $candidate->publications()->create([
            'article_title' => $title,
            'journal_name' => $journal,
            'doi' => $doi,
            'indexed_in' => $indexedIn,
            'status' => 'SUBMITTED',
        ]);

        $this->record($candidate, $publication, 'publication.submitted', null, 'SUBMITTED');

        return $publication;
    }

    public function decidePublication(PgPublication $publication, string $decision, ?string $notes = null): PgPublication
    {
        $allowed = ['UNDER_REVIEW', 'ACCEPTED', 'REJECTED'];
        $this->guard(in_array($decision, $allowed, true), 'Unknown publication decision.');
        $this->guard(
            ! in_array($publication->status, ['ACCEPTED', 'REJECTED'], true),
            'This publication has already been decided.',
        );

        $from = $publication->status;
        $publication->update([
            'status' => $decision,
            'review_notes' => $notes,
            'decided_by' => Auth::id(),
            'decided_at' => now(),
        ]);

        $this->record($publication->candidate, $publication, 'publication.decided', $from, $decision);

        return $publication;
    }

    /**
     * Import a legacy research record. Failures are recorded on the row rather
     * than thrown away, so a batch reconciles instead of silently shrinking.
     */
    public function importLegacyRecord(PgLegacyMigration $row): PgLegacyMigration
    {
        $this->guard(in_array($row->status, ['PENDING', 'FAILED'], true), 'This record has already been imported.');

        try {
            return DB::transaction(function () use ($row): PgLegacyMigration {
                $candidate = $row->candidate_id
                    ? $row->candidate
                    : PgResearchCandidate::where('reg_no', $row->source_reference)->first();

                if ($candidate === null) {
                    throw new RuntimeException("No candidate matches reference {$row->source_reference}.");
                }

                $row->update([
                    'candidate_id' => $candidate->id,
                    'status' => 'IMPORTED',
                    'imported_by' => Auth::id(),
                    'imported_at' => now(),
                    'error_message' => null,
                ]);

                $this->advanceStage($candidate, $row->target_stage);
                $this->record($candidate, $row, 'legacy.imported', 'PENDING', 'IMPORTED');

                return $row;
            });
        } catch (RuntimeException $e) {
            $row->update(['status' => 'FAILED', 'error_message' => $e->getMessage()]);
            $this->record($row->candidate, $row, 'legacy.failed', 'PENDING', 'FAILED', ['error' => $e->getMessage()]);

            return $row;
        }
    }

    public function verifyLegacyRecord(PgLegacyMigration $row): PgLegacyMigration
    {
        $this->guard($row->status === 'IMPORTED', 'Only an imported record can be verified.');

        $row->update(['status' => 'VERIFIED']);
        $this->record($row->candidate, $row, 'legacy.verified', 'IMPORTED', 'VERIFIED');

        return $row;
    }

    // ---------------------------------------------------------------- Appeals

    /**
     * An appeal is only accepted inside an open window for its category — the
     * period is a gate, not a label on a dashboard.
     */
    public function lodgeAppeal(
        PgResearchCandidate $candidate,
        int $categoryId,
        string $grounds,
        ?string $evidencePath = null,
    ): PgAppeal {
        $category = \App\Models\PgResearch\PgAppealCategory::findOrFail($categoryId);
        $this->guard($category->is_active, 'This appeal category is not currently active.');

        $period = \App\Models\PgResearch\PgAppealPeriod::query()
            ->where('status', 'OPEN')
            ->where(fn ($q) => $q->where('category_id', $category->id)->orWhereNull('category_id'))
            ->whereDate('opens_on', '<=', now())
            ->whereDate('closes_on', '>=', now())
            ->orderByRaw('category_id nulls last')
            ->first();

        $this->guard($period !== null, 'No appeal window is open for this category.');
        $this->guard(
            ! $category->requires_evidence || $evidencePath !== null,
            'This appeal category requires supporting evidence.',
        );

        return DB::transaction(function () use ($candidate, $category, $period, $grounds, $evidencePath): PgAppeal {
            $appeal = $candidate->appeals()->create([
                'category_id' => $category->id,
                'period_id' => $period->id,
                'reference' => $this->nextAppealReference(),
                'grounds' => $grounds,
                'evidence_path' => $evidencePath,
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            $this->record($candidate, $appeal, 'appeal.lodged', null, 'SUBMITTED', ['category' => $category->code]);

            return $appeal;
        });
    }

    public function assignAppeal(PgAppeal $appeal, int $userId): PgAppeal
    {
        $this->guard(! in_array($appeal->status, PgAppeal::TERMINAL, true), 'This appeal is already closed.');

        $from = $appeal->status;
        $appeal->update(['assigned_to' => $userId, 'status' => 'UNDER_REVIEW']);
        $this->record($appeal->candidate, $appeal, 'appeal.assigned', $from, 'UNDER_REVIEW', ['assignee' => $userId]);

        return $appeal;
    }

    public function decideAppeal(PgAppeal $appeal, string $decision, string $notes): PgAppeal
    {
        $allowed = ['UPHELD', 'DISMISSED', 'SENT_BACK', 'WITHDRAWN'];
        $this->guard(in_array($decision, $allowed, true), 'Unknown appeal decision.');
        $this->guard(! in_array($appeal->status, PgAppeal::TERMINAL, true), 'This appeal is already closed.');

        $from = $appeal->status;
        $appeal->update([
            'status' => $decision,
            'decision_notes' => $notes,
            'decided_by' => Auth::id(),
            'decided_at' => in_array($decision, PgAppeal::TERMINAL, true) ? now() : null,
        ]);

        $this->record($appeal->candidate, $appeal, 'appeal.decided', $from, $decision);

        return $appeal;
    }

    public function openAppealPeriod(\App\Models\PgResearch\PgAppealPeriod $period): \App\Models\PgResearch\PgAppealPeriod
    {
        $this->guard($period->status === 'DRAFT', 'Only a draft window can be opened.');

        $period->update(['status' => 'OPEN']);
        $this->record(null, $period, 'appeal_period.opened', 'DRAFT', 'OPEN');

        return $period;
    }

    public function closeAppealPeriod(\App\Models\PgResearch\PgAppealPeriod $period): \App\Models\PgResearch\PgAppealPeriod
    {
        $this->guard($period->status === 'OPEN', 'Only an open window can be closed.');

        $period->update(['status' => 'CLOSED']);
        $this->record(null, $period, 'appeal_period.closed', 'OPEN', 'CLOSED');

        return $period;
    }

    // ---------------------------------------------------------------- Helpers

    public function advanceStage(PgResearchCandidate $candidate, string $stage): PgResearchCandidate
    {
        $this->guard(in_array($stage, PgResearchCandidate::STAGES, true), "Unknown research stage: {$stage}.");

        if ($candidate->stage === $stage) {
            return $candidate;
        }

        $from = $candidate->stage;
        $candidate->update(['stage' => $stage]);
        $this->record($candidate, $candidate, 'stage.advanced', $from, $stage);

        return $candidate;
    }

    public function gradeFor(float $score): string
    {
        return match (true) {
            $score >= 75 => 'Distinction',
            $score >= 65 => 'Credit',
            $score >= 50 => 'Pass',
            default => 'Fail',
        };
    }

    private function nextAppealReference(): string
    {
        $year = now()->year;
        $sequence = PgAppeal::whereYear('submitted_at', $year)->count() + 1;

        return sprintf('APL/%d/%04d', $year, $sequence);
    }

    private function record(
        ?PgResearchCandidate $candidate,
        Model $subject,
        string $action,
        ?string $from,
        ?string $to,
        array $payload = [],
    ): void {
        PgResearchEvent::create([
            'candidate_id' => $candidate?->id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'actor_id' => Auth::id(),
            'payload' => $payload ?: null,
            'created_at' => now(),
        ]);
    }

    private function guard(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }
}
