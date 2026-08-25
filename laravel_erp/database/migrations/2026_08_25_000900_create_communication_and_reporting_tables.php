<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications, report definitions and governed export jobs.
 *
 * Purpose: every applicant-facing message is templated and recorded so support can answer "what did we
 * send them?", and every export is a permissioned background job with a checksum, a classification and
 * an expiring download — never an unbounded synchronous dump.
 *
 * `communications` stores a body hash and a private storage reference rather than the rendered body,
 * because rendered bodies contain personal data and this table is read widely by support staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 80);
            $t->string('name', 190);
            // EMAIL | SMS | IN_APP
            $t->string('channel', 20)->index();
            $t->string('locale', 10)->default('en');
            $t->unsignedSmallInteger('version')->default(1);
            $t->string('subject', 255)->nullable();
            $t->text('body');
            $t->jsonb('placeholders')->default('[]');
            // SMS must never carry rejection detail or identity numbers; the flag is enforced at render.
            $t->boolean('allows_sensitive_detail')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->unique(['institution_id', 'code', 'channel', 'locale', 'version'], 'notification_template_unique');
        });

        Schema::create('communications', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            $t->uuid('admission_application_id')->nullable()->index();
            $t->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('recipient_masked', 190);
            $t->string('channel', 20)->index();
            $t->string('template_code', 80)->index();
            $t->unsignedSmallInteger('template_version')->nullable();
            $t->string('subject', 255)->nullable();
            $t->string('body_hash', 64)->nullable();
            $t->string('body_ref', 255)->nullable();
            // QUEUED | SENT | DELIVERED | FAILED | BOUNCED | SUPPRESSED
            $t->string('status', 20)->default('QUEUED')->index();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->timestampTz('queued_at')->useCurrent();
            $t->timestampTz('sent_at')->nullable();
            $t->timestampTz('delivered_at')->nullable();
            $t->timestampTz('failed_at')->nullable();
            $t->string('failure_reason', 500)->nullable();
            $t->string('provider_message_id', 190)->nullable();
            $t->uuid('correlation_id')->nullable()->index();
            $t->timestampsTz();
        });

        Schema::create('report_definitions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 80)->unique();
            $t->string('name', 190);
            $t->string('description', 500);
            $t->string('category', 60)->index();
            // Deny-by-default: the report cannot be run without this permission.
            $t->string('permission_code', 120);
            // application | person | payment | document | assignment
            $t->string('grain', 40);
            $t->string('date_basis', 60)->nullable();
            $t->jsonb('default_columns')->default('[]');
            $t->jsonb('available_columns')->default('[]');
            // Excluded unless the caller holds the sensitive-data permission and states a purpose.
            $t->jsonb('sensitive_columns')->default('[]');
            $t->jsonb('available_filters')->default('[]');
            $t->unsignedInteger('max_rows')->default(50000);
            // Aggregate rows below this count are suppressed in grouped output.
            $t->unsignedSmallInteger('small_cell_threshold')->default(5);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        Schema::create('export_jobs', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            $t->string('report_code', 80)->index();
            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->string('purpose', 500);
            $t->jsonb('filters')->default('{}');
            $t->jsonb('columns')->default('[]');
            // CSV | XLSX | PDF | JSON
            $t->string('format', 10);
            $t->unsignedInteger('row_limit')->default(50000);
            // QUEUED | RUNNING | COMPLETED | FAILED | EXPIRED
            $t->string('status', 20)->default('QUEUED')->index();
            $t->unsignedInteger('row_count')->default(0);
            $t->string('storage_disk', 40)->default('admissions');
            $t->string('storage_key', 255)->nullable();
            $t->string('checksum', 64)->nullable();
            $t->unsignedBigInteger('size_bytes')->default(0);
            $t->string('classification', 20)->default('confidential');
            $t->boolean('includes_sensitive_columns')->default(false);
            $t->timestampTz('requested_at')->useCurrent();
            $t->timestampTz('started_at')->nullable();
            $t->timestampTz('completed_at')->nullable();
            $t->timestampTz('expires_at')->nullable();
            $t->unsignedSmallInteger('download_count')->default(0);
            $t->timestampTz('last_downloaded_at')->nullable();
            $t->string('error_code', 80)->nullable();
            $t->string('error_detail', 500)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->timestampsTz();
        });
    }

    public function down(): void
    {
        foreach (['export_jobs', 'report_definitions', 'communications', 'notification_templates'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
