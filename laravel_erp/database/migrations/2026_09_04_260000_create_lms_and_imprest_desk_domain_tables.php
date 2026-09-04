<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_course_shells', function (Blueprint $table): void {
            $table->id();
            $table->string('shell_code', 80)->unique();
            $table->string('course_title', 190);
            $table->string('faculty', 190)->nullable();
            $table->string('instructor', 190)->nullable();
            $table->string('intake_cohort', 80)->nullable();
            $table->string('delivery_mode', 80)->nullable();
            $table->unsignedInteger('enrolled_count')->default(0);
            $table->unsignedInteger('modules_count')->default(0);
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('lms_lecturer_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('assignment_ref', 80)->unique();
            $table->string('instructor_name', 190);
            $table->string('course_shell', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->string('role', 80)->nullable();
            $table->string('access_level', 80)->nullable();
            $table->string('teaching_assistant', 190)->nullable();
            $table->string('office_hours', 120)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('lms_live_lectures', function (Blueprint $table): void {
            $table->id();
            $table->string('session_title', 190);
            $table->string('course_code', 40)->nullable();
            $table->string('instructor', 190)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('scheduled_time', 80)->nullable();
            $table->string('attendance_mode', 80)->nullable();
            $table->string('recording_status', 40)->nullable();
            $table->string('session_status', 40)->default('Scheduled')->index();
            $table->timestamps();
        });

        Schema::create('lms_e_resources', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_title', 190);
            $table->string('course_shell', 190)->nullable();
            $table->string('resource_type', 80)->nullable();
            $table->string('file_size', 40)->nullable();
            $table->string('uploaded_by', 120)->nullable();
            $table->string('upload_date', 40)->nullable();
            $table->string('downloads_views', 80)->nullable();
            $table->string('access_rule', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('lms_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('assignment_title', 190);
            $table->string('course_code', 40)->nullable();
            $table->string('weight', 40)->nullable();
            $table->string('submission_deadline', 40)->nullable();
            $table->unsignedInteger('submissions_count')->default(0);
            $table->string('turnitin_status', 40)->nullable();
            $table->string('grading_status', 40)->default('Open')->index();
            $table->timestamps();
        });

        Schema::create('lms_student_analytics', function (Blueprint $table): void {
            $table->id();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('programme', 190)->nullable();
            $table->string('engagement_score', 40)->nullable();
            $table->unsignedInteger('total_logins_trimester')->default(0);
            $table->string('video_watch_rate', 40)->nullable();
            $table->string('cat_completion_rate', 40)->nullable();
            $table->string('risk_status', 40)->default('On Track')->index();
            $table->timestamps();
        });

        Schema::create('lms_discussion_threads', function (Blueprint $table): void {
            $table->id();
            $table->string('thread_title', 190);
            $table->string('course_code', 40)->nullable();
            $table->string('author', 190)->nullable();
            $table->unsignedInteger('replies_count')->default(0);
            $table->string('last_reply_by', 190)->nullable();
            $table->string('last_activity', 80)->nullable();
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
        });

        Schema::create('lms_online_quizzes', function (Blueprint $table): void {
            $table->id();
            $table->string('quiz_title', 190);
            $table->string('course_code', 40)->nullable();
            $table->string('weight', 40)->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->unsignedInteger('completed_attempts')->default(0);
            $table->string('avg_score', 40)->nullable();
            $table->string('proctoring_mode', 80)->nullable();
            $table->string('status', 40)->default('Draft')->index();
            $table->timestamps();
        });

        Schema::create('lms_gradebook_syncs', function (Blueprint $table): void {
            $table->id();
            $table->string('sync_ref', 80)->unique();
            $table->string('course_code', 40)->nullable();
            $table->string('cohort', 80)->nullable();
            $table->unsignedInteger('enrolled_students')->default(0);
            $table->unsignedInteger('total_cat_synced')->default(0);
            $table->string('erp_exam_engine_sync', 80)->nullable();
            $table->string('sync_timestamp', 80)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('imprest_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role_title', 190);
            $table->string('authority_level', 80)->nullable();
            $table->string('min_limit', 80)->nullable();
            $table->string('max_limit', 80)->nullable();
            $table->string('allowed_categories', 255)->nullable();
            $table->string('mandate_rule', 255)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('imprest_claim_matrices', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_code', 80)->unique();
            $table->string('claim_category', 120)->nullable();
            $table->string('originating_unit', 190)->nullable();
            $table->string('workflow_sequence', 255)->nullable();
            $table->unsignedInteger('auto_escalation_hours')->default(0);
            $table->string('delegate_allowed', 40)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('imprest_surrender_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('policy_code', 80)->unique();
            $table->string('title', 190);
            $table->string('timeline', 120)->nullable();
            $table->string('document_requirements', 255)->nullable();
            $table->string('non_compliance_action', 255)->nullable();
            $table->string('waiver_authority', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('imprest_requisitions', function (Blueprint $table): void {
            $table->id();
            $table->string('requisition_no', 80)->unique();
            $table->string('applicant_name', 190);
            $table->string('department', 190)->nullable();
            $table->string('vote_head', 120)->nullable();
            $table->string('amount_requested', 80)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->string('disbursement_mode', 80)->nullable();
            $table->string('surrender_due_date', 40)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('imprest_surrenders', function (Blueprint $table): void {
            $table->id();
            $table->string('surrender_no', 80)->unique();
            $table->string('requisition_ref', 80)->nullable();
            $table->string('staff_name', 190);
            $table->string('department', 190)->nullable();
            $table->string('imprest_amount', 80)->nullable();
            $table->string('actual_expenditure', 80)->nullable();
            $table->string('unspent_refund', 80)->nullable();
            $table->string('supplementary_claim', 80)->nullable();
            $table->string('etims_compliance', 80)->nullable();
            $table->string('audit_verdict', 80)->nullable();
            $table->string('surrender_status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('imprest_audit_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->string('imprest_ref', 80)->unique();
            $table->string('staff_name', 190);
            $table->string('staff_no', 40)->nullable();
            $table->string('department', 190)->nullable();
            $table->string('amount_due', 80)->nullable();
            $table->string('issue_date', 40)->nullable();
            $table->string('due_date', 40)->nullable();
            $table->unsignedInteger('days_overdue')->default(0);
            $table->string('risk_category', 80)->nullable();
            $table->string('recovery_status', 40)->default('Open')->index();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('imprest_audit_ledgers');
        Schema::dropIfExists('imprest_surrenders');
        Schema::dropIfExists('imprest_requisitions');
        Schema::dropIfExists('imprest_surrender_rules');
        Schema::dropIfExists('imprest_claim_matrices');
        Schema::dropIfExists('imprest_permissions');
        Schema::dropIfExists('lms_gradebook_syncs');
        Schema::dropIfExists('lms_online_quizzes');
        Schema::dropIfExists('lms_discussion_threads');
        Schema::dropIfExists('lms_student_analytics');
        Schema::dropIfExists('lms_assignments');
        Schema::dropIfExists('lms_e_resources');
        Schema::dropIfExists('lms_live_lectures');
        Schema::dropIfExists('lms_lecturer_assignments');
        Schema::dropIfExists('lms_course_shells');
    }
};
