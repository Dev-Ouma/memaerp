<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smhr_workloads', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_id', 40)->nullable();
            $table->string('name', 190);
            $table->string('dept', 190)->nullable();
            $table->string('units', 255)->nullable();
            $table->unsignedInteger('teaching_hours')->default(0);
            $table->unsignedInteger('supervision_hours')->default(0);
            $table->unsignedInteger('admin_hours')->default(0);
            $table->unsignedInteger('total_hours')->default(0);
            $table->string('status', 40)->default('OPTIMAL')->index();
            $table->timestamps();
        });

        Schema::create('smhr_appraisals', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_id', 40)->nullable();
            $table->string('name', 190);
            $table->string('dept', 190)->nullable();
            $table->string('teaching_eval', 40)->nullable();
            $table->string('research_publications', 80)->nullable();
            $table->string('community_service', 80)->nullable();
            $table->string('overall_score', 40)->nullable();
            $table->string('grade', 40)->nullable();
            $table->string('completed', 40)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('smhr_payroll_items', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_id', 40)->nullable();
            $table->string('name', 190);
            $table->string('dept', 190)->nullable();
            $table->string('bank', 120)->nullable();
            $table->string('month', 40)->nullable();
            $table->decimal('basic_pay', 14, 2)->default(0);
            $table->decimal('allowances', 14, 2)->default(0);
            $table->decimal('gross', 14, 2)->default(0);
            $table->decimal('paye', 14, 2)->default(0);
            $table->decimal('statutory', 14, 2)->default(0);
            $table->decimal('net_pay', 14, 2)->default(0);
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('smhr_payroll_variance_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('month', 40);
            $table->unsignedInteger('staff_count')->default(0);
            $table->string('gross', 80)->nullable();
            $table->string('paye', 80)->nullable();
            $table->string('variance', 40)->nullable();
            $table->string('reason', 255)->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status', 40)->default('Compliant')->index();
            $table->timestamps();
        });

        Schema::create('smhr_statutory_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('obligation', 190);
            $table->string('authority', 190)->nullable();
            $table->string('frequency', 80)->nullable();
            $table->string('amount', 80)->nullable();
            $table->string('ref', 80)->nullable();
            $table->string('status', 40)->default('Compliant')->index();
            $table->timestamps();
        });

        Schema::create('smhr_disciplinary_records', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_id', 40)->nullable();
            $table->string('staff_name', 190);
            $table->string('dept', 190)->nullable();
            $table->string('category', 80)->nullable();
            $table->string('type', 80)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('action_taken', 255)->nullable();
            $table->string('date', 40)->nullable();
            $table->string('resolved', 40)->nullable();
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
        });

        Schema::create('smhr_onboarding_candidates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->string('email', 190)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('designation', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->string('joining_date', 40)->nullable();
            $table->string('stage', 80)->nullable();
            $table->string('progress', 40)->nullable();
            $table->string('checklist', 255)->nullable();
            $table->string('status', 40)->default('In Progress')->index();
            $table->timestamps();
        });

        Schema::create('institution_cohorts', function (Blueprint $table): void {
            $table->id();
            $table->string('cohort_code', 80)->unique();
            $table->string('cohort_name', 190);
            $table->string('academic_year', 40)->nullable();
            $table->string('intake_session', 120)->nullable();
            $table->string('study_mode', 120)->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedInteger('enrolled')->default(0);
            $table->string('graduation_expected', 40)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('institution_cohorts');
        Schema::dropIfExists('smhr_onboarding_candidates');
        Schema::dropIfExists('smhr_disciplinary_records');
        Schema::dropIfExists('smhr_statutory_schedules');
        Schema::dropIfExists('smhr_payroll_variance_reports');
        Schema::dropIfExists('smhr_payroll_items');
        Schema::dropIfExists('smhr_appraisals');
        Schema::dropIfExists('smhr_workloads');
    }
};
