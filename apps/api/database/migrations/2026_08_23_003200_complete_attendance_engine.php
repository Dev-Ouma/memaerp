<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-02-02: Class attendance, QR clock-in and session registers.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS attendance');

        if (! Schema::hasTable('attendance.sessions')) {
            Schema::create('attendance.sessions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
                $table->foreignUuid('teaching_slot_id')->nullable()->constrained('course.teaching_slots')->nullOnDelete();
                $table->foreignUuid('lecturer_id')->constrained('iam.users')->restrictOnDelete();
                $table->date('session_date');
                $table->string('status', 20)->default('OPEN');
                $table->string('qr_token_hash', 64)->unique();
                $table->timestampTz('expires_at');
                $table->timestampTz('opened_at');
                $table->timestampTz('closed_at')->nullable();
                $table->timestampsTz();

                $table->index(['course_offering_id', 'session_date']);
                $table->index(['institution_id', 'status']);
            });
        }

        if (! Schema::hasTable('attendance.session_logs')) {
            Schema::create('attendance.session_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('session_id')->constrained('attendance.sessions')->cascadeOnDelete();
                $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->date('session_date');
                $table->timestampTz('check_in_time')->nullable();
                $table->string('status', 20)->default('PRESENT');
                $table->string('method', 20)->default('QR');
                $table->timestampsTz();

                $table->unique(['session_id', 'student_id']);
                $table->unique(['course_offering_id', 'student_id', 'session_date'], 'attendance_daily_unique');
                $table->index(['student_id', 'course_offering_id']);
            });
        }

        if (! Schema::hasTable('attendance.at_risk_alerts')) {
            Schema::create('attendance.at_risk_alerts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
                $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
                $table->decimal('attendance_percentage', 5, 2);
                $table->decimal('threshold_percentage', 5, 2)->default(75);
                $table->string('status', 20)->default('OPEN');
                $table->timestampTz('flagged_at');
                $table->timestampTz('resolved_at')->nullable();
                $table->timestampsTz();

                $table->unique(['student_id', 'course_offering_id', 'status'], 'attendance_open_risk_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance.at_risk_alerts');
        Schema::dropIfExists('attendance.session_logs');
        Schema::dropIfExists('attendance.sessions');
    }
};
