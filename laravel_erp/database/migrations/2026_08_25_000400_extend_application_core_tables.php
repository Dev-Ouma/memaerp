<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Application core: one continuous application, section-level drafts, and immutable submitted versions.
 *
 * Purpose: `admission_applications` becomes the lifecycle aggregate — it carries its own status, a
 * *separate* payment status, an optimistic-locking version, the workflow instance it belongs to and the
 * correlation identifier that ties every downstream event and audit record back to it.
 *
 * Section drafts are separate rows so autosave writes touch one section and cannot clobber another.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $t): void {
            $t->uuid('institution_id')->nullable()->index();
            // Payment truth is a related state machine, never folded into `status`.
            $t->string('payment_status', 20)->nullable()->index();
            $t->uuid('fee_setup_id')->nullable();
            $t->unsignedInteger('fee_amount_expected')->nullable();
            $t->string('fee_currency', 3)->nullable();
            $t->unsignedInteger('current_version')->default(0);
            $t->uuid('submitted_version_id')->nullable();
            $t->uuid('workflow_instance_id')->nullable()->index();
            $t->uuid('correlation_id')->nullable()->index();
            $t->string('submission_idempotency_key', 190)->nullable();
            $t->string('submission_receipt_number', 60)->nullable()->unique();
            // web | mobile | agent | walk_in | import | api
            $t->string('source_channel', 30)->default('web');
            // A second live application for the same offering requires an explicit authorisation.
            $t->boolean('duplicate_authorised')->default(false);
            $t->foreignId('duplicate_authorised_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('duplicate_authorisation_reason', 255)->nullable();
            $t->string('declaration_version', 40)->nullable();
            $t->string('terms_version', 40)->nullable();
            $t->string('privacy_version', 40)->nullable();
            $t->decimal('eligibility_score', 6, 2)->nullable();
            // ELIGIBLE | INELIGIBLE | REVIEW_REQUIRED
            $t->string('eligibility_result', 20)->nullable();
            $t->decimal('review_score', 6, 2)->nullable();
            $t->uuid('assigned_department_id')->nullable()->index();
            $t->timestampTz('sla_due_at')->nullable()->index();
            $t->timestampTz('info_requested_at')->nullable();
            $t->timestampTz('info_due_at')->nullable();
            $t->timestampTz('last_activity_at')->nullable();
            $t->timestampTz('withdrawn_at')->nullable();
            $t->timestampTz('closed_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->softDeletesTz();
        });

        DB::table('admission_applications')->whereNull('payment_status')->update(['payment_status' => 'NOT_STARTED']);
        DB::statement('update admission_applications set institution_id = (select id from institutions limit 1) where institution_id is null');

        Schema::create('application_section_drafts', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            // personal | contact | education | employment | references | documents | support | declarations
            $t->string('section_key', 40);
            $t->jsonb('payload')->default('{}');
            $t->unsignedSmallInteger('completion_percent')->default(0);
            $t->boolean('is_complete')->default(false);
            $t->jsonb('validation_errors')->default('[]');
            $t->unsignedInteger('lock_version')->default(1);
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
            $t->unique(['admission_application_id', 'section_key']);
        });

        Schema::create('education_history', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->string('institution_name', 190);
            $t->uuid('qualification_level_id')->nullable();
            $t->string('qualification_name', 190);
            $t->string('index_number', 60)->nullable();
            $t->date('started_on')->nullable();
            $t->date('ended_on')->nullable();
            $t->string('mean_grade', 10)->nullable();
            $t->decimal('mean_points', 5, 2)->nullable();
            // [{"subject":"Mathematics","grade":"B+"}, …]
            $t->jsonb('subject_grades')->default('[]');
            $t->string('country_code', 2)->default('KE');
            $t->uuid('evidence_document_id')->nullable();
            $t->boolean('is_highest')->default(false);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestampsTz();
            $t->index(['admission_application_id', 'sort_order']);
        });

        Schema::create('employment_history', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->string('employer_name', 190);
            $t->string('position', 190);
            $t->date('started_on')->nullable();
            $t->date('ended_on')->nullable();
            $t->boolean('is_current')->default(false);
            $t->text('responsibilities')->nullable();
            $t->string('reference_name', 190)->nullable();
            $t->string('reference_contact', 190)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestampsTz();
            $t->index(['admission_application_id', 'sort_order']);
        });

        Schema::create('referee_requests', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->string('referee_name', 190);
            $t->string('referee_email', 190);
            $t->string('referee_phone', 20)->nullable();
            $t->string('referee_organisation', 190)->nullable();
            $t->string('relationship', 120)->nullable();
            // Opaque single-use token; only the hash is stored.
            $t->string('token_hash', 64)->unique();
            // PENDING | SENT | RESPONDED | DECLINED | EXPIRED
            $t->string('status', 20)->default('PENDING');
            $t->timestampTz('requested_at')->nullable();
            $t->timestampTz('reminded_at')->nullable();
            $t->timestampTz('expires_at')->nullable();
            $t->timestampsTz();
            $t->index(['admission_application_id', 'status']);
        });

        Schema::create('referee_responses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('referee_request_id')->unique()->constrained('referee_requests')->cascadeOnDelete();
            $t->unsignedSmallInteger('overall_rating')->nullable();
            $t->jsonb('answers')->default('{}');
            $t->text('comments')->nullable();
            $t->boolean('recommends')->nullable();
            $t->string('submitted_ip', 45)->nullable();
            $t->timestampTz('submitted_at')->useCurrent();
            $t->string('evidence_hash', 64)->nullable();
        });

        Schema::table('application_versions', function (Blueprint $t): void {
            // SUBMISSION | CORRECTION | REISSUE
            $t->string('version_type', 20)->default('SUBMISSION');
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reason_code', 80)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->boolean('is_current')->default(true);
            $t->timestampTz('superseded_at')->nullable();
        });

        Schema::table('application_status_history', function (Blueprint $t): void {
            $t->string('actor_role', 60)->nullable();
            $t->uuid('workflow_step_id')->nullable();
            $t->uuid('correlation_id')->nullable()->index();
            $t->string('source_channel', 30)->default('api');
            $t->string('evidence_hash', 64)->nullable();
            $t->jsonb('metadata')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('application_status_history', function (Blueprint $t): void {
            $t->dropColumn(['actor_role', 'workflow_step_id', 'correlation_id', 'source_channel', 'evidence_hash', 'metadata']);
        });
        Schema::table('application_versions', function (Blueprint $t): void {
            $t->dropColumn(['version_type', 'created_by', 'reason_code', 'correlation_id', 'is_current', 'superseded_at']);
        });
        foreach ([
            'referee_responses', 'referee_requests', 'employment_history',
            'education_history', 'application_section_drafts',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('admission_applications', function (Blueprint $t): void {
            $t->dropColumn([
                'institution_id', 'payment_status', 'fee_setup_id', 'fee_amount_expected', 'fee_currency',
                'current_version', 'submitted_version_id', 'workflow_instance_id', 'correlation_id',
                'submission_idempotency_key', 'submission_receipt_number', 'source_channel',
                'duplicate_authorised', 'duplicate_authorised_by', 'duplicate_authorisation_reason',
                'declaration_version', 'terms_version', 'privacy_version', 'eligibility_score',
                'eligibility_result', 'review_score', 'assigned_department_id', 'sla_due_at',
                'info_requested_at', 'info_due_at', 'last_activity_at', 'withdrawn_at', 'closed_at',
                'created_by', 'updated_by', 'deleted_at',
            ]);
        });
    }
};
