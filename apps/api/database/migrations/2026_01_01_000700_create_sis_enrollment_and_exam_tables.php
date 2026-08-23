<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-06: Student Onboarding, Matriculation & SIS Records.
 * MOD-01-07: Semester Registration & Course Enrollment Engine.
 * MOD-01-10: Coursework Assessment & Examination Management.
 * MOD-01-11: Grading, GPA & Academic Progression Engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Matriculated Students
        Schema::create('student.students', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('student.persons')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('campus_id')->constrained('institution.campuses')->cascadeOnDelete();
            $table->foreignUuid('admission_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('student_number', 50)->unique();
            $table->unsignedSmallInteger('current_year_level')->default(1);
            $table->unsignedSmallInteger('current_semester')->default(1);
            $table->string('academic_standing', 32)->default('GOOD_STANDING'); // GOOD_STANDING | PROBATION | SUSPENDED | DISCONTINUED
            $table->string('status', 32)->default('ACTIVE'); // ACTIVE | ON_LEAVE | SUSPENDED | GRADUATED | WITHDRAWN
            $table->date('matriculated_on');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['institution_id', 'status']);
        });

        // 2. Term Registrations
        Schema::create('enrollment.term_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('institution.terms')->cascadeOnDelete();
            $table->unsignedSmallInteger('year_level')->default(1);
            $table->unsignedSmallInteger('semester')->default(1);
            $table->boolean('financial_clearance_status')->default(true);
            $table->string('status', 32)->default('REGISTERED'); // REGISTERED | PENDING_CLEARANCE | PROVISIONAL
            $table->timestampTz('registered_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['student_id', 'term_id']);
        });

        // 3. Course Enrollments
        Schema::create('enrollment.course_enrollments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('term_registration_id')->constrained('enrollment.term_registrations')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
            $table->string('status', 32)->default('ENROLLED'); // ENROLLED | DROPPED | AUDITING
            $table->boolean('is_retake')->default(false);
            $table->timestampTz('enrolled_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['student_id', 'course_offering_id']);
        });

        // 4. Student Marks & Assessments
        Schema::create('examination.student_marks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('course_enrollment_id')->constrained('enrollment.course_enrollments')->cascadeOnDelete();
            $table->decimal('cat_score', 5, 2)->default(0.00);
            $table->decimal('exam_score', 5, 2)->default(0.00);
            $table->decimal('total_score', 5, 2)->default(0.00);
            $table->string('letter_grade', 4)->nullable();
            $table->decimal('grade_points', 4, 2)->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->foreignUuid('submitted_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('approval_status', 32)->default('DRAFT'); // DRAFT | SUBMITTED | VERIFIED | BOARD_APPROVED | SENATE_RATIFIED
            $table->timestampsTz();

            $table->unique('course_enrollment_id');
        });

        // 5. Term GPAs & Progression
        Schema::create('examination.term_gpas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('institution.terms')->cascadeOnDelete();
            $table->unsignedSmallInteger('credits_attempted')->default(0);
            $table->unsignedSmallInteger('credits_earned')->default(0);
            $table->decimal('gpa', 4, 2)->default(0.00);
            $table->decimal('cgpa', 4, 2)->default(0.00);
            $table->string('academic_standing', 32)->default('GOOD_STANDING');
            $table->timestampsTz();

            $table->unique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination.term_gpas');
        Schema::dropIfExists('examination.student_marks');
        Schema::dropIfExists('enrollment.course_enrollments');
        Schema::dropIfExists('enrollment.term_registrations');
        Schema::dropIfExists('student.students');
    }
};
