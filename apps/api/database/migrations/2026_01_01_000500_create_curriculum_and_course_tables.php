<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-03: Programme Structure & Curriculum Engine.
 * MOD-01-04: Course Master Catalogue & Semester Offerings.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Programmes
        Schema::create('curriculum.programmes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('institution.departments')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 200);
            $table->string('award_level', 50); // BACHELORS, MASTERS, DIPLOMA, CERTIFICATE, DOCTORATE
            $table->unsignedSmallInteger('duration_years')->default(4);
            $table->unsignedSmallInteger('total_credits_required')->default(120);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'department_id']);
        });

        // 2. Versioned Curricula
        Schema::create('curriculum.curriculum_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('effective_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('version_code', 50);
            $table->string('senate_approval_ref', 100)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestampTz('approved_at')->nullable();
            $table->timestampsTz();

            $table->unique(['programme_id', 'version_code']);
        });

        // 3. Master Course Catalogue
        Schema::create('course.courses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('institution.departments')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('credits')->default(3);
            $table->unsignedSmallInteger('lecture_hours')->default(3);
            $table->unsignedSmallInteger('lab_hours')->default(0);
            $table->unsignedSmallInteger('tutorial_hours')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'department_id']);
        });

        // 4. Course Prerequisites
        Schema::create('course.course_prerequisites', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('course.courses')->cascadeOnDelete();
            $table->foreignUuid('prerequisite_course_id')->constrained('course.courses')->cascadeOnDelete();
            $table->string('requirement_type', 32)->default('PREREQUISITE'); // PREREQUISITE | COREQUISITE | ANTIREQUISITE
            $table->timestampsTz();

            $table->unique(['course_id', 'prerequisite_course_id', 'requirement_type'], 'course_req_unique');
        });

        // 5. Curriculum Course Mappings (Semester by Semester Grid)
        Schema::create('curriculum.curriculum_courses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('curriculum_version_id')->constrained('curriculum.curriculum_versions')->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained('course.courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('year_level'); // 1, 2, 3, 4
            $table->unsignedSmallInteger('semester'); // 1, 2, 3
            $table->string('course_type', 32)->default('CORE'); // CORE | ELECTIVE | REQUIRED_AUDIT
            $table->string('elective_group', 64)->nullable();
            $table->timestampsTz();

            $table->unique(['curriculum_version_id', 'course_id']);
            $table->index(['curriculum_version_id', 'year_level', 'semester']);
        });

        // 6. Semester Course Offerings & Sections
        Schema::create('course.course_offerings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained('course.courses')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('institution.terms')->cascadeOnDelete();
            $table->foreignUuid('campus_id')->constrained('institution.campuses')->cascadeOnDelete();
            $table->foreignUuid('lecturer_id')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('section_code', 16)->default('A');
            $table->unsignedSmallInteger('max_capacity')->default(60);
            $table->unsignedSmallInteger('enrolled_count')->default(0);
            $table->string('delivery_mode', 32)->default('IN_PERSON'); // IN_PERSON | ONLINE | HYBRID
            $table->boolean('is_open_for_enrollment')->default(true);
            $table->timestampsTz();

            $table->unique(['course_id', 'term_id', 'campus_id', 'section_code'], 'offering_section_unique');
            $table->index(['institution_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course.course_offerings');
        Schema::dropIfExists('curriculum.curriculum_courses');
        Schema::dropIfExists('course.course_prerequisites');
        Schema::dropIfExists('course.courses');
        Schema::dropIfExists('curriculum.curriculum_versions');
        Schema::dropIfExists('curriculum.programmes');
    }
};
