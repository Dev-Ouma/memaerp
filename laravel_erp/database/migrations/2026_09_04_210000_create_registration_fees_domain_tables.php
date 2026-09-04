<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('title', 255);
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->date('starts_on');
            $table->date('regular_deadline');
            $table->date('late_deadline')->nullable();
            $table->unsignedSmallInteger('min_units')->default(1);
            $table->unsignedSmallInteger('max_units')->default(8);
            $table->boolean('financial_gating')->default(true);
            $table->decimal('late_penalty_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('DRAFT')->index();
            $table->timestamps();
        });

        Schema::create('course_enrolments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_period_id')->constrained('registration_periods')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('status', 20)->default('REGISTERED')->index();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            $table->unique(['registration_period_id', 'student_id', 'subject_id'], 'course_enrolments_period_student_subject_unique');
            $table->index(['student_id', 'status']);
        });

        Schema::create('fee_structures', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('title', 255);
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('cohort', 80)->nullable();
            $table->decimal('tuition_amount', 14, 2)->default(0);
            $table->decimal('admin_amount', 14, 2)->default(0);
            $table->string('currency', 8)->default('KES');
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('fee_invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_no', 40)->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->restrictOnDelete();
            $table->foreignId('registration_period_id')->nullable()->constrained('registration_periods')->nullOnDelete();
            $table->foreignId('course_enrolment_id')->nullable()->constrained('course_enrolments')->nullOnDelete();
            $table->decimal('amount_invoiced', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->string('status', 20)->default('OPEN')->index();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });

        Schema::create('fee_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_ref', 40)->unique();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method', 40)->default('MANUAL');
            $table->string('transaction_ref', 80)->nullable();
            $table->string('status', 20)->default('PENDING')->index();
            $table->string('receipt_no', 40)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('course_enrolments');
        Schema::dropIfExists('registration_periods');
    }
};
