<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_centers', function (Blueprint $table): void {
            $table->id();
            $table->string('center_code', 40)->unique();
            $table->string('name');
            $table->string('location');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('proctors_allocated')->default(0);
            $table->string('special_needs_access')->nullable();
            $table->string('status', 30)->default('OPERATIONAL')->index();
            $table->timestamps();
        });
        Schema::create('exam_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_code', 40)->unique();
            $table->string('session_title');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('daily_slots');
            $table->date('moderation_deadline');
            $table->string('status', 30)->default('DRAFT')->index();
            $table->timestamps();
        });
        Schema::create('exam_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('exam_center_id')->constrained()->restrictOnDelete();
            $table->foreignId('chief_invigilator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('exam_date');
            $table->string('slot', 30);
            $table->unsignedInteger('candidate_count')->default(0);
            $table->string('status', 30)->default('SCHEDULED')->index();
            $table->timestamps();
            $table->unique(['exam_session_id', 'subject_id']);
            $table->unique(['exam_center_id', 'exam_date', 'slot']);
        });
        Schema::create('grade_scales', function (Blueprint $table): void {
            $table->id();
            $table->string('grade_letter', 5)->unique();
            $table->decimal('min_marks', 5, 2);
            $table->decimal('max_marks', 5, 2);
            $table->decimal('gpa_points', 3, 2);
            $table->string('performance_descriptor');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE grade_scales ADD CONSTRAINT grade_scales_marks_range_check CHECK (min_marks >= 0 AND max_marks <= 100 AND min_marks <= max_marks)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_centers');
    }
};
