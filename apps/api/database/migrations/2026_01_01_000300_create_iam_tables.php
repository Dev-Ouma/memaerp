<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-00-01 / MOD-01-01 — Identity, Authentication & Access Control.
 *
 * Authorization has three orthogonal dimensions (ADR-008):
 *   permission — what action, e.g. examination.marks.approve
 *   role       — a named bundle of permissions
 *   scope      — the slice of the institution it applies to
 *
 * Holding a permission is never sufficient. A Head of Department with
 * examination.marks.approve may approve marks only within their department scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iam.users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();

            $table->string('email')->unique();
            $table->string('username', 64)->nullable()->unique();
            $table->string('password');

            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);
            $table->timestampTz('password_changed_at')->nullable();

            // MFA. Secrets are encrypted at rest by the model cast, never stored in the clear.
            $table->boolean('mfa_enabled')->default(false);
            $table->text('mfa_secret')->nullable();
            $table->jsonb('mfa_recovery_codes')->nullable();

            $table->timestampTz('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedSmallInteger('failed_login_attempts')->default(0);
            $table->timestampTz('locked_until')->nullable();

            $table->timestampTz('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['institution_id', 'is_active']);
        });

        Schema::create('iam.permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            // module.resource.action — e.g. finance.invoice.approve
            $table->string('name', 128)->unique();
            $table->string('module', 64);
            $table->string('resource', 64);
            $table->string('action', 32);
            $table->string('description');
            // Marks permissions that carry financial or academic-record consequence.
            $table->boolean('is_sensitive')->default(false);
            $table->timestampsTz();

            $table->index(['module', 'resource']);
        });

        Schema::create('iam.roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('description')->nullable();
            // registry | finance | academic | hr | executive | student | system
            $table->string('family', 32);
            // System roles cannot be edited or deleted through the UI.
            $table->boolean('is_system')->default(false);
            $table->timestampsTz();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('iam.permission_role', function (Blueprint $table): void {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('role_id')->constrained('iam.roles')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('iam.permissions')->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['role_id', 'permission_id']);
        });

        // The scoped assignment: this user holds this role, over this slice of the institution.
        Schema::create('iam.role_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('iam.users')->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('iam.roles')->cascadeOnDelete();

            // institution | campus | faculty | department | self
            $table->string('scope_type', 32);
            // Null for institution-wide and self scopes.
            $table->uuid('scope_id')->nullable();

            // Time-boxed assignments support acting appointments and delegation.
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();

            $table->foreignUuid('granted_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('grant_reason')->nullable();
            $table->timestampsTz();

            $table->unique(['user_id', 'role_id', 'scope_type', 'scope_id'], 'role_assignments_unique');
            $table->index(['user_id', 'scope_type']);
        });

        Schema::create('iam.login_attempts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->nullable()->constrained('institution.institutions')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('email');
            $table->boolean('succeeded');
            $table->string('failure_reason', 64)->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestampTz('attempted_at');

            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });

        // Break-glass impersonation (MOD-00-05). Every session is bounded and fully attributed.
        Schema::create('iam.impersonation_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('impersonator_id')->constrained('iam.users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('iam.users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('ticket_reference', 64)->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('expires_at');
            $table->timestampTz('ended_at')->nullable();
            $table->timestampsTz();

            $table->index(['subject_id', 'started_at']);
        });

        DB::statement("COMMENT ON TABLE iam.role_assignments IS
            'Scoped role grants. Permission alone never authorises an action - scope is applied as a query filter, not a post-fetch check.'");
    }

    public function down(): void
    {
        Schema::dropIfExists('iam.impersonation_sessions');
        Schema::dropIfExists('iam.login_attempts');
        Schema::dropIfExists('iam.role_assignments');
        Schema::dropIfExists('iam.permission_role');
        Schema::dropIfExists('iam.roles');
        Schema::dropIfExists('iam.permissions');
        Schema::dropIfExists('iam.users');
    }
};
