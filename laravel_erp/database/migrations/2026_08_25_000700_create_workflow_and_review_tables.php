<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuration-driven review, scoring and approval.
 *
 * Purpose: who reviews what, in which order, against which rubric, and who is allowed to turn a
 * recommendation into an institutional decision. The workflow is data, not code, so an intake can change
 * its approval chain without a deployment.
 *
 * `workflow_definitions.steps` is the template; `workflow_steps` are the materialised runtime steps of a
 * single `workflow_instances` row, which is what SLA timers and escalation act on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->unsignedSmallInteger('version')->default(1);
            $t->foreignId('admission_intake_id')->nullable()->constrained('admission_intakes')->nullOnDelete();
            $t->foreignId('programme_offering_id')->nullable()->constrained('programme_offerings')->nullOnDelete();
            // [{"key":"ops_verification","name":"…","role":"admissions_officer","sla_hours":48,"required":true}, …]
            $t->jsonb('steps');
            $t->boolean('is_active')->default(true);
            $t->timestampTz('published_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
            $t->unique(['institution_id', 'code', 'version']);
        });

        Schema::create('workflow_instances', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('workflow_definition_id')->constrained('workflow_definitions')->restrictOnDelete();
            $t->string('current_step_key', 60)->nullable()->index();
            // RUNNING | PAUSED | COMPLETED | CANCELLED
            $t->string('status', 20)->default('RUNNING')->index();
            $t->timestampTz('started_at')->useCurrent();
            $t->timestampTz('completed_at')->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->timestampsTz();
            $t->index(['admission_application_id', 'status']);
        });

        Schema::create('workflow_steps', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $t->string('step_key', 60);
            $t->string('name', 190);
            $t->unsignedSmallInteger('sequence');
            $t->string('required_role', 60)->nullable();
            // PENDING | ACTIVE | PAUSED | COMPLETED | SKIPPED | ESCALATED
            $t->string('status', 20)->default('PENDING')->index();
            $t->string('outcome', 40)->nullable();
            $t->timestampTz('activated_at')->nullable();
            $t->timestampTz('due_at')->nullable()->index();
            $t->timestampTz('completed_at')->nullable();
            $t->timestampTz('paused_at')->nullable();
            $t->unsignedInteger('paused_seconds')->default(0);
            $t->timestampTz('escalated_at')->nullable();
            $t->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->jsonb('metadata')->default('{}');
            $t->timestampsTz();
            $t->unique(['workflow_instance_id', 'step_key']);
        });

        Schema::create('review_assignments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('workflow_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $t->foreignId('assignee_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('stage', 40)->index();
            $t->string('role_code', 60)->nullable();
            // PENDING | IN_PROGRESS | COMPLETED | REASSIGNED | RECUSED | DELEGATED | CANCELLED
            $t->string('status', 20)->default('PENDING')->index();
            $t->unsignedSmallInteger('priority')->default(5);
            $t->timestampTz('due_at')->nullable()->index();
            $t->timestampTz('started_at')->nullable();
            $t->timestampTz('completed_at')->nullable();
            $t->foreignId('delegated_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('reassigned_from')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('conflict_declared')->default(false);
            $t->string('conflict_note', 500)->nullable();
            $t->string('recusal_reason', 255)->nullable();
            $t->timestampTz('escalated_at')->nullable();
            $t->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $t->uuid('correlation_id')->nullable();
            $t->timestampsTz();
            $t->index(['assignee_id', 'status']);
        });

        Schema::create('review_checklists', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->string('stage', 40);
            // [{"key":"identity_matches","label":"…","required":true}, …]
            $t->jsonb('items');
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->unique(['institution_id', 'code']);
        });

        Schema::create('scoring_rubrics', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->unsignedSmallInteger('version')->default(1);
            $t->string('stage', 40)->default('department_review');
            $t->foreignId('programme_offering_id')->nullable()->constrained('programme_offerings')->nullOnDelete();
            $t->decimal('pass_score', 6, 2)->default(50);
            $t->decimal('max_score', 6, 2)->default(100);
            $t->date('effective_from')->nullable();
            $t->date('effective_to')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->unique(['institution_id', 'code', 'version']);
        });

        Schema::create('scoring_criteria', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('scoring_rubric_id')->constrained('scoring_rubrics')->cascadeOnDelete();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->string('description', 500)->nullable();
            $t->decimal('weight', 6, 3)->default(1);
            $t->decimal('min_score', 6, 2)->default(0);
            $t->decimal('max_score', 6, 2)->default(10);
            // A knockout criterion scoring below its minimum fails the whole application.
            $t->boolean('is_knockout')->default(false);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestampsTz();
            $t->unique(['scoring_rubric_id', 'code']);
        });

        Schema::table('application_reviews', function (Blueprint $t): void {
            $t->uuid('review_assignment_id')->nullable()->index();
            $t->uuid('workflow_step_id')->nullable()->index();
            $t->uuid('scoring_rubric_id')->nullable()->index();
            $t->jsonb('checklist_responses')->default('{}');
            $t->decimal('total_score', 8, 2)->nullable();
            $t->decimal('weighted_score', 8, 2)->nullable();
            $t->decimal('moderated_score', 8, 2)->nullable();
            $t->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('moderated_at')->nullable();
            // DRAFT | SUBMITTED
            $t->string('status', 20)->default('SUBMITTED');
            $t->boolean('conflict_declared')->default(false);
            $t->uuid('correlation_id')->nullable();
            $t->timestampTz('updated_at')->nullable();
        });

        Schema::create('review_scores', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('application_review_id')->constrained('application_reviews')->cascadeOnDelete();
            $t->foreignUuid('scoring_criteria_id')->constrained('scoring_criteria')->restrictOnDelete();
            $t->decimal('raw_score', 6, 2);
            $t->decimal('weighted_score', 8, 2);
            $t->string('comment', 500)->nullable();
            $t->timestampsTz();
            $t->unique(['application_review_id', 'scoring_criteria_id']);
        });

        Schema::create('decision_batches', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('reference', 60)->unique();
            $t->string('decision_type', 40);
            $t->string('outcome', 40);
            $t->jsonb('filter_snapshot')->default('{}');
            $t->jsonb('preview')->default('[]');
            $t->unsignedInteger('item_count')->default(0);
            // DRAFT | PREVIEWED | APPROVED | APPLIED | CANCELLED
            $t->string('status', 20)->default('DRAFT')->index();
            $t->string('rationale', 500)->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('approved_at')->nullable();
            $t->timestampTz('applied_at')->nullable();
            $t->string('checksum', 64)->nullable();
            $t->timestampsTz();
        });

        Schema::create('decisions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('decision_batch_id')->nullable()->constrained('decision_batches')->nullOnDelete();
            // SHORTLIST | RECOMMENDATION | DEPARTMENT_APPROVAL | FINAL
            $t->string('decision_type', 40)->index();
            // ADMIT | ADMIT_CONDITIONAL | WAITLIST | REJECT | DEFER | REVOKE
            $t->string('outcome', 30)->index();
            $t->string('reason_code', 80)->nullable();
            $t->text('rationale')->nullable();
            $t->boolean('is_final')->default(false);
            $t->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            $t->string('decided_by_role', 60)->nullable();
            $t->timestampTz('decided_at')->useCurrent();
            // Maker-checker: a final decision needs a second, differently-permissioned actor.
            $t->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('checked_at')->nullable();
            $t->uuid('superseded_by_decision_id')->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->string('evidence_hash', 64)->nullable();
            $t->timestampsTz();
            $t->index(['admission_application_id', 'decision_type']);
        });

        Schema::create('decision_conditions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('decision_id')->constrained('decisions')->cascadeOnDelete();
            $t->string('code', 60);
            $t->string('description', 500);
            $t->date('due_date')->nullable();
            $t->boolean('is_mandatory')->default(true);
            // PENDING | MET | WAIVED | FAILED
            $t->string('status', 20)->default('PENDING')->index();
            $t->uuid('evidence_document_id')->nullable();
            $t->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('cleared_at')->nullable();
            $t->string('clearance_note', 500)->nullable();
            $t->timestampsTz();
        });

        Schema::create('approval_steps', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('decision_id')->nullable()->constrained('decisions')->nullOnDelete();
            $t->unsignedSmallInteger('step_order');
            $t->string('role_code', 60);
            $t->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            // PENDING | APPROVED | REJECTED | SKIPPED
            $t->string('status', 20)->default('PENDING')->index();
            $t->timestampTz('acted_at')->nullable();
            $t->string('comment', 500)->nullable();
            $t->foreignId('delegated_from')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
            $t->unique(['admission_application_id', 'step_order']);
        });
    }

    public function down(): void
    {
        foreach ([
            'approval_steps', 'decision_conditions', 'decisions', 'decision_batches', 'review_scores',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('application_reviews', function (Blueprint $t): void {
            $t->dropColumn([
                'review_assignment_id', 'workflow_step_id', 'scoring_rubric_id', 'checklist_responses',
                'total_score', 'weighted_score', 'moderated_score', 'moderated_by', 'moderated_at',
                'status', 'conflict_declared', 'correlation_id', 'updated_at',
            ]);
        });
        foreach ([
            'scoring_criteria', 'scoring_rubrics', 'review_checklists', 'review_assignments',
            'workflow_steps', 'workflow_instances', 'workflow_definitions',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
