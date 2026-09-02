<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('title', 30)->nullable();
            $table->string('username', 80)->nullable()->unique();
            $table->string('phone_number', 40)->nullable();
            $table->string('department', 100)->nullable();
            $table->timestampTz('first_login_at')->nullable();
            $table->timestampTz('last_successful_login_at')->nullable();
            $table->string('recovery_email', 150)->nullable();
            $table->string('email_verification_token', 100)->nullable();
            $table->string('email_change_pending', 150)->nullable();
            $table->text('description')->nullable();
        });

        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->jsonb('accessibility_settings')->default('{}');
            $table->jsonb('privacy_settings')->default('{}');
            $table->jsonb('communication_settings')->default('{}');
            $table->jsonb('learning_settings')->default('{}');
        });

        Schema::create('login_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email_or_username', 150);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('status', 20); // success, failed
            $table->timestampTz('occurred_at')->useCurrent();
        });

        Schema::create('calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->timestampTz('start_time');
            $table->timestampTz('end_time');
            $table->boolean('is_all_day')->default(false);
            $table->string('category', 60)->default('Personal');
            $table->string('color', 30)->default('blue');
            $table->string('recurrence', 30)->nullable(); // none, daily, weekly, monthly, yearly
            $table->unsignedInteger('reminder_minutes')->nullable();
            $table->string('google_event_id', 150)->nullable();
            $table->timestampsTz();
        });

        Schema::create('user_calendar_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestampTz('expires_at')->nullable();
            $table->jsonb('selected_calendars')->nullable();
            $table->string('sync_direction', 20)->default('two-way');
            $table->timestampTz('last_sync_at')->nullable();
            $table->string('last_sync_status', 30)->nullable();
            $table->timestampsTz();
        });

        Schema::create('personal_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('personal_files')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('path', 500)->nullable(); // folders are null
            $table->boolean('is_folder')->default(false);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 10)->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('malware_status', 20)->default('clean');
            $table->softDeletesTz();
            $table->timestampsTz();
        });

        Schema::create('personal_file_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('action', 50); // upload, rename, move, delete, restore, download
            $table->unsignedBigInteger('file_size')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('result', 50);
            $table->timestampTz('occurred_at')->useCurrent();
        });

        Schema::create('personal_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('source', 80);
            $table->jsonb('columns')->default('[]');
            $table->jsonb('filters')->default('[]');
            $table->jsonb('sorting')->default('[]');
            $table->jsonb('grouping')->default('[]');
            $table->jsonb('options')->default('{}');
            $table->boolean('is_draft')->default(true);
            $table->timestampsTz();
        });

        Schema::create('report_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('personal_report_id')->constrained('personal_reports')->cascadeOnDelete();
            $table->string('frequency', 30); // daily, weekly, monthly
            $table->string('delivery_email', 150);
            $table->string('format', 10)->default('csv'); // csv, pdf
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('user_trusted_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_name', 100);
            $table->string('browser', 100);
            $table->ipAddress('ip_address');
            $table->string('location', 150)->nullable();
            $table->string('token', 100)->unique();
            $table->timestampTz('last_used_at')->useCurrent();
            $table->timestampsTz();
        });

        Schema::create('security_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('credential_id', 255)->unique();
            $table->text('public_key');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_keys');
        Schema::dropIfExists('user_trusted_devices');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('personal_reports');
        Schema::dropIfExists('personal_file_logs');
        Schema::dropIfExists('personal_files');
        Schema::dropIfExists('user_calendar_connections');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('login_activities');

        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->dropColumn(['accessibility_settings', 'privacy_settings', 'communication_settings', 'learning_settings']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'title', 'username', 'phone_number', 'department',
                'first_login_at', 'last_successful_login_at',
                'recovery_email', 'email_verification_token', 'email_change_pending', 'description',
            ]);
        });
    }
};
