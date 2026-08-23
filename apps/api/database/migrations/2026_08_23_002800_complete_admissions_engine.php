<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-05: Student Recruitment, Applications & Admissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission.prospects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('source', 64)->default('WEBSITE');
            $table->string('campaign_code', 64)->nullable();
            $table->foreignUuid('programme_interest_id')->nullable()->constrained('curriculum.programmes')->nullOnDelete();
            $table->string('status', 32)->default('NEW');
            $table->text('notes')->nullable();
            $table->timestampTz('converted_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'email']);
        });

        Schema::create('admission.programme_cutoffs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->decimal('minimum_score', 5, 2);
            $table->string('minimum_mean_grade', 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['programme_id', 'academic_year_id'], 'programme_cutoff_unique');
        });

        Schema::table('admission.applications', function (Blueprint $table): void {
            $table->foreignUuid('intake_id')->nullable()->after('academic_year_id')->constrained('institution.intakes')->nullOnDelete();
            $table->foreignUuid('study_mode_id')->nullable()->after('intake_id')->constrained('institution.study_modes')->nullOnDelete();
            $table->foreignUuid('prospect_id')->nullable()->after('person_id')->constrained('admission.prospects')->nullOnDelete();
            $table->string('kcse_index_number', 32)->nullable()->after('mean_grade');
            $table->string('entry_path', 32)->default('DIRECT')->after('kcse_index_number');
            $table->text('decision_notes')->nullable()->after('offer_accepted_at');
            $table->string('offer_qr_token', 64)->nullable()->unique()->after('offer_letter_ref');
            $table->string('offer_letter_hash', 128)->nullable()->after('offer_qr_token');
            $table->timestampTz('offer_expires_at')->nullable()->after('offer_issued_at');
            $table->timestampTz('submitted_at')->nullable()->after('status');
            $table->timestampTz('documents_verified_at')->nullable();
            $table->foreignUuid('documents_verified_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->decimal('application_fee_amount', 12, 2)->default(1500);
            $table->string('application_fee_currency', 3)->default('KES');
        });

        DB::statement("UPDATE admission.applications SET status = 'DRAFT' WHERE status = 'SUBMITTED' AND is_fee_paid = false");
        DB::statement("ALTER TABLE admission.applications ALTER COLUMN status SET DEFAULT 'DRAFT'");
        DB::statement('ALTER TABLE admission.applications DROP CONSTRAINT IF EXISTS applications_status_valid');
        DB::statement("ALTER TABLE admission.applications ADD CONSTRAINT applications_status_valid CHECK (status IN ('DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'SHORTLISTED', 'ADMITTED', 'REJECTED', 'ACCEPTED', 'EXPIRED', 'MATRICULATED'))");
        DB::statement("ALTER TABLE admission.applications ADD CONSTRAINT applications_entry_path_valid CHECK (entry_path IN ('DIRECT', 'KUCCPS', 'TRANSFER', 'SPECIAL'))");

        Schema::create('admission.application_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('admission.applications')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('original_name');
            $table->string('disk_path');
            $table->string('mime_type', 128);
            $table->unsignedInteger('byte_size');
            $table->string('verification_status', 32)->default('PENDING');
            $table->text('verification_notes')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();

            $table->index(['application_id', 'document_type']);
        });

        Schema::create('admission.application_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('admission.applications')->cascadeOnDelete();
            $table->string('channel', 32);
            $table->string('transaction_reference', 100)->unique();
            $table->string('payer_phone', 32)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status', 32)->default('PENDING');
            $table->string('receipt_number', 50)->nullable()->unique();
            $table->timestampTz('paid_at')->nullable();
            $table->jsonb('gateway_payload')->nullable();
            $table->timestampsTz();

            $table->index(['application_id', 'status']);
        });

        Schema::create('admission.application_reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('admission.applications')->cascadeOnDelete();
            $table->string('stage', 64);
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 32)->default('PENDING');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('reference', 128)->nullable();
            $table->text('comments')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['application_id', 'stage'], 'application_review_stage_unique');
        });

        Schema::create('admission.kuccps_placements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('intake_id')->nullable()->constrained('institution.intakes')->nullOnDelete();
            $table->string('kuccps_index', 32);
            $table->string('applicant_name');
            $table->string('programme_code', 32);
            $table->foreignUuid('programme_id')->nullable()->constrained('curriculum.programmes')->nullOnDelete();
            $table->string('mean_grade', 8)->nullable();
            $table->decimal('aggregate_points', 5, 2)->nullable();
            $table->string('import_batch', 64);
            $table->foreignUuid('application_id')->nullable()->constrained('admission.applications')->nullOnDelete();
            $table->string('status', 32)->default('IMPORTED');
            $table->timestampsTz();

            $table->unique(['institution_id', 'kuccps_index', 'import_batch'], 'kuccps_batch_unique');
            $table->index(['institution_id', 'status']);
        });

        DB::statement('SELECT public.enforce_governance_columns()');
    }

    public function down(): void
    {
        Schema::dropIfExists('admission.kuccps_placements');
        Schema::dropIfExists('admission.application_reviews');
        Schema::dropIfExists('admission.application_payments');
        Schema::dropIfExists('admission.application_documents');

        Schema::table('admission.applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('documents_verified_by');
            $table->dropConstrainedForeignId('prospect_id');
            $table->dropConstrainedForeignId('study_mode_id');
            $table->dropConstrainedForeignId('intake_id');
            $table->dropColumn([
                'kcse_index_number', 'entry_path', 'decision_notes', 'offer_qr_token', 'offer_letter_hash',
                'offer_expires_at', 'submitted_at', 'documents_verified_at', 'application_fee_amount',
                'application_fee_currency',
            ]);
        });

        DB::statement('ALTER TABLE admission.applications DROP CONSTRAINT IF EXISTS applications_status_valid');
        DB::statement('ALTER TABLE admission.applications DROP CONSTRAINT IF EXISTS applications_entry_path_valid');
        DB::statement("ALTER TABLE admission.applications ALTER COLUMN status SET DEFAULT 'SUBMITTED'");

        Schema::dropIfExists('admission.programme_cutoffs');
        Schema::dropIfExists('admission.prospects');
    }
};
