<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_study_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('trimester', 80);
            $table->string('academic_year', 20);
            $table->string('application_start', 40)->nullable();
            $table->string('application_deadline', 40)->nullable();
            $table->string('total_budget', 80)->nullable();
            $table->string('committed_budget', 80)->nullable();
            $table->string('hourly_rate', 40)->nullable();
            $table->string('max_weekly_hours', 40)->nullable();
            $table->string('target_beneficiaries', 40)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('work_study_positions', function (Blueprint $table): void {
            $table->id();
            $table->string('job_code', 80)->unique();
            $table->string('title', 190);
            $table->string('department', 190)->nullable();
            $table->string('supervisor', 190)->nullable();
            $table->string('hours_per_week', 40)->nullable();
            $table->string('skills_required', 255)->nullable();
            $table->unsignedInteger('slots_available')->default(0);
            $table->unsignedInteger('slots_filled')->default(0);
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
        });

        Schema::create('work_study_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('app_no', 80)->unique();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('programme', 190)->nullable();
            $table->string('preferred_role', 190)->nullable();
            $table->string('current_gpa', 40)->nullable();
            $table->string('need_category', 80)->nullable();
            $table->string('fee_arrears', 80)->nullable();
            $table->string('socio_economic_score', 40)->nullable();
            $table->string('vetting_status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('work_study_allocations', function (Blueprint $table): void {
            $table->id();
            $table->string('allocation_code', 80)->unique();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('assigned_position', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->string('supervisor', 190)->nullable();
            $table->string('approved_weekly_hours', 40)->nullable();
            $table->string('start_date', 40)->nullable();
            $table->string('end_date', 40)->nullable();
            $table->string('contract_status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('work_study_timesheets', function (Blueprint $table): void {
            $table->id();
            $table->string('timesheet_no', 80)->unique();
            $table->string('student_name', 190);
            $table->string('department', 190)->nullable();
            $table->string('month_cycle', 40)->nullable();
            $table->string('hours_claimed', 40)->nullable();
            $table->string('hourly_rate', 40)->nullable();
            $table->string('total_amount', 80)->nullable();
            $table->string('supervisor_rating', 40)->nullable();
            $table->string('supervisor_status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('work_study_claims', function (Blueprint $table): void {
            $table->id();
            $table->string('voucher_no', 80)->unique();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('timesheet_ref', 80)->nullable();
            $table->string('gross_amount', 80)->nullable();
            $table->string('fee_account_credit', 80)->nullable();
            $table->string('cash_stipend', 80)->nullable();
            $table->string('disbursement_mode', 80)->nullable();
            $table->string('audit_approval', 80)->nullable();
            $table->string('disbursement_status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('sp_taxes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 190);
            $table->string('type', 80)->nullable();
            $table->string('rate', 40)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('sp_items', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 190);
            $table->string('category', 120)->nullable();
            $table->string('unit_cost', 80)->nullable();
            $table->string('stock', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('sp_provider_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 190);
            $table->string('desc', 255)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('sp_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('provider_code', 80)->unique();
            $table->string('name', 190);
            $table->string('group', 120)->nullable();
            $table->string('contact', 190)->nullable();
            $table->string('outstanding_bills', 80)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('sp_vendor_approvals', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 80)->unique();
            $table->string('name', 190);
            $table->string('kra_pin', 40)->nullable();
            $table->string('compliance_doc', 190)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('sp_invoice_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_name', 190);
            $table->string('department', 190)->nullable();
            $table->string('limit_amount', 80)->nullable();
            $table->string('policy_level', 120)->nullable();
            $table->string('last_audited', 80)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('sp_bills', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 80)->unique();
            $table->string('vendor', 190);
            $table->string('amount', 80)->nullable();
            $table->string('due_date', 40)->nullable();
            $table->string('status', 40)->default('Unpaid')->index();
            $table->timestamps();
        });

        Schema::create('sp_payment_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('staff_name', 190);
            $table->string('department', 190)->nullable();
            $table->string('limit_amount', 80)->nullable();
            $table->string('compliance', 120)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('sp_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 80)->unique();
            $table->string('vendor', 190);
            $table->string('amount', 80)->nullable();
            $table->string('date', 40)->nullable();
            $table->string('mode', 80)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('sp_debit_notes', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 80)->unique();
            $table->string('vendor', 190);
            $table->string('amount', 80)->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
        });

        Schema::create('sp_credit_notes', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 80)->unique();
            $table->string('vendor', 190);
            $table->string('amount', 80)->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sp_credit_notes');
        Schema::dropIfExists('sp_debit_notes');
        Schema::dropIfExists('sp_payments');
        Schema::dropIfExists('sp_payment_permissions');
        Schema::dropIfExists('sp_bills');
        Schema::dropIfExists('sp_invoice_permissions');
        Schema::dropIfExists('sp_vendor_approvals');
        Schema::dropIfExists('sp_providers');
        Schema::dropIfExists('sp_provider_groups');
        Schema::dropIfExists('sp_items');
        Schema::dropIfExists('sp_taxes');
        Schema::dropIfExists('work_study_claims');
        Schema::dropIfExists('work_study_timesheets');
        Schema::dropIfExists('work_study_allocations');
        Schema::dropIfExists('work_study_applications');
        Schema::dropIfExists('work_study_positions');
        Schema::dropIfExists('work_study_periods');
    }
};
