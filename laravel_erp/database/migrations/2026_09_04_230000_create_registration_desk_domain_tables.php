<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuccps_placements', function (Blueprint $table): void {
            $table->id();
            $table->string('kuccps_index', 40)->unique();
            $table->string('student_name', 190);
            $table->string('placed_programme', 190)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('county', 80)->nullable();
            $table->decimal('cluster_points', 8, 2)->nullable();
            $table->string('mema_reg_no', 40)->nullable()->index();
            $table->string('reporting_status', 40)->default('Unclaimed')->index();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('student_promotions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('programme', 190)->nullable();
            $table->string('from_stage', 80)->nullable();
            $table->string('to_stage', 80)->nullable();
            $table->decimal('cumulative_gpa', 4, 2)->nullable();
            $table->unsignedSmallInteger('credits_passed')->nullable();
            $table->string('promotion_verdict', 120)->default('Promoted')->index();
            $table->date('senate_date')->nullable();
            $table->timestamps();
        });

        Schema::create('cpd_enrolments', function (Blueprint $table): void {
            $table->id();
            $table->string('participant_no', 40)->unique();
            $table->string('full_name', 190);
            $table->string('organization', 190)->nullable();
            $table->string('course_enrolled', 190)->nullable();
            $table->string('completion_progress', 40)->nullable();
            $table->decimal('cpd_points_awarded', 8, 2)->default(0);
            $table->string('certificate_ref', 80)->nullable();
            $table->string('status', 40)->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('moodle_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('unit_code', 40)->index();
            $table->string('unit_title', 190);
            $table->string('moodle_course_id', 80)->nullable();
            $table->unsignedInteger('enrolled_students')->default(0);
            $table->string('instructor_assigned', 190)->nullable();
            $table->string('sync_status', 40)->default('Pending')->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_info_update_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_no', 40)->unique();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name', 190);
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('update_type', 80)->nullable();
            $table->text('current_details')->nullable();
            $table->text('requested_details')->nullable();
            $table->string('verification_status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('registration_reminder_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('campaign_code', 40)->unique();
            $table->string('title', 190);
            $table->string('target_audience', 120)->nullable();
            $table->string('dispatch_channels', 120)->nullable();
            $table->string('trigger_schedule', 120)->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->string('status', 40)->default('Draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_reminder_campaigns');
        Schema::dropIfExists('student_info_update_requests');
        Schema::dropIfExists('moodle_sync_logs');
        Schema::dropIfExists('cpd_enrolments');
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('kuccps_placements');
    }
};
