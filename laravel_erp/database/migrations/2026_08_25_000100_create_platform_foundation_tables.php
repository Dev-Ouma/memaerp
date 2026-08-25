<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform foundation for the Admission Module.
 *
 * Purpose: cross-cutting capability that no single domain owns — the permission catalogue and scoped
 * role grants, API bearer tokens, atomic human-readable number sequences, idempotency replay storage,
 * the transactional outbox, append-only audit evidence, consent, legal holds and retention rules.
 *
 * Additive only: the `users` table gains nullable/defaulted columns and keeps every existing column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t): void {
            $t->uuid('public_id')->nullable()->unique()->after('id');
            $t->string('middle_name')->nullable()->after('first_name');
            $t->timestampTz('password_changed_at')->nullable();
            $t->unsignedSmallInteger('failed_login_count')->default(0);
            $t->timestampTz('locked_until')->nullable();
            $t->timestampTz('last_login_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
            $t->text('mfa_secret')->nullable();
            $t->timestampTz('mfa_enabled_at')->nullable();
            $t->text('mfa_recovery_codes')->nullable();
        });

        Schema::create('permissions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            // {module}.{resource}.{action}[.{qualifier}] — e.g. application.submit.own
            $t->string('code', 120)->unique();
            $t->string('module', 40)->index();
            $t->string('resource', 60);
            $t->string('action', 40);
            $t->string('description', 255);
            // public | internal | confidential | restricted — drives field filtering and export defaults
            $t->string('classification', 20)->default('internal');
            // Permissions that must never be granted implicitly to platform administrators.
            $t->boolean('is_segregated')->default(false);
            $t->timestampsTz();
        });

        Schema::create('roles', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 60)->unique();
            $t->string('name', 120);
            $t->string('description', 255)->nullable();
            // Default scope an assignment of this role is expected to carry.
            $t->string('default_scope_type', 30)->default('institution');
            $t->boolean('is_system')->default(false);
            $t->timestampsTz();
        });

        Schema::create('role_permissions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $t->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $t->timestampTz('created_at')->useCurrent();
            $t->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignUuid('role_id')->constrained('roles')->restrictOnDelete();
            // institution | campus | faculty | department | programme | intake | self
            $t->string('scope_type', 30)->default('institution');
            // Deliberately a string: scoped entities do not share one key type across the ERP.
            $t->string('scope_id', 64)->nullable();
            $t->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('granted_at')->useCurrent();
            $t->timestampTz('expires_at')->nullable();
            $t->string('grant_reason', 255)->nullable();
            $t->timestampsTz();
            $t->index(['user_id', 'role_id']);
        });

        Schema::create('api_tokens', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('name', 120);
            // SHA-256 of the presented token; the plaintext is returned once and never stored.
            $t->string('token_hash', 64)->unique();
            $t->jsonb('abilities')->default('["*"]');
            $t->timestampTz('last_used_at')->nullable();
            $t->timestampTz('expires_at')->nullable();
            $t->timestampTz('revoked_at')->nullable();
            $t->string('created_ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestampsTz();
            $t->index(['user_id', 'revoked_at']);
        });

        Schema::create('number_sequences', function (Blueprint $t): void {
            $t->id();
            // e.g. applicant_number:2026 · application_number:SEP2026 · receipt_number:2026
            $t->string('scope_key', 120)->unique();
            $t->string('pattern', 120);
            $t->unsignedBigInteger('next_value')->default(1);
            $t->unsignedSmallInteger('pad_length')->default(6);
            $t->timestampsTz();
        });

        Schema::create('idempotency_keys', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('idempotency_key', 190);
            $t->string('route', 190);
            $t->string('principal', 120)->default('anonymous');
            $t->string('request_hash', 64);
            $t->unsignedSmallInteger('response_status')->nullable();
            $t->jsonb('response_body')->nullable();
            $t->timestampTz('locked_at')->nullable();
            $t->timestampTz('completed_at')->nullable();
            $t->timestampTz('expires_at');
            $t->timestampsTz();
            $t->unique(['idempotency_key', 'route', 'principal']);
            $t->index('expires_at');
        });

        Schema::create('outbox_events', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('event_name', 120)->index();
            $t->string('aggregate_type', 80);
            $t->string('aggregate_id', 64);
            $t->jsonb('payload');
            $t->uuid('correlation_id')->nullable()->index();
            $t->timestampTz('occurred_at')->useCurrent();
            $t->timestampTz('available_at')->useCurrent();
            $t->timestampTz('published_at')->nullable();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->text('last_error')->nullable();
            $t->index(['aggregate_type', 'aggregate_id']);
        });

        Schema::create('audit_events', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->timestampTz('occurred_at')->useCurrent()->index();
            $t->unsignedBigInteger('sequence_no');
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('actor_role', 60)->nullable();
            $t->string('action', 120)->index();
            $t->string('subject_type', 120)->nullable();
            $t->string('subject_id', 64)->nullable();
            $t->uuid('institution_id')->nullable();
            $t->uuid('correlation_id')->nullable()->index();
            $t->string('source_channel', 40)->default('api');
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->jsonb('before')->nullable();
            $t->jsonb('after')->nullable();
            $t->string('classification', 20)->default('internal');
            // Tamper-evidence: sha256(previous_hash || canonical payload).
            $t->string('previous_hash', 64)->nullable();
            $t->string('evidence_hash', 64);
            $t->index(['subject_type', 'subject_id']);
        });

        Schema::create('consent_records', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('subject_type', 120)->nullable();
            $t->string('subject_id', 64)->nullable();
            // terms | privacy | cookie | marketing | data_processing
            $t->string('policy_type', 40);
            $t->string('policy_version', 40);
            $t->boolean('accepted');
            $t->timestampTz('recorded_at')->useCurrent();
            $t->string('source_channel', 40)->default('api');
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->index(['subject_type', 'subject_id', 'policy_type']);
        });

        Schema::create('legal_holds', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('subject_type', 120);
            $t->string('subject_id', 64);
            $t->string('reason', 255);
            $t->foreignId('placed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('placed_at')->useCurrent();
            $t->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('released_at')->nullable();
            $t->timestampsTz();
            $t->index(['subject_type', 'subject_id', 'released_at']);
        });

        Schema::create('retention_rules', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 60)->unique();
            $t->string('subject_type', 120);
            $t->string('description', 255);
            $t->unsignedSmallInteger('retention_months');
            // ARCHIVE | PURGE | ANONYMISE
            $t->string('disposal_action', 20);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        Schema::create('email_verification_tokens', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->string('sent_to', 190);
            $t->timestampTz('expires_at');
            $t->timestampTz('consumed_at')->nullable();
            $t->timestampsTz();
            $t->index(['user_id', 'consumed_at']);
        });

        Schema::create('login_attempts', function (Blueprint $t): void {
            $t->id();
            // Hashed so that a log dump does not enumerate registered addresses.
            $t->string('email_hash', 64)->index();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('ip_address', 45)->nullable()->index();
            $t->string('user_agent', 255)->nullable();
            $t->boolean('successful')->default(false);
            $t->string('failure_reason', 60)->nullable();
            $t->timestampTz('occurred_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        foreach ([
            'login_attempts', 'email_verification_tokens', 'retention_rules', 'legal_holds',
            'consent_records', 'audit_events', 'outbox_events', 'idempotency_keys',
            'number_sequences', 'api_tokens', 'user_roles', 'role_permissions', 'roles', 'permissions',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $t): void {
            $t->dropColumn([
                'public_id', 'middle_name', 'password_changed_at', 'failed_login_count', 'locked_until',
                'last_login_at', 'last_login_ip', 'mfa_secret', 'mfa_enabled_at', 'mfa_recovery_codes',
            ]);
        });
    }
};
