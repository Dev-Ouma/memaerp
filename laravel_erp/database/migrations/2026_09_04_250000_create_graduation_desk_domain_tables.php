<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduation_criteria', function (Blueprint $table): void {
            $table->id();
            $table->string('programme', 190);
            $table->string('min_credits', 40)->nullable();
            $table->string('min_cgpa', 40)->nullable();
            $table->string('thesis_required', 40)->nullable();
            $table->string('clearance_nodes', 255)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('graduation_clearance_nodes', function (Blueprint $table): void {
            $table->id();
            $table->string('node_name', 190);
            $table->string('check_type', 80)->nullable();
            $table->string('assigned_role', 120)->nullable();
            $table->string('requires_approval', 120)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('graduation_finance_clearances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('programme', 190)->nullable();
            $table->string('ledger_balance', 80)->nullable();
            $table->string('last_payment_date', 40)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('graduation_grade_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('cgpa', 40)->nullable();
            $table->string('classification', 80)->nullable();
            $table->string('grades_distribution', 255)->nullable();
            $table->string('status', 40)->default('Eligible')->index();
            $table->timestamps();
        });

        Schema::create('graduation_list_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('generation_run', 80)->unique();
            $table->string('school', 190)->nullable();
            $table->string('cohort', 80)->nullable();
            $table->string('run_date', 40)->nullable();
            $table->unsignedInteger('total_qualified')->default(0);
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('graduation_list_validations', function (Blueprint $table): void {
            $table->id();
            $table->string('validation_code', 80)->unique();
            $table->string('school', 190)->nullable();
            $table->unsignedInteger('total_candidates')->default(0);
            $table->string('dean_signoff', 120)->nullable();
            $table->string('registrar_audit', 120)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('graduation_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('publication_code', 80)->unique();
            $table->string('list_title', 190);
            $table->string('date_published', 40)->nullable();
            $table->string('published_by', 120)->nullable();
            $table->unsignedInteger('total_graduands')->default(0);
            $table->string('status', 40)->default('Draft')->index();
            $table->timestamps();
        });

        Schema::create('graduation_list_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_ref', 80)->unique();
            $table->string('school', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->unsignedInteger('total_candidates')->default(0);
            $table->string('file_format', 40)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('graduation_summaries', function (Blueprint $table): void {
            $table->id();
            $table->string('school', 190);
            $table->unsignedInteger('degree_count')->default(0);
            $table->unsignedInteger('diploma_count')->default(0);
            $table->unsignedInteger('masters_count')->default(0);
            $table->unsignedInteger('phd_count')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('graduation_certificate_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('template_code', 80)->unique();
            $table->string('name', 190);
            $table->string('dimensions', 80)->nullable();
            $table->string('security_features', 255)->nullable();
            $table->string('signatories', 255)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('graduation_alumni', function (Blueprint $table): void {
            $table->id();
            $table->string('alumni_code', 80)->unique();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('programme', 190)->nullable();
            $table->string('contact', 190)->nullable();
            $table->string('grad_year', 20)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('graduation_ceremonies', function (Blueprint $table): void {
            $table->id();
            $table->string('congregation_number', 80)->unique();
            $table->string('date', 40)->nullable();
            $table->string('chief_guest', 190)->nullable();
            $table->string('gown_return_deadline', 40)->nullable();
            $table->string('gown_fine_rate', 80)->nullable();
            $table->string('status', 40)->default('Upcoming')->index();
            $table->timestamps();
        });

        Schema::create('graduation_ceremony_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_ref', 80)->unique();
            $table->string('title', 190);
            $table->string('audit_date', 40)->nullable();
            $table->string('compiled_by', 120)->nullable();
            $table->string('senate_submission', 120)->nullable();
            $table->string('status', 40)->default('Draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduation_ceremony_reports');
        Schema::dropIfExists('graduation_ceremonies');
        Schema::dropIfExists('graduation_alumni');
        Schema::dropIfExists('graduation_certificate_templates');
        Schema::dropIfExists('graduation_summaries');
        Schema::dropIfExists('graduation_list_reports');
        Schema::dropIfExists('graduation_publications');
        Schema::dropIfExists('graduation_list_validations');
        Schema::dropIfExists('graduation_list_batches');
        Schema::dropIfExists('graduation_grade_entries');
        Schema::dropIfExists('graduation_finance_clearances');
        Schema::dropIfExists('graduation_clearance_nodes');
        Schema::dropIfExists('graduation_criteria');
    }
};
