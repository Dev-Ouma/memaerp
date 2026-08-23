<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-04: Course Master Catalogue & Semester Offerings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course.courses', function (Blueprint $table): void {
            $table->string('status', 32)->default('ACTIVE');
            $table->text('learning_outcomes')->nullable();
            $table->text('syllabus_outline')->nullable();
            $table->string('department_board_ref', 128)->nullable();
            $table->string('school_board_ref', 128)->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('discontinued_at')->nullable();
        });

        DB::statement("ALTER TABLE course.courses ADD CONSTRAINT courses_status_valid CHECK (status IN ('DRAFT', 'UNDER_REVIEW', 'ACTIVE', 'DISCONTINUED'))");

        Schema::table('course.course_offerings', function (Blueprint $table): void {
            $table->string('status', 32)->default('OFFERED');
            $table->unsignedSmallInteger('waitlist_count')->default(0);
            $table->unsignedSmallInteger('workload_credits')->default(0);
            $table->timestampTz('closed_at')->nullable();
        });

        DB::statement("ALTER TABLE course.course_offerings ADD CONSTRAINT offerings_status_valid CHECK (status IN ('OFFERED', 'CLOSED'))");

        Schema::create('course.course_reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained('course.courses')->cascadeOnDelete();
            $table->string('stage', 32);
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 24)->default('PENDING');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('reference', 128)->nullable();
            $table->text('comments')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['course_id', 'stage']);
        });

        Schema::create('course.offering_allocations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
            $table->foreignUuid('lecturer_id')->constrained('iam.users')->restrictOnDelete();
            $table->string('role', 24)->default('PRIMARY');
            $table->unsignedSmallInteger('workload_credits')->default(0);
            $table->timestampsTz();

            $table->unique(['course_offering_id', 'lecturer_id']);
        });

        Schema::create('course.offering_waitlist', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('status', 24)->default('WAITING');
            $table->timestampsTz();

            $table->unique(['course_offering_id', 'student_id']);
        });

        DB::statement("ALTER TABLE course.offering_allocations ADD CONSTRAINT offering_allocation_role_valid CHECK (role IN ('PRIMARY', 'ASSISTANT'))");
        DB::statement("ALTER TABLE course.offering_waitlist ADD CONSTRAINT offering_waitlist_status_valid CHECK (status IN ('WAITING', 'PROMOTED', 'WITHDRAWN'))");
        DB::statement('SELECT public.enforce_governance_columns()');
    }

    public function down(): void
    {
        Schema::dropIfExists('course.offering_waitlist');
        Schema::dropIfExists('course.offering_allocations');
        Schema::dropIfExists('course.course_reviews');
        DB::statement('ALTER TABLE course.course_offerings DROP CONSTRAINT IF EXISTS offerings_status_valid');
        Schema::table('course.course_offerings', function (Blueprint $table): void {
            $table->dropColumn(['status', 'waitlist_count', 'workload_credits', 'closed_at']);
        });
        DB::statement('ALTER TABLE course.courses DROP CONSTRAINT IF EXISTS courses_status_valid');
        Schema::table('course.courses', function (Blueprint $table): void {
            $table->dropColumn([
                'status', 'learning_outcomes', 'syllabus_outline',
                'department_board_ref', 'school_board_ref', 'approved_at', 'discontinued_at',
            ]);
        });
    }
};
