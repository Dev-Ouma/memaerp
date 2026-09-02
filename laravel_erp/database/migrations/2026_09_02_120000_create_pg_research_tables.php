<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pg_supervisors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('staff_no', 40)->unique();
            $table->string('full_name');
            $table->string('academic_rank', 60);
            $table->string('department')->nullable();
            $table->string('specialization')->nullable();
            $table->unsignedSmallInteger('max_load')->default(5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pg_research_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained('academic_programmes')->nullOnDelete();
            $table->string('reg_no', 60)->unique();
            $table->string('candidate_name');
            $table->string('degree_level', 20)->index();
            $table->string('programme_title');
            $table->string('academic_year', 20)->nullable();
            $table->unsignedSmallInteger('coursework_units_total')->default(0);
            $table->unsignedSmallInteger('coursework_units_passed')->default(0);
            $table->decimal('gpa', 3, 2)->nullable();
            $table->decimal('fee_balance', 12, 2)->default(0);
            $table->string('registration_status', 40)->default('ACTIVE');
            $table->string('eligibility_status', 30)->default('PENDING')->index();
            $table->string('stage', 30)->default('REGISTERED')->index();
            $table->string('thesis_title')->nullable();
            $table->date('commenced_on')->nullable();
            $table->date('expected_completion')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_eligibility_waivers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('waiver_type', 60)->default('R19_PROVISIONAL');
            $table->text('reason');
            $table->string('status', 30)->default('PENDING')->index();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_supervisor_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('pg_supervisors')->restrictOnDelete();
            $table->string('role', 20)->default('LEAD');
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->date('assigned_on');
            $table->date('ended_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'supervisor_id', 'role']);
        });

        Schema::create('pg_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->foreignId('reader_id')->nullable()->constrained('pg_supervisors')->nullOnDelete();
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status', 30)->default('DRAFT')->index();
            $table->string('manuscript_path')->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_proposal_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_id')->constrained('pg_proposals')->cascadeOnDelete();
            $table->foreignId('reader_id')->nullable()->constrained('pg_supervisors')->nullOnDelete();
            $table->string('verdict', 30);
            $table->text('comments');
            $table->decimal('score', 5, 2)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at');
            $table->timestamps();
        });

        Schema::create('pg_seminars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('seminar_type', 30)->default('PROPOSAL');
            $table->timestampTz('scheduled_for');
            $table->string('venue');
            $table->string('panel_chair')->nullable();
            $table->string('status', 30)->default('SCHEDULED')->index();
            $table->text('outcome_notes')->nullable();
            $table->timestampTz('held_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_progress_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('period_label', 60);
            $table->string('report_stage', 60);
            $table->text('milestone_summary');
            $table->text('supervisor_comment')->nullable();
            $table->string('status', 30)->default('SUBMITTED')->index();
            $table->timestampTz('submitted_at');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'period_label']);
        });

        Schema::create('pg_plagiarism_scans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('document_type', 30)->default('THESIS');
            $table->decimal('similarity_index', 5, 2);
            $table->decimal('threshold', 5, 2)->default(15);
            $table->string('status', 30)->default('PENDING')->index();
            $table->string('report_reference')->nullable();
            $table->timestampTz('scanned_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_defence_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->foreignId('plagiarism_scan_id')->nullable()->constrained('pg_plagiarism_scans')->nullOnDelete();
            $table->string('thesis_title');
            $table->string('status', 30)->default('PENDING')->index();
            $table->timestampTz('requested_at');
            $table->text('decision_notes')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_examiners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('examiner_name');
            $table->string('examiner_type', 20)->default('EXTERNAL');
            $table->string('institution')->nullable();
            $table->string('email')->nullable();
            $table->date('appointed_on');
            $table->string('status', 30)->default('NOMINATED')->index();
            $table->timestamps();
        });

        Schema::create('pg_examiner_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('examiner_id')->constrained('pg_examiners')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('recommendation', 30);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('remarks');
            $table->timestampTz('submitted_at');
            $table->timestamps();
            $table->unique('examiner_id');
        });

        Schema::create('pg_viva_examinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->timestampTz('scheduled_for');
            $table->string('venue');
            $table->string('chair_name')->nullable();
            $table->string('status', 30)->default('SCHEDULED')->index();
            $table->string('verdict', 30)->nullable();
            $table->text('verdict_notes')->nullable();
            $table->foreignId('verdict_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verdict_recorded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_thesis_marks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->decimal('composite_score', 5, 2);
            $table->string('final_grade', 20);
            $table->string('status', 30)->default('SUBMITTED')->index();
            $table->foreignId('ratified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('ratified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('candidate_id');
        });

        Schema::create('pg_thesis_resubmissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->unsignedSmallInteger('cycle')->default(1);
            $table->date('due_on');
            $table->timestampTz('submitted_at')->nullable();
            $table->string('status', 30)->default('AWAITING')->index();
            $table->text('corrections_summary')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'cycle']);
        });

        Schema::create('pg_publications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('article_title');
            $table->string('journal_name');
            $table->string('doi')->nullable();
            $table->string('indexed_in')->nullable();
            $table->string('status', 30)->default('SUBMITTED')->index();
            $table->text('review_notes')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_legacy_migrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->nullable()->constrained('pg_research_candidates')->nullOnDelete();
            $table->string('batch_reference', 60)->index();
            $table->string('source_module', 60);
            $table->string('source_reference', 100);
            $table->string('target_stage', 30);
            $table->text('artifacts')->nullable();
            $table->string('status', 30)->default('PENDING')->index();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('imported_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['batch_reference', 'source_reference']);
        });

        Schema::create('pg_appeal_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('applies_to', 30)->default('MARKS');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->unsignedSmallInteger('sla_days')->default(14);
            $table->boolean('requires_evidence')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pg_appeal_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('pg_appeal_categories')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->string('term_label', 40);
            $table->date('opens_on');
            $table->date('closes_on');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['academic_year', 'term_label', 'category_id']);
        });

        Schema::create('pg_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('pg_appeal_categories')->restrictOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('pg_appeal_periods')->nullOnDelete();
            $table->string('reference', 60)->unique();
            $table->text('grounds');
            $table->string('evidence_path')->nullable();
            $table->string('status', 30)->default('SUBMITTED')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_notes')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->timestampTz('submitted_at');
            $table->timestamps();
        });

        Schema::create('pg_research_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->nullable()->constrained('pg_research_candidates')->cascadeOnDelete();
            $table->string('subject_type', 80)->index();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE pg_research_candidates ADD CONSTRAINT pg_candidates_degree_level_check CHECK (degree_level IN ('MASTERS','PHD'))");
            DB::statement("ALTER TABLE pg_research_candidates ADD CONSTRAINT pg_candidates_eligibility_check CHECK (eligibility_status IN ('PENDING','ELIGIBLE','PROVISIONAL','BLOCKED'))");
            DB::statement('ALTER TABLE pg_research_candidates ADD CONSTRAINT pg_candidates_units_check CHECK (coursework_units_passed <= coursework_units_total)');
            DB::statement("ALTER TABLE pg_supervisor_allocations ADD CONSTRAINT pg_alloc_role_check CHECK (role IN ('LEAD','CO','EXTERNAL'))");
            DB::statement('ALTER TABLE pg_plagiarism_scans ADD CONSTRAINT pg_scan_similarity_check CHECK (similarity_index >= 0 AND similarity_index <= 100)');
            DB::statement('ALTER TABLE pg_thesis_marks ADD CONSTRAINT pg_marks_score_check CHECK (composite_score >= 0 AND composite_score <= 100)');
            DB::statement('ALTER TABLE pg_appeal_periods ADD CONSTRAINT pg_appeal_period_window_check CHECK (closes_on >= opens_on)');
        }
    }

    public function down(): void
    {
        foreach ([
            'pg_research_events', 'pg_appeals', 'pg_appeal_periods', 'pg_appeal_categories',
            'pg_legacy_migrations', 'pg_publications', 'pg_thesis_resubmissions', 'pg_thesis_marks',
            'pg_viva_examinations', 'pg_examiner_reports', 'pg_examiners', 'pg_defence_requests',
            'pg_plagiarism_scans', 'pg_progress_reports', 'pg_seminars', 'pg_proposal_reviews',
            'pg_proposals', 'pg_supervisor_allocations', 'pg_eligibility_waivers',
            'pg_research_candidates', 'pg_supervisors',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
