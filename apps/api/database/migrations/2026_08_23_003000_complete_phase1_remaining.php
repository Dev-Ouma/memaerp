<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-08 through MOD-01-13: Phase 1 remaining engine tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination.term_gpas', function (Blueprint $table): void {
            $table->string('progression_decision', 32)->default('IN_PROGRESS')->after('academic_standing');
            $table->boolean('is_published')->default(false)->after('progression_decision');
            $table->timestampTz('calculated_at')->nullable()->after('is_published');
            $table->timestampTz('published_at')->nullable()->after('calculated_at');
        });

        Schema::create('examination.exam_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('institution.terms')->cascadeOnDelete();
            $table->string('qr_token', 64)->unique();
            $table->string('status', 32)->default('ACTIVE');
            $table->timestampTz('issued_at');
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->unique(['student_id', 'term_id']);
        });

        Schema::create('graduation.applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->restrictOnDelete();
            $table->foreignUuid('curriculum_version_id')->constrained('curriculum.curriculum_versions')->restrictOnDelete();
            $table->string('status', 32)->default('PENDING');
            $table->decimal('audit_credits_required', 6, 2);
            $table->decimal('audit_credits_earned', 6, 2)->default(0);
            $table->boolean('audit_passed')->default(false);
            $table->timestampTz('applied_at');
            $table->timestampTz('approved_at')->nullable();
            $table->timestampsTz();

            $table->index(['institution_id', 'status']);
        });

        Schema::create('graduation.clearance_checkpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('graduation_application_id')->constrained('graduation.applications')->cascadeOnDelete();
            $table->string('department_code', 64);
            $table->string('department_name');
            $table->string('status', 32)->default('PENDING');
            $table->foreignUuid('cleared_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->timestampTz('cleared_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['graduation_application_id', 'department_code'], 'graduation_checkpoint_unique');
        });

        Schema::create('graduation.certificates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->restrictOnDelete();
            $table->foreignUuid('graduation_application_id')->constrained('graduation.applications')->restrictOnDelete();
            $table->string('certificate_number', 64)->unique();
            $table->string('verification_token', 64)->unique();
            $table->timestampTz('issued_at');
            $table->string('status', 32)->default('ACTIVE');
            $table->timestampsTz();
        });

        Schema::create('student.portal_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();
            $table->string('locale', 16)->default('en-KE');
            $table->string('theme', 16)->default('light');
            $table->boolean('email_alerts')->default(true);
            $table->boolean('sms_alerts')->default(true);
            $table->jsonb('dashboard_widgets')->nullable();
            $table->timestampsTz();

            $table->unique('person_id');
        });

        DB::statement('SELECT public.enforce_governance_columns()');
    }

    public function down(): void
    {
        Schema::dropIfExists('student.portal_preferences');
        Schema::dropIfExists('graduation.certificates');
        Schema::dropIfExists('graduation.clearance_checkpoints');
        Schema::dropIfExists('graduation.applications');
        Schema::dropIfExists('examination.exam_cards');

        Schema::table('examination.term_gpas', function (Blueprint $table): void {
            $table->dropColumn(['progression_decision', 'is_published', 'calculated_at', 'published_at']);
        });
    }
};
