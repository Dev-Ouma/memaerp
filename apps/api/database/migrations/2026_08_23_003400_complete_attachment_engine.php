<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-02-04: Industrial attachment placements, logbooks and assessments.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS attachment');

        if (! Schema::hasTable('attachment.host_organizations')) {
            Schema::create('attachment.host_organizations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->string('name');
                $table->string('industry', 80)->nullable();
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone', 40)->nullable();
                $table->text('address')->nullable();
                $table->unsignedSmallInteger('capacity_per_intake')->default(5);
                $table->date('mou_valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('quality_rating', 3, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestampsTz();

                $table->index(['institution_id', 'is_active']);
                $table->index('name');
            });
        }

        if (! Schema::hasTable('attachment.attachment_applications')) {
            Schema::create('attachment.attachment_applications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->foreignUuid('term_id')->nullable()->constrained('institution.terms')->nullOnDelete();
                $table->json('preferred_organization_ids')->nullable();
                $table->text('motivation')->nullable();
                $table->string('status', 30)->default('DRAFT');
                $table->timestampTz('submitted_at')->nullable();
                $table->foreignUuid('reviewed_by')->nullable()->constrained('iam.users')->nullOnDelete();
                $table->timestampTz('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestampsTz();

                $table->index(['institution_id', 'status']);
                $table->index(['student_id', 'status']);
            });
        }

        if (! Schema::hasTable('attachment.attachment_placements')) {
            Schema::create('attachment.attachment_placements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('application_id')->constrained('attachment.attachment_applications')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->foreignUuid('host_organization_id')->constrained('attachment.host_organizations')->restrictOnDelete();
                $table->foreignUuid('university_supervisor_id')->nullable()->constrained('iam.users')->nullOnDelete();
                $table->string('field_supervisor_name')->nullable();
                $table->string('field_supervisor_email')->nullable();
                $table->string('field_supervisor_phone', 40)->nullable();
                $table->date('starts_on');
                $table->date('ends_on');
                $table->string('status', 30)->default('PENDING_HOST');
                $table->timestampTz('host_accepted_at')->nullable();
                $table->timestampTz('activated_at')->nullable();
                $table->timestampTz('completed_at')->nullable();
                $table->decimal('final_grade', 5, 2)->nullable();
                $table->string('grade_letter', 5)->nullable();
                $table->timestampsTz();

                $table->index(['institution_id', 'status']);
                $table->index(['student_id', 'status']);
                $table->index('university_supervisor_id');
            });
        }

        if (! Schema::hasTable('attachment.logbook_entries')) {
            Schema::create('attachment.logbook_entries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('placement_id')->constrained('attachment.attachment_placements')->cascadeOnDelete();
                $table->unsignedSmallInteger('week_number');
                $table->date('week_start');
                $table->text('activities_summary');
                $table->text('skills_learned')->nullable();
                $table->decimal('hours_logged', 5, 2)->default(0);
                $table->string('status', 20)->default('DRAFT');
                $table->timestampTz('submitted_at')->nullable();
                $table->timestampTz('endorsed_at')->nullable();
                $table->text('host_comment')->nullable();
                $table->timestampsTz();

                $table->unique(['placement_id', 'week_number']);
                $table->index(['placement_id', 'status']);
            });
        }

        if (! Schema::hasTable('attachment.attachment_assessments')) {
            Schema::create('attachment.attachment_assessments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('placement_id')->constrained('attachment.attachment_placements')->cascadeOnDelete();
                $table->string('assessment_type', 30);
                $table->decimal('score', 5, 2)->nullable();
                $table->decimal('max_score', 5, 2)->default(100);
                $table->text('comments')->nullable();
                $table->foreignUuid('assessed_by')->nullable()->constrained('iam.users')->nullOnDelete();
                $table->string('assessor_name')->nullable();
                $table->timestampTz('assessed_at')->nullable();
                $table->string('status', 20)->default('DRAFT');
                $table->timestampsTz();

                $table->unique(['placement_id', 'assessment_type']);
                $table->index(['placement_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attachment.attachment_assessments');
        Schema::dropIfExists('attachment.logbook_entries');
        Schema::dropIfExists('attachment.attachment_placements');
        Schema::dropIfExists('attachment.attachment_applications');
        Schema::dropIfExists('attachment.host_organizations');
    }
};
