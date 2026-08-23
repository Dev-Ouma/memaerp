<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Point-in-time organisational attribution on the tables that authorization filters against.
 *
 * A course offering's department is reachable by joining through the course, and a student's by
 * joining through the programme — so at first glance these columns are redundant. They are not,
 * for two reasons:
 *
 * 1. **Correctness.** Departments merge, split and get renamed. An offering taught in 2026 under
 *    Computer Science must stay attributed to Computer Science even if the course later moves to
 *    Software Engineering; otherwise reorganising the university silently rewrites who taught
 *    what, and rewrites which Dean's approval was valid at the time. The join gives today's
 *    answer to a historical question.
 *
 * 2. **Cost.** Scope filtering runs on nearly every authenticated read. Two extra joins on the
 *    hottest path in the system, during registration week, is not a trade worth making for
 *    columns that never change once written.
 *
 * These are set once at creation and then left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course.course_offerings', function (Blueprint $table): void {
            $table->foreignUuid('department_id')->nullable()
                ->constrained('institution.departments')->nullOnDelete();
            $table->foreignUuid('faculty_id')->nullable()
                ->constrained('institution.faculties')->nullOnDelete();

            $table->index(['institution_id', 'department_id'], 'offerings_department_idx');
            $table->index(['institution_id', 'faculty_id'], 'offerings_faculty_idx');
            $table->index(['institution_id', 'lecturer_id'], 'offerings_lecturer_idx');
        });

        Schema::table('student.students', function (Blueprint $table): void {
            $table->foreignUuid('department_id')->nullable()
                ->constrained('institution.departments')->nullOnDelete();
            $table->foreignUuid('faculty_id')->nullable()
                ->constrained('institution.faculties')->nullOnDelete();

            $table->index(['institution_id', 'department_id'], 'students_department_idx');
            $table->index(['institution_id', 'faculty_id'], 'students_faculty_idx');
        });

        // Backfill from the current structure. Correct for existing rows, because nothing has
        // been reorganised yet — which is exactly why this has to happen before it is.
        DB::statement('
            UPDATE course.course_offerings o
            SET department_id = c.department_id,
                faculty_id    = d.faculty_id
            FROM course.courses c
            JOIN institution.departments d ON d.id = c.department_id
            WHERE o.course_id = c.id
        ');

        DB::statement('
            UPDATE student.students s
            SET department_id = p.department_id,
                faculty_id    = d.faculty_id
            FROM curriculum.programmes p
            JOIN institution.departments d ON d.id = p.department_id
            WHERE s.programme_id = p.id
        ');
    }

    public function down(): void
    {
        Schema::table('course.course_offerings', function (Blueprint $table): void {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['department_id', 'faculty_id']);
        });

        Schema::table('student.students', function (Blueprint $table): void {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['department_id', 'faculty_id']);
        });
    }
};
