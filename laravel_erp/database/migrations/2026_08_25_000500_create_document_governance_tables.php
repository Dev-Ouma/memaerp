<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Document governance: what must be supplied, what was supplied, who verified it and who read it.
 *
 * Purpose: `application_documents` holds metadata only — bytes live on a private disk. The row records
 * the content hash, the malware-scan outcome and whether the object has become immutable evidence
 * because the application was submitted. A row may not be treated as usable evidence until the scan is
 * CLEAN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            // Null offering = institution-wide requirement.
            $t->foreignId('programme_offering_id')->nullable()->constrained('programme_offerings')->cascadeOnDelete();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->string('description', 500)->nullable();
            $t->boolean('is_mandatory')->default(true);
            // ALL | KENYAN | INTERNATIONAL | SUPPORT_NEEDS | EMPLOYED
            $t->string('applies_to', 30)->default('ALL');
            $t->jsonb('accepted_mime_types')->default('["application/pdf","image/jpeg","image/png"]');
            $t->unsignedBigInteger('max_size_bytes')->default(5242880);
            $t->unsignedSmallInteger('min_count')->default(1);
            $t->unsignedSmallInteger('max_count')->default(1);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->unique(['programme_offering_id', 'code']);
        });

        Schema::table('application_documents', function (Blueprint $t): void {
            $t->uuid('document_requirement_id')->nullable()->index();
            $t->uuid('institution_id')->nullable()->index();
            $t->string('storage_disk', 40)->default('admissions');
            // public | internal | confidential | restricted
            $t->string('classification', 20)->default('confidential');
            // PENDING | SCANNING | CLEAN | INFECTED | ERROR
            $t->string('scan_status', 20)->default('PENDING')->index();
            $t->string('scan_engine', 60)->nullable();
            $t->string('scan_result', 190)->nullable();
            $t->timestampTz('scanned_at')->nullable();
            $t->timestampTz('quarantined_at')->nullable();
            // Set at submission: the object becomes immutable evidence and can only be superseded.
            $t->boolean('is_immutable')->default(false);
            $t->uuid('replaced_by_document_id')->nullable();
            $t->unsignedSmallInteger('version')->default(1);
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('retention_tag', 60)->nullable();
            $t->date('retention_until')->nullable();
            $t->boolean('legal_hold')->default(false);
            $t->uuid('correlation_id')->nullable();
            $t->softDeletesTz();
        });

        Schema::create('document_verifications', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('application_document_id')->constrained('application_documents')->cascadeOnDelete();
            $t->foreignId('verifier_id')->constrained('users')->restrictOnDelete();
            // VERIFIED | REJECTED | RESUBMISSION_REQUESTED
            $t->string('outcome', 30);
            $t->string('reason_code', 80)->nullable();
            $t->text('notes')->nullable();
            $t->string('evidence_hash', 64)->nullable();
            $t->timestampTz('verified_at')->useCurrent();
            $t->index(['application_document_id', 'verified_at']);
        });

        Schema::create('document_access_logs', function (Blueprint $t): void {
            $t->id();
            $t->uuid('application_document_id')->index();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // PREVIEW | DOWNLOAD | URL_ISSUED
            $t->string('action', 20);
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->timestampTz('occurred_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');
        Schema::dropIfExists('document_verifications');
        Schema::table('application_documents', function (Blueprint $t): void {
            $t->dropColumn([
                'document_requirement_id', 'institution_id', 'storage_disk', 'classification',
                'scan_status', 'scan_engine', 'scan_result', 'scanned_at', 'quarantined_at',
                'is_immutable', 'replaced_by_document_id', 'version', 'uploaded_by', 'retention_tag',
                'retention_until', 'legal_hold', 'correlation_id', 'deleted_at',
            ]);
        });
        Schema::dropIfExists('document_requirements');
    }
};
