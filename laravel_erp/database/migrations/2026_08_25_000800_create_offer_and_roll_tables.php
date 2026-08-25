<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offers, generated evidence documents, QR verification, admission rolls and student conversion.
 *
 * Purpose: everything from "we have decided" to "this person is a student", with the evidence a
 * regulator would ask for. Generated documents are immutable and checksummed; a correction is a new
 * version, never an overwrite. QR tokens are opaque random values — the token itself carries no
 * personal data, and the public endpoint discloses only what policy permits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 60);
            $t->string('name', 190);
            // ADMISSION_LETTER | PAYMENT_RECEIPT | SUBMISSION_RECEIPT | ADMISSION_ROLL | EMAIL | SMS
            $t->string('template_type', 40)->index();
            $t->unsignedSmallInteger('version')->default(1);
            $t->date('effective_from');
            $t->date('effective_to')->nullable();
            $t->text('body');
            // Declared placeholders, validated at render time so a typo fails loudly.
            $t->jsonb('placeholders')->default('[]');
            $t->string('signatory_name', 190)->nullable();
            $t->string('signatory_title', 190)->nullable();
            $t->string('signature_image_path', 255)->nullable();
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
            $t->unique(['institution_id', 'code', 'version']);
        });

        Schema::create('generated_documents', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            $t->string('document_type', 40)->index();
            $t->string('subject_type', 120);
            $t->string('subject_id', 64);
            $t->uuid('admission_application_id')->nullable()->index();
            $t->foreignUuid('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $t->unsignedSmallInteger('template_version')->nullable();
            $t->unsignedSmallInteger('version')->default(1);
            $t->string('storage_disk', 40)->default('admissions');
            $t->string('storage_key', 255);
            $t->string('mime_type', 100)->default('application/pdf');
            $t->unsignedBigInteger('size_bytes')->default(0);
            $t->string('checksum', 64);
            $t->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('generated_at')->useCurrent();
            $t->boolean('is_current')->default(true);
            $t->timestampTz('superseded_at')->nullable();
            $t->uuid('supersedes_document_id')->nullable();
            $t->date('retention_until')->nullable();
            $t->boolean('legal_hold')->default(false);
            $t->string('classification', 20)->default('confidential');
            $t->timestampsTz();
            $t->index(['subject_type', 'subject_id', 'version']);
        });

        Schema::table('admission_offers', function (Blueprint $t): void {
            $t->uuid('institution_id')->nullable()->index();
            $t->uuid('decision_id')->nullable()->index();
            // CONDITIONAL | UNCONDITIONAL | WAITLIST
            $t->string('offer_type', 30)->default('UNCONDITIONAL');
            $t->unsignedSmallInteger('version')->default(1);
            $t->uuid('reissue_of_offer_id')->nullable();
            $t->uuid('generated_document_id')->nullable();
            $t->uuid('document_template_id')->nullable();
            $t->date('reporting_date')->nullable();
            $t->date('extended_to')->nullable();
            $t->foreignId('extended_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('fee_instructions')->nullable();
            $t->string('signatory_name', 190)->nullable();
            $t->string('signatory_title', 190)->nullable();
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('revoked_at')->nullable();
            $t->string('revocation_reason', 255)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->timestampTz('created_at')->nullable();
            $t->timestampTz('updated_at')->nullable();
        });

        Schema::create('offer_responses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_offer_id')->constrained('admission_offers')->cascadeOnDelete();
            $t->uuid('admission_application_id')->index();
            // ACCEPTED | DECLINED | DEFERRAL_REQUESTED | APPEAL_SUBMITTED
            $t->string('response', 40)->index();
            $t->timestampTz('responded_at')->useCurrent();
            $t->string('declaration_version', 40)->nullable();
            $t->string('terms_version', 40)->nullable();
            $t->text('comment')->nullable();
            $t->foreignId('admission_intake_id')->nullable()->constrained('admission_intakes')->nullOnDelete();
            // PENDING | APPROVED | REJECTED — used for deferral and appeal outcomes.
            $t->string('decision_status', 20)->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('decided_at')->nullable();
            $t->string('decision_note', 500)->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->string('evidence_hash', 64)->nullable();
            $t->timestampsTz();
        });

        Schema::create('qr_verification_tokens', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            // Only the hash is stored: a database reader cannot mint a working verification URL.
            $t->string('token_hash', 64)->unique();
            // First 8 characters, for support lookups without holding the secret.
            $t->string('token_prefix', 12)->index();
            $t->string('subject_type', 120);
            $t->string('subject_id', 64);
            $t->uuid('generated_document_id')->nullable();
            $t->uuid('admission_application_id')->nullable()->index();
            // ACTIVE | ROTATED | REVOKED | EXPIRED
            $t->string('status', 20)->default('ACTIVE')->index();
            $t->timestampTz('issued_at')->useCurrent();
            $t->timestampTz('expires_at')->nullable();
            $t->timestampTz('revoked_at')->nullable();
            $t->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('revocation_reason', 255)->nullable();
            $t->uuid('rotation_of_token_id')->nullable();
            $t->unsignedInteger('scan_count')->default(0);
            $t->timestampTz('last_scanned_at')->nullable();
            // What the public endpoint is allowed to disclose for this token.
            $t->jsonb('disclosure_policy')->default('{"applicant_name":true,"programme":true,"intake":true}');
            $t->timestampsTz();
        });

        Schema::create('qr_scan_events', function (Blueprint $t): void {
            $t->id();
            $t->uuid('qr_verification_token_id')->nullable()->index();
            $t->string('presented_prefix', 12)->nullable();
            // VALID | INVALID | REVOKED | EXPIRED | RATE_LIMITED
            $t->string('result', 20)->index();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestampTz('occurred_at')->useCurrent()->index();
        });

        Schema::create('admission_rolls', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('reference', 60)->unique();
            $t->string('title', 190);
            $t->foreignId('admission_intake_id')->constrained('admission_intakes')->restrictOnDelete();
            $t->foreignId('programme_offering_id')->nullable()->constrained('programme_offerings')->nullOnDelete();
            // The saved query the draft was generated from — reproducibility, not just provenance.
            $t->jsonb('query_snapshot')->default('{}');
            // DRAFT | PENDING_APPROVAL | APPROVED | FROZEN | SUPERSEDED
            $t->string('status', 20)->default('DRAFT')->index();
            $t->unsignedSmallInteger('version')->default(1);
            $t->unsignedInteger('total_entries')->default(0);
            $t->unsignedInteger('eligible_entries')->default(0);
            $t->string('checksum', 64)->nullable();
            $t->uuid('generated_document_id')->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('approved_at')->nullable();
            $t->foreignId('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('frozen_at')->nullable();
            $t->uuid('supersedes_roll_id')->nullable();
            $t->string('notes', 500)->nullable();
            $t->timestampsTz();
        });

        Schema::create('admission_roll_entries', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_roll_id')->constrained('admission_rolls')->cascadeOnDelete();
            $t->uuid('admission_application_id')->index();
            // Snapshots, deliberately: a frozen roll is legal evidence of what was published that day,
            // so it must not silently change when a master record is later corrected.
            $t->string('applicant_number', 60);
            $t->string('application_number', 60);
            $t->string('applicant_name', 190);
            $t->string('programme_name', 190);
            $t->string('campus_name', 190)->nullable();
            $t->string('study_mode_name', 120)->nullable();
            $t->string('offer_reference', 60)->nullable();
            $t->string('decision_outcome', 30)->nullable();
            $t->boolean('is_eligible')->default(true);
            $t->string('eligibility_note', 500)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->string('entry_checksum', 64)->nullable();
            $t->timestampsTz();
            $t->unique(['admission_roll_id', 'admission_application_id']);
        });

        Schema::create('student_conversions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->unique()->constrained('admission_applications')->restrictOnDelete();
            $t->string('idempotency_key', 190)->unique();
            $t->uuid('person_id')->nullable();
            $t->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $t->string('student_number', 60)->nullable()->unique();
            $t->uuid('programme_admission_id')->nullable();
            $t->uuid('admission_roll_id')->nullable();
            // PENDING | COMPLETED | FAILED
            $t->string('status', 20)->default('PENDING')->index();
            $t->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('converted_at')->nullable();
            $t->string('failure_code', 80)->nullable();
            $t->string('failure_reason', 500)->nullable();
            $t->string('external_reference', 190)->nullable();
            $t->jsonb('payload')->default('{}');
            $t->uuid('correlation_id')->nullable();
            $t->timestampsTz();
        });
    }

    public function down(): void
    {
        foreach ([
            'student_conversions', 'admission_roll_entries', 'admission_rolls', 'qr_scan_events',
            'qr_verification_tokens', 'offer_responses',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('admission_offers', function (Blueprint $t): void {
            $t->dropColumn([
                'institution_id', 'decision_id', 'offer_type', 'version', 'reissue_of_offer_id',
                'generated_document_id', 'document_template_id', 'reporting_date', 'extended_to',
                'extended_by', 'fee_instructions', 'signatory_name', 'signatory_title', 'issued_by',
                'revoked_by', 'revoked_at', 'revocation_reason', 'correlation_id', 'created_at', 'updated_at',
            ]);
        });
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('document_templates');
    }
};
