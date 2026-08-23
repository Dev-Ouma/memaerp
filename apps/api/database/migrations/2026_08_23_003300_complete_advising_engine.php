<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-02-03: Academic advising assignments, notes and degree progress tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS advising');

        if (! Schema::hasTable('advising.advisor_assignments')) {
            Schema::create('advising.advisor_assignments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('advisor_user_id')->constrained('iam.users')->restrictOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->timestampTz('assigned_at');
                $table->boolean('is_active')->default(true);
                $table->foreignUuid('assigned_by')->nullable()->constrained('iam.users')->nullOnDelete();
                $table->text('assignment_reason')->nullable();
                $table->timestampsTz();

                $table->index(['advisor_user_id', 'is_active']);
                $table->index(['institution_id', 'is_active']);
                $table->index('student_id');
            });

            DB::statement('CREATE UNIQUE INDEX advising_one_active_advisor ON advising.advisor_assignments (student_id) WHERE is_active = true');
        }

        if (! Schema::hasTable('advising.advisory_notes')) {
            Schema::create('advising.advisory_notes', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->foreignUuid('advisor_user_id')->constrained('iam.users')->restrictOnDelete();
                $table->string('note_type', 40)->default('GENERAL');
                $table->text('note_text');
                $table->boolean('is_confidential')->default(true);
                $table->boolean('visible_to_student')->default(false);
                $table->string('follow_up_status', 20)->default('NONE');
                $table->timestampTz('follow_up_at')->nullable();
                $table->timestampsTz();

                $table->index(['student_id', 'created_at']);
                $table->index(['advisor_user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('advising.advising_sessions')) {
            Schema::create('advising.advising_sessions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->foreignUuid('advisor_user_id')->constrained('iam.users')->restrictOnDelete();
                $table->timestampTz('scheduled_at');
                $table->string('status', 20)->default('REQUESTED');
                $table->string('mode', 20)->default('IN_PERSON');
                $table->text('topic')->nullable();
                $table->text('outcome')->nullable();
                $table->timestampsTz();

                $table->index(['advisor_user_id', 'scheduled_at']);
                $table->index(['student_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advising.advising_sessions');
        Schema::dropIfExists('advising.advisory_notes');
        Schema::dropIfExists('advising.advisor_assignments');
    }
};
