<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-02-01: LMS two-way synchronization hub (Moodle).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lms');

        if (! Schema::hasTable('lms.sync_logs')) {
            Schema::create('lms.sync_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->string('sync_type', 50);
                $table->uuid('entity_id');
                $table->string('direction', 20);
                $table->string('status', 20)->default('PENDING');
                $table->text('error_message')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->timestampsTz();

                $table->index(['institution_id', 'sync_type', 'status']);
                $table->index(['entity_id', 'sync_type']);
            });
        }

        if (! Schema::hasTable('lms.course_mappings')) {
            Schema::create('lms.course_mappings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('course_offering_id')->constrained('course.course_offerings')->cascadeOnDelete();
                $table->unsignedBigInteger('moodle_course_id')->nullable();
                $table->string('moodle_shortname', 128)->nullable();
                $table->string('status', 32)->default('PENDING');
                $table->timestampTz('last_synced_at')->nullable();
                $table->timestampsTz();

                $table->unique(['course_offering_id']);
                $table->unique(['institution_id', 'moodle_course_id']);
            });
        }

        if (! Schema::hasTable('lms.enrollment_mappings')) {
            Schema::create('lms.enrollment_mappings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
                $table->foreignUuid('course_enrollment_id')->constrained('enrollment.course_enrollments')->cascadeOnDelete();
                $table->unsignedBigInteger('moodle_enrollment_id')->nullable();
                $table->string('status', 32)->default('PENDING');
                $table->timestampTz('last_synced_at')->nullable();
                $table->timestampsTz();

                $table->unique(['course_enrollment_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lms.enrollment_mappings');
        Schema::dropIfExists('lms.course_mappings');
        Schema::dropIfExists('lms.sync_logs');
    }
};
