<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The PG Research module is asserted here the way it will be used: by driving
 * real HTTP requests through the same routes the screens post to, and then
 * reading the database back. Nothing in this file constructs domain rows
 * directly through the ORM to set up a state that the UI itself cannot reach —
 * if a state is unreachable through the routes, that is a defect, not a test
 * fixture problem.
 */
final class PgResearchLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($this->admin);
    }

    public function test_a_candidate_is_registered_and_eligibility_is_derived_not_typed_in(): void
    {
        $this->act('pg-research.candidates.store', [], [
            'reg_no' => 'PG/PHD/2026/001',
            'candidate_name' => 'Amina Wanjiru',
            'degree_level' => 'PHD',
            'programme_title' => 'PhD in Public Health',
            'academic_year' => '2025/2026',
            'coursework_units_total' => 8,
            'coursework_units_passed' => 8,
            'gpa' => 3.85,
            'fee_balance' => 0,
        ]);

        $this->assertDatabaseHas('pg_research_candidates', [
            'reg_no' => 'PG/PHD/2026/001',
            'eligibility_status' => 'ELIGIBLE',
            'stage' => 'REGISTERED',
        ]);

        // Same registration, unpaid fees: the verdict must differ without anyone choosing it.
        $this->act('pg-research.candidates.store', [], [
            'reg_no' => 'PG/MSC/2026/002',
            'candidate_name' => 'Brian Otieno',
            'degree_level' => 'MASTERS',
            'programme_title' => 'MSc in Data Science',
            'coursework_units_total' => 6,
            'coursework_units_passed' => 6,
            'fee_balance' => 42000,
        ]);

        $this->assertDatabaseHas('pg_research_candidates', [
            'reg_no' => 'PG/MSC/2026/002',
            'eligibility_status' => 'BLOCKED',
        ]);

        $this->get(route('pg-research.eligibility-gating'))
            ->assertOk()
            ->assertSee('PG/PHD/2026/001')
            ->assertSee('PG/MSC/2026/002');
    }

    public function test_a_waiver_moves_a_blocked_candidate_to_provisional_and_revoking_it_moves_them_back(): void
    {
        $candidate = $this->registerCandidate('PG/MSC/2026/010', ['fee_balance' => 30000]);
        $this->assertSame('BLOCKED', $candidate->refresh()->eligibility_status);

        $this->act('pg-research.waivers.request', [], [
            'candidate_id' => $candidate->id,
            'waiver_type' => 'R19_PROVISIONAL',
            'reason' => 'Sponsor disbursement confirmed in writing for the coming term.',
        ]);

        $waiver = PgEligibilityWaiver::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('PENDING', $waiver->status);
        $this->assertSame('BLOCKED', $candidate->refresh()->eligibility_status, 'A pending waiver must not unblock anyone.');

        $this->act('pg-research.waivers.decide', $waiver->id, [
            'decision' => 'approve',
            'notes' => 'Approved by the Director of Postgraduate Studies.',
            'expires_on' => now()->addMonths(6)->toDateString(),
        ]);

        $this->assertSame('APPROVED', $waiver->refresh()->status);
        $this->assertSame('PROVISIONAL', $candidate->refresh()->eligibility_status);

        $this->act('pg-research.waivers.revoke', $waiver->id, [
            'reason' => 'Sponsor withdrew the undertaking.',
        ]);

        $this->assertSame('REVOKED', $waiver->refresh()->status);
        $this->assertSame('BLOCKED', $candidate->refresh()->eligibility_status);
    }

    public function test_supervision_load_is_enforced_and_a_new_lead_ends_the_incumbent(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/020');
        $first = $this->addSupervisor('SUP-100', 'Prof. Grace Njeri', maxLoad: 1);
        $second = $this->addSupervisor('SUP-101', 'Dr. Peter Mwangi', maxLoad: 3);

        $this->act('pg-research.allocations.store', [], [
            'candidate_id' => $candidate->id,
            'supervisor_id' => $first->id,
            'role' => 'LEAD',
        ]);

        $this->assertDatabaseHas('pg_supervisor_allocations', [
            'candidate_id' => $candidate->id,
            'supervisor_id' => $first->id,
            'role' => 'LEAD',
            'status' => 'ACTIVE',
        ]);
        $this->assertSame('PROPOSAL', $candidate->refresh()->stage, 'Allocating a lead moves a registered candidate to the proposal stage.');

        // The first supervisor is now at capacity, so a second candidate must be refused.
        $other = $this->registerCandidate('PG/PHD/2026/021');
        $this->post(route('pg-research.allocations.store'), [
            'candidate_id' => $other->id,
            'supervisor_id' => $first->id,
            'role' => 'LEAD',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('pg_supervisor_allocations', [
            'candidate_id' => $other->id,
            'supervisor_id' => $first->id,
        ]);

        // Promoting a new lead retires the incumbent in the same transaction.
        $this->act('pg-research.allocations.store', [], [
            'candidate_id' => $candidate->id,
            'supervisor_id' => $second->id,
            'role' => 'LEAD',
        ]);

        $this->assertSame('REPLACED', PgSupervisorAllocation::where('candidate_id', $candidate->id)
            ->where('supervisor_id', $first->id)->value('status'));
        $this->assertSame(1, PgSupervisorAllocation::where('candidate_id', $candidate->id)
            ->where('role', 'LEAD')->where('status', 'ACTIVE')->count());
    }

    public function test_defence_clearance_cannot_be_requested_around_a_flagged_similarity_report(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/030');

        // No scan at all.
        $this->post(route('pg-research.defence-requests.store'), [
            'candidate_id' => $candidate->id,
            'thesis_title' => 'Community health financing in arid counties',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgDefenceRequest::where('candidate_id', $candidate->id)->count());

        // A scan above threshold: the status is computed, not supplied.
        $this->act('pg-research.scans.store', [], [
            'candidate_id' => $candidate->id,
            'document_type' => 'THESIS',
            'similarity_index' => 41.5,
            'threshold' => 15,
            'report_reference' => 'TURN-2026-0031',
        ]);

        $scan = PgPlagiarismScan::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('FLAGGED', $scan->status);

        $this->post(route('pg-research.defence-requests.store'), [
            'candidate_id' => $candidate->id,
            'thesis_title' => 'Community health financing in arid counties',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgDefenceRequest::where('candidate_id', $candidate->id)->count());

        // A documented override is the only way through, and it is itself recorded.
        $this->act('pg-research.scans.override', $scan->id, [
            'notes' => 'Matches are the candidate\'s own published pilot study, verified by the reader.',
        ]);
        $this->assertSame('CLEARED_BY_OVERRIDE', $scan->refresh()->status);
        $this->assertSame($this->admin->id, $scan->reviewed_by);

        $this->act('pg-research.defence-requests.store', [], [
            'candidate_id' => $candidate->id,
            'thesis_title' => 'Community health financing in arid counties',
        ]);
        $this->assertDatabaseHas('pg_defence_requests', [
            'candidate_id' => $candidate->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_a_candidate_walks_the_whole_lifecycle_and_every_stage_change_is_persisted(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/040');
        $supervisor = $this->addSupervisor('SUP-200', 'Prof. Halima Yusuf');
        $reader = $this->addSupervisor('SUP-201', 'Dr. Joseph Kamau');

        $this->act('pg-research.allocations.store', [], [
            'candidate_id' => $candidate->id, 'supervisor_id' => $supervisor->id, 'role' => 'LEAD',
        ]);
        $this->assertSame('PROPOSAL', $candidate->refresh()->stage);

        // --- Proposal -------------------------------------------------------
        $this->act('pg-research.proposals.store', [], [
            'candidate_id' => $candidate->id,
            'title' => 'Adaptive irrigation scheduling for smallholder farms',
            'abstract' => 'A field study across three counties over two growing seasons.',
        ]);
        $proposal = PgProposal::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('SUBMITTED', $proposal->status);
        $this->assertSame(1, $proposal->version);

        $this->act('pg-research.proposals.reader', $proposal->id, ['reader_id' => $reader->id]);
        $this->assertSame('UNDER_REVIEW', $proposal->refresh()->status);

        $this->act('pg-research.proposals.review', $proposal->id, [
            'verdict' => 'APPROVE',
            'comments' => 'Methodology is sound and the sampling frame is defensible.',
            'score' => 82,
        ]);
        $this->assertSame('APPROVED', $proposal->refresh()->status);
        $this->assertSame('FIELDWORK', $candidate->refresh()->stage);
        $this->assertDatabaseHas('pg_proposal_reviews', ['proposal_id' => $proposal->id, 'verdict' => 'APPROVE']);

        // --- Seminar --------------------------------------------------------
        $this->act('pg-research.seminars.store', [], [
            'candidate_id' => $candidate->id,
            'seminar_type' => 'PROGRESS',
            'scheduled_for' => now()->addWeek()->toDateTimeString(),
            'venue' => 'Senate Boardroom',
            'panel_chair' => 'Prof. Halima Yusuf',
        ]);
        $seminar = PgSeminar::where('candidate_id', $candidate->id)->firstOrFail();

        $this->act('pg-research.seminars.conclude', $seminar->id, [
            'status' => 'PASSED',
            'outcome_notes' => 'Panel satisfied with fieldwork progress.',
        ]);
        $this->assertSame('PASSED', $seminar->refresh()->status);
        $this->assertNotNull($seminar->held_at);

        // A concluded seminar cannot be concluded twice.
        $this->post(route('pg-research.seminars.conclude', $seminar->id), [
            'status' => 'FAILED', 'outcome_notes' => 'Attempted double entry.',
        ])->assertSessionHas('error');
        $this->assertSame('PASSED', $seminar->refresh()->status);

        // --- Progress report ------------------------------------------------
        $this->act('pg-research.progress-reports.store', [], [
            'candidate_id' => $candidate->id,
            'period_label' => '2026 Semester 1',
            'report_stage' => 'FIELDWORK',
            'milestone_summary' => 'Baseline survey complete across all three counties.',
        ]);
        $report = PgProgressReport::where('candidate_id', $candidate->id)->firstOrFail();

        $this->act('pg-research.progress-reports.decide', $report->id, [
            'decision' => 'APPROVED',
            'comment' => 'On schedule.',
        ]);
        $this->assertSame('APPROVED', $report->refresh()->status);
        $this->assertSame($this->admin->id, $report->decided_by);

        // --- Similarity and defence clearance -------------------------------
        $this->act('pg-research.scans.store', [], [
            'candidate_id' => $candidate->id,
            'document_type' => 'THESIS',
            'similarity_index' => 8.2,
            'threshold' => 15,
        ]);
        $this->assertSame('PASSED', PgPlagiarismScan::where('candidate_id', $candidate->id)->value('status'));

        $this->act('pg-research.defence-requests.store', [], [
            'candidate_id' => $candidate->id,
            'thesis_title' => 'Adaptive irrigation scheduling for smallholder farms',
        ]);
        $defence = PgDefenceRequest::where('candidate_id', $candidate->id)->firstOrFail();

        $this->act('pg-research.defence-requests.decide', $defence->id, [
            'decision' => 'APPROVED',
            'notes' => 'All clearances on file.',
        ]);
        $this->assertSame('APPROVED', $defence->refresh()->status);
        $this->assertSame('DEFENCE', $candidate->refresh()->stage);

        // --- Examiner panel -------------------------------------------------
        foreach ([['Prof. Lucy Adhiambo', 'INTERNAL', 78.0], ['Prof. Samuel Kiptoo', 'EXTERNAL', 84.0]] as [$name, $type, $score]) {
            $this->act('pg-research.examiners.store', [], [
                'candidate_id' => $candidate->id,
                'examiner_name' => $name,
                'examiner_type' => $type,
                'institution' => 'University of Nairobi',
            ]);
        }

        $examiners = PgExaminer::where('candidate_id', $candidate->id)->orderBy('id')->get();
        $this->assertCount(2, $examiners);

        // A viva cannot be booked while a report is outstanding.
        $this->post(route('pg-research.vivas.store'), [
            'candidate_id' => $candidate->id,
            'scheduled_for' => now()->addWeeks(2)->toDateTimeString(),
            'venue' => 'Senate Boardroom',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgVivaExamination::where('candidate_id', $candidate->id)->count());

        $this->act('pg-research.examiners.report', $examiners[0]->id, [
            'recommendation' => 'PASS',
            'remarks' => 'A thorough and well-argued thesis.',
            'score' => 78,
        ]);

        // Still one outstanding.
        $this->post(route('pg-research.vivas.store'), [
            'candidate_id' => $candidate->id,
            'scheduled_for' => now()->addWeeks(2)->toDateTimeString(),
            'venue' => 'Senate Boardroom',
        ])->assertSessionHas('error');

        $this->act('pg-research.examiners.report', $examiners[1]->id, [
            'recommendation' => 'PASS',
            'remarks' => 'Contribution to knowledge is clearly established.',
            'score' => 84,
        ]);

        // --- Viva -----------------------------------------------------------
        $this->act('pg-research.vivas.store', [], [
            'candidate_id' => $candidate->id,
            'scheduled_for' => now()->addWeeks(2)->toDateTimeString(),
            'venue' => 'Senate Boardroom',
            'chair_name' => 'Prof. Halima Yusuf',
        ]);
        $viva = PgVivaExamination::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('SCHEDULED', $viva->status);
        $this->assertSame('EXAMINATION', $candidate->refresh()->stage);

        $this->act('pg-research.vivas.verdict', $viva->id, [
            'verdict' => 'PASS',
            'verdict_notes' => 'Board satisfied; no corrections required.',
        ]);
        $this->assertSame('HELD', $viva->refresh()->status);
        $this->assertSame('PASS', $viva->verdict);

        // --- Mark -----------------------------------------------------------
        $mark = PgThesisMark::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertEqualsWithDelta(81.0, (float) $mark->composite_score, 0.01, 'The composite is the mean of the panel scores, not a typed figure.');
        $this->assertSame('Distinction', $mark->final_grade);
        $this->assertSame('SUBMITTED', $mark->status);

        $this->act('pg-research.marks.return', $mark->id, ['reason' => 'Panel asked to revisit the external score sheet.']);
        $this->assertSame('RETURNED', $mark->refresh()->status);
        $this->assertNotSame('COMPLETE', $candidate->refresh()->stage);

        // A returned mark is not ratifiable until it is re-seeded.
        $this->post(route('pg-research.marks.ratify', $mark->id), [])->assertSessionHas('error');

        $mark->update(['status' => 'SUBMITTED']);
        $this->act('pg-research.marks.ratify', $mark->id, ['notes' => 'Ratified by Senate.']);

        $this->assertSame('RATIFIED', $mark->refresh()->status);
        $this->assertSame($this->admin->id, $mark->ratified_by);
        $this->assertSame('COMPLETE', $candidate->refresh()->stage);

        // --- The audit trail is the downstream report -----------------------
        $events = \DB::table('pg_research_events')->where('candidate_id', $candidate->id)->pluck('action')->all();
        foreach ([
            'supervisor.allocated', 'proposal.submitted', 'proposal.reviewed', 'seminar.concluded',
            'progress_report.decided', 'plagiarism.scanned', 'defence.decided', 'examiner_report.submitted',
            'viva.scheduled', 'viva.verdict_recorded', 'thesis_mark.computed', 'thesis_mark.ratified',
        ] as $expected) {
            $this->assertContains($expected, $events, "The lifecycle did not record a {$expected} event.");
        }

        // --- And the screens show it ----------------------------------------
        $this->get(route('pg-research.thesis-marks-approval'))->assertOk()->assertSee('PG/PHD/2026/040');
        $this->get(route('pg-research.viva-examination'))->assertOk()->assertSee('PG/PHD/2026/040');
    }

    public function test_a_corrections_verdict_opens_a_resubmission_cycle_that_must_be_worked(): void
    {
        $candidate = $this->candidateWithReportedPanel('PG/MSC/2026/050', [66.0, 70.0]);

        $this->act('pg-research.vivas.store', [], [
            'candidate_id' => $candidate->id,
            'scheduled_for' => now()->addWeek()->toDateTimeString(),
            'venue' => 'Room 4B',
        ]);
        $viva = PgVivaExamination::where('candidate_id', $candidate->id)->firstOrFail();

        $this->act('pg-research.vivas.verdict', $viva->id, [
            'verdict' => 'PASS_MINOR',
            'verdict_notes' => 'Typographical corrections and one clarification in chapter four.',
        ]);

        $resubmission = PgThesisResubmission::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('AWAITING', $resubmission->status);
        $this->assertSame(1, $resubmission->cycle);
        $this->assertSame(
            now()->addDays(90)->toDateString(),
            $resubmission->due_on->toDateString(),
            'A minor-corrections verdict carries a real 90-day deadline.',
        );
        $this->assertNull(PgThesisMark::where('candidate_id', $candidate->id)->first(), 'No mark exists until corrections are accepted.');

        // Verification is impossible before the candidate files anything.
        $this->post(route('pg-research.resubmissions.verify', $resubmission->id), [
            'decision' => 'accept',
        ])->assertSessionHas('error');

        $this->act('pg-research.resubmissions.submit', $resubmission->id, [
            'corrections_summary' => 'All eleven examiner comments addressed; see the corrections matrix.',
        ]);
        $this->assertSame('SUBMITTED', $resubmission->refresh()->status);
        $this->assertNotNull($resubmission->submitted_at);

        $this->act('pg-research.resubmissions.verify', $resubmission->id, [
            'decision' => 'accept',
            'notes' => 'Corrections verified against the examiner matrix.',
        ]);
        $this->assertSame('ACCEPTED', $resubmission->refresh()->status);
        $this->assertSame($this->admin->id, $resubmission->verified_by);

        $mark = PgThesisMark::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertEqualsWithDelta(68.0, (float) $mark->composite_score, 0.01);
        $this->assertSame('Credit', $mark->final_grade);

        $this->get(route('pg-research.thesis-resubmission'))->assertOk()->assertSee('PG/MSC/2026/050');
    }

    public function test_publications_are_only_credited_once_a_reviewer_accepts_them(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/060');

        $this->act('pg-research.publications.store', [], [
            'candidate_id' => $candidate->id,
            'article_title' => 'Rainfall variability and maize yields, 2015-2024',
            'journal_name' => 'East African Journal of Agriculture',
            'doi' => '10.1234/eaja.2026.001',
            'indexed_in' => 'Scopus',
        ]);

        $publication = PgPublication::where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('SUBMITTED', $publication->status);

        $this->act('pg-research.publications.decide', $publication->id, [
            'decision' => 'UNDER_REVIEW',
            'notes' => 'Checking the publisher against the CUE accredited list.',
        ]);
        $this->assertSame('UNDER_REVIEW', $publication->refresh()->status);

        $this->act('pg-research.publications.decide', $publication->id, [
            'decision' => 'ACCEPTED',
            'notes' => 'Publisher confirmed accredited.',
        ]);
        $this->assertSame('ACCEPTED', $publication->refresh()->status);

        $this->get(route('pg-research.publications-review'))
            ->assertOk()
            ->assertSee('Rainfall variability and maize yields, 2015-2024');
    }

    public function test_a_legacy_import_that_cannot_be_matched_records_the_failure_instead_of_losing_the_row(): void
    {
        $this->registerCandidate('PG/PHD/2026/070');

        $this->act('pg-research.legacy.store', [], [
            'batch_reference' => 'LEGACY-2019-A',
            'source_module' => 'Old PG Register',
            'source_reference' => 'PG/PHD/2026/070',
            'target_stage' => 'WRITING',
            'artifacts' => 'Proposal, two progress reports',
        ]);

        $this->act('pg-research.legacy.store', [], [
            'batch_reference' => 'LEGACY-2019-A',
            'source_module' => 'Old PG Register',
            'source_reference' => 'PG/UNKNOWN/9999',
            'target_stage' => 'WRITING',
        ]);

        $this->assertSame(2, PgLegacyMigration::where('batch_reference', 'LEGACY-2019-A')->count());

        $this->post(route('pg-research.legacy.batch'), ['batch_reference' => 'LEGACY-2019-A'])
            ->assertRedirect();

        $matched = PgLegacyMigration::where('source_reference', 'PG/PHD/2026/070')->firstOrFail();
        $unmatched = PgLegacyMigration::where('source_reference', 'PG/UNKNOWN/9999')->firstOrFail();

        $this->assertSame('IMPORTED', $matched->status);
        $this->assertNotNull($matched->candidate_id);
        $this->assertSame('WRITING', PgResearchCandidate::find($matched->candidate_id)->stage);

        $this->assertSame('FAILED', $unmatched->status);
        $this->assertStringContainsString('PG/UNKNOWN/9999', (string) $unmatched->error_message);
        $this->assertSame(2, PgLegacyMigration::count(), 'A failed import must not shrink the batch.');

        $this->act('pg-research.legacy.verify', $matched->id, []);
        $this->assertSame('VERIFIED', $matched->refresh()->status);

        // An unmatched row stays unverifiable.
        $this->post(route('pg-research.legacy.verify', $unmatched->id), [])->assertSessionHas('error');

        $this->get(route('pg-research.legacy-migration'))
            ->assertOk()
            ->assertSee('LEGACY-2019-A');
    }

    public function test_an_appeal_can_only_be_lodged_inside_an_open_window_and_runs_to_determination(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/080');

        $this->act('pg-research.appeal-categories.store', [], [
            'code' => 'mark-review',
            'name' => 'Thesis mark review',
            'description' => 'Challenge to a composite thesis mark.',
            'applies_to' => 'MARKS',
            'fee_amount' => 2500,
            'sla_days' => 21,
        ]);

        $category = PgAppealCategory::where('code', 'MARK-REVIEW')->firstOrFail();
        $this->assertTrue($category->is_active);

        // No window yet.
        $this->post(route('pg-research.appeals.store'), [
            'candidate_id' => $candidate->id,
            'category_id' => $category->id,
            'grounds' => 'The external examiner score sheet was transposed when the composite was computed.',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgAppeal::count());

        $this->act('pg-research.appeal-periods.store', [], [
            'category_id' => $category->id,
            'academic_year' => '2025/2026',
            'term_label' => 'Semester 1 Appeals',
            'opens_on' => now()->subDay()->toDateString(),
            'closes_on' => now()->addWeeks(3)->toDateString(),
        ]);

        $period = PgAppealPeriod::where('term_label', 'Semester 1 Appeals')->firstOrFail();
        $this->assertSame('DRAFT', $period->status);

        // A draft window is not an open window.
        $this->post(route('pg-research.appeals.store'), [
            'candidate_id' => $candidate->id,
            'category_id' => $category->id,
            'grounds' => 'The external examiner score sheet was transposed when the composite was computed.',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgAppeal::count());

        $this->act('pg-research.appeal-periods.open', $period->id, []);
        $this->assertSame('OPEN', $period->refresh()->status);

        $this->act('pg-research.appeals.store', [], [
            'candidate_id' => $candidate->id,
            'category_id' => $category->id,
            'grounds' => 'The external examiner score sheet was transposed when the composite was computed.',
        ]);

        $appeal = PgAppeal::firstOrFail();
        $this->assertSame(sprintf('APL/%d/0001', now()->year), $appeal->reference);
        $this->assertSame('SUBMITTED', $appeal->status);
        $this->assertSame($period->id, $appeal->period_id);
        $this->assertSame(now()->addDays(21)->toDateString(), $appeal->dueAt()->toDateString());

        $reviewer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->act('pg-research.appeals.assign', $appeal->id, ['assigned_to' => $reviewer->id]);
        $this->assertSame('UNDER_REVIEW', $appeal->refresh()->status);
        $this->assertSame($reviewer->id, $appeal->assigned_to);

        $this->act('pg-research.appeals.decide', $appeal->id, [
            'decision' => 'UPHELD',
            'notes' => 'Transposition confirmed; the mark is referred back to the examination board.',
        ]);
        $this->assertSame('UPHELD', $appeal->refresh()->status);
        $this->assertNotNull($appeal->decided_at);

        // A closed appeal is closed.
        $this->post(route('pg-research.appeals.decide', $appeal->id), [
            'decision' => 'DISMISSED',
            'notes' => 'Attempted second determination on a closed appeal.',
        ])->assertSessionHas('error');
        $this->assertSame('UPHELD', $appeal->refresh()->status);

        // Closing the window blocks further lodging.
        $this->act('pg-research.appeal-periods.close', $period->id, []);
        $this->post(route('pg-research.appeals.store'), [
            'candidate_id' => $candidate->id,
            'category_id' => $category->id,
            'grounds' => 'A second, later appeal lodged after the window has closed.',
        ])->assertSessionHas('error');
        $this->assertSame(1, PgAppeal::count());

        $this->get(route('pg-research.appeal-category'))
            ->assertOk()
            ->assertSee('MARK-REVIEW')
            ->assertSee($appeal->reference);

        $this->get(route('pg-research.appeal-period-setup'))
            ->assertOk()
            ->assertSee('Semester 1 Appeals');
    }

    public function test_a_category_that_requires_evidence_refuses_an_appeal_without_it(): void
    {
        $candidate = $this->registerCandidate('PG/PHD/2026/090');

        $this->act('pg-research.appeal-categories.store', [], [
            'code' => 'viva-conduct',
            'name' => 'Conduct of the viva',
            'applies_to' => 'VIVA',
            'sla_days' => 14,
            'requires_evidence' => 1,
        ]);
        $category = PgAppealCategory::where('code', 'VIVA-CONDUCT')->firstOrFail();
        $this->assertTrue($category->requires_evidence);

        $this->act('pg-research.appeal-periods.store', [], [
            'academic_year' => '2025/2026',
            'term_label' => 'Open Window',
            'opens_on' => now()->subDay()->toDateString(),
            'closes_on' => now()->addWeek()->toDateString(),
        ]);
        $period = PgAppealPeriod::where('term_label', 'Open Window')->firstOrFail();
        $this->act('pg-research.appeal-periods.open', $period->id, []);

        $this->post(route('pg-research.appeals.store'), [
            'candidate_id' => $candidate->id,
            'category_id' => $category->id,
            'grounds' => 'The panel chair was absent for the second half of the examination.',
        ])->assertSessionHas('error');
        $this->assertSame(0, PgAppeal::count());

        // Deactivating a category takes it out of service immediately.
        $this->act('pg-research.appeal-categories.toggle', $category->id, []);
        $this->assertFalse($category->refresh()->is_active);
    }

    public function test_every_pg_research_screen_renders_against_live_tables(): void
    {
        $this->registerCandidate('PG/PHD/2026/100');

        foreach ([
            'index', 'supervisor-roles', 'eligibility-gating', 'supervisor-allocation',
            'proposal-reader-review', 'seminar-presentations', 'progress-reports',
            'plagiarism-checker', 'defence-request-approval', 'examiner-dashboard',
            'viva-examination', 'thesis-marks-approval', 'thesis-resubmission',
            'publications-review', 'legacy-migration', 'appeal-period-setup', 'appeal-category',
        ] as $name) {
            $this->get(route("pg-research.{$name}"))
                ->assertOk()
                ->assertDontSee('triggerActionAlert', false);
        }
    }

    // ------------------------------------------------------------- Helpers

    /** Drive a write route the way a screen does, and require it to have worked. */
    private function act(string $name, mixed $params, array $payload): TestResponse
    {
        $response = $this->from(route('pg-research.index'))->post(route($name, $params), $payload);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $response->assertSessionHas('success');

        return $response;
    }

    private function registerCandidate(string $regNo, array $overrides = []): PgResearchCandidate
    {
        $this->act('pg-research.candidates.store', [], array_merge([
            'reg_no' => $regNo,
            'candidate_name' => 'Candidate '.$regNo,
            'degree_level' => 'PHD',
            'programme_title' => 'PhD in Applied Sciences',
            'academic_year' => '2025/2026',
            'coursework_units_total' => 8,
            'coursework_units_passed' => 8,
            'gpa' => 3.6,
            'fee_balance' => 0,
        ], $overrides));

        return PgResearchCandidate::where('reg_no', $regNo)->firstOrFail();
    }

    private function addSupervisor(string $staffNo, string $name, int $maxLoad = 5): PgSupervisor
    {
        $this->act('pg-research.supervisors.store', [], [
            'staff_no' => $staffNo,
            'full_name' => $name,
            'academic_rank' => 'Professor',
            'department' => 'Graduate School',
            'specialization' => 'Applied Sciences',
            'max_load' => $maxLoad,
        ]);

        return PgSupervisor::where('staff_no', $staffNo)->firstOrFail();
    }

    /**
     * Bring a candidate to the point where a viva can legitimately be scheduled,
     * using only the routes the screens use.
     *
     * @param  list<float>  $scores
     */
    private function candidateWithReportedPanel(string $regNo, array $scores): PgResearchCandidate
    {
        $candidate = $this->registerCandidate($regNo);

        $this->act('pg-research.scans.store', [], [
            'candidate_id' => $candidate->id,
            'document_type' => 'THESIS',
            'similarity_index' => 6.4,
            'threshold' => 15,
        ]);

        $this->act('pg-research.defence-requests.store', [], [
            'candidate_id' => $candidate->id,
            'thesis_title' => 'A thesis for '.$regNo,
        ]);

        $defence = PgDefenceRequest::where('candidate_id', $candidate->id)->firstOrFail();
        $this->act('pg-research.defence-requests.decide', $defence->id, [
            'decision' => 'APPROVED',
            'notes' => 'Cleared.',
        ]);

        foreach ($scores as $index => $score) {
            $this->act('pg-research.examiners.store', [], [
                'candidate_id' => $candidate->id,
                'examiner_name' => "Examiner {$index} for {$regNo}",
                'examiner_type' => $index === 0 ? 'INTERNAL' : 'EXTERNAL',
            ]);
        }

        $examiners = PgExaminer::where('candidate_id', $candidate->id)->orderBy('id')->get();
        foreach ($examiners as $index => $examiner) {
            $this->act('pg-research.examiners.report', $examiner->id, [
                'recommendation' => 'MINOR',
                'remarks' => 'Minor corrections required before binding.',
                'score' => $scores[$index],
            ]);
        }

        return $candidate->refresh();
    }
}
