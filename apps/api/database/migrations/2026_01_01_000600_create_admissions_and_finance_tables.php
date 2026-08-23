<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-05: Student Recruitment, Applications & Admissions.
 * MOD-01-09: Student Finance, Fee Structures, Billing & Payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Admission Applications
        Schema::create('admission.applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('campus_id')->constrained('institution.campuses')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('application_number', 50)->unique();
            $table->string('status', 32)->default('SUBMITTED'); // DRAFT | SUBMITTED | SHORTLISTED | ADMITTED | REJECTED | ACCEPTED | MATRICULATED
            $table->boolean('is_fee_paid')->default(false);
            $table->decimal('qualification_score', 5, 2)->nullable();
            $table->string('secondary_school_name')->nullable();
            $table->string('mean_grade', 8)->nullable();
            $table->string('offer_letter_ref', 100)->nullable();
            $table->timestampTz('offer_issued_at')->nullable();
            $table->timestampTz('offer_accepted_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['institution_id', 'status']);
        });

        // 2. Finance Fee Structures
        Schema::create('finance.fee_structures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('year_level')->default(1);
            $table->unsignedSmallInteger('semester')->default(1);
            $table->decimal('tuition_fee', 12, 2)->default(0.00);
            $table->decimal('statutory_fees', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('currency', 3)->default('KES');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['programme_id', 'academic_year_id', 'year_level', 'semester'], 'fee_struct_unique');
        });

        // 3. Student Fee Invoices
        Schema::create('finance.invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();
            $table->foreignUuid('fee_structure_id')->nullable()->constrained('finance.fee_structures')->nullOnDelete();
            $table->foreignUuid('term_id')->constrained('institution.terms')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2);
            $table->string('status', 32)->default('PENDING'); // PENDING | PARTIALLY_PAID | FULLY_PAID | CANCELLED
            $table->date('due_date');
            $table->timestampsTz();

            $table->index(['institution_id', 'status']);
        });

        // 4. Fee Payments & Receipts
        Schema::create('finance.payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('finance.invoices')->nullOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->string('payment_method', 32); // MPESA | BANK_TRANSFER | CHEQUE | CARD
            $table->string('transaction_reference', 100)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status', 32)->default('COMPLETED'); // COMPLETED | REVERSED | FAILED
            $table->timestampTz('paid_at');
            $table->timestampsTz();

            $table->index(['institution_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance.payments');
        Schema::dropIfExists('finance.invoices');
        Schema::dropIfExists('finance.fee_structures');
        Schema::dropIfExists('admission.applications');
    }
};
