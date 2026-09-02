<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_intakes', function (Blueprint $t): void {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->date('opens_at');
            $t->date('closes_at');
            $t->date('acceptance_deadline');
            $t->boolean('is_published')->default(false);
            $t->timestampsTz();
        });
        Schema::create('programme_offerings', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('course_id')->constrained()->restrictOnDelete();
            $t->foreignId('admission_intake_id')->constrained()->restrictOnDelete();
            $t->string('campus')->default('Main Campus');
            $t->string('study_mode')->default('Full-time');
            $t->unsignedInteger('capacity')->default(50);
            $t->unsignedInteger('application_fee')->default(1000);
            $t->text('requirements')->nullable();
            $t->boolean('is_published')->default(false);
            $t->timestampsTz();
            $t->unique(['course_id', 'admission_intake_id', 'campus', 'study_mode'], 'offering_unique');
        });
        Schema::create('applicant_profiles', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('applicant_number')->unique();
            $t->date('date_of_birth')->nullable();
            $t->string('phone', 32)->nullable();
            $t->string('nationality', 80)->default('Kenyan');
            $t->string('county', 80)->nullable();
            $t->string('identity_type', 30)->nullable();
            $t->text('identity_number')->nullable();
            $t->boolean('has_support_need')->default(false);
            $t->text('support_details')->nullable();
            $t->string('source_channel', 60)->nullable();
            $t->string('qr_token', 64)->unique();
            $t->timestampsTz();
        });
        Schema::create('admission_applications', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('applicant_profile_id')->constrained()->cascadeOnDelete();
            $t->foreignId('programme_offering_id')->constrained()->restrictOnDelete();
            $t->string('application_number')->unique();
            $t->string('status', 40)->default('DRAFT')->index();
            $t->jsonb('form_data')->default('{}');
            $t->unsignedInteger('completion_percent')->default(20);
            $t->unsignedInteger('lock_version')->default(1);
            $t->boolean('declarations_accepted')->default(false);
            $t->timestampTz('submitted_at')->nullable();
            $t->timestampTz('decision_at')->nullable();
            $t->timestampsTz();
            $t->unique(['applicant_profile_id', 'programme_offering_id']);
        });
        Schema::create('application_documents', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained()->cascadeOnDelete();
            $t->string('document_type', 60);
            $t->string('original_name');
            $t->string('storage_path');
            $t->string('mime_type', 100);
            $t->unsignedBigInteger('size_bytes');
            $t->string('sha256', 64);
            $t->string('verification_status', 30)->default('PENDING');
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
        });
        Schema::create('application_payment_attempts', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained()->cascadeOnDelete();
            $t->string('reference')->unique();
            $t->string('channel', 30);
            $t->unsignedInteger('amount')->default(1000);
            $t->string('currency', 3)->default('KES');
            $t->string('status', 30)->default('INITIATED')->index();
            $t->string('idempotency_key')->unique();
            $t->timestampTz('paid_at')->nullable();
            $t->string('receipt_number')->nullable()->unique();
            $t->jsonb('provider_payload')->default('{}');
            $t->timestampsTz();
        });
        Schema::create('application_versions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('version');
            $t->jsonb('snapshot');
            $t->string('checksum', 64);
            $t->timestampTz('created_at')->useCurrent();
            $t->unique(['admission_application_id', 'version']);
        });
        Schema::create('application_status_history', function (Blueprint $t): void {
            $t->id();
            $t->foreignUuid('admission_application_id')->constrained()->cascadeOnDelete();
            $t->string('from_status', 40)->nullable();
            $t->string('to_status', 40);
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reason_code', 80)->nullable();
            $t->text('note')->nullable();
            $t->timestampTz('created_at')->useCurrent();
        });
        Schema::create('application_reviews', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $t->string('stage', 40);
            $t->unsignedInteger('score')->nullable();
            $t->string('recommendation', 40);
            $t->text('notes')->nullable();
            $t->timestampTz('created_at')->useCurrent();
        });
        Schema::create('admission_offers', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('offer_number')->unique();
            $t->string('verification_token', 64)->unique();
            $t->string('status', 30)->default('ISSUED');
            $t->date('expires_at');
            $t->text('conditions')->nullable();
            $t->string('checksum', 64);
            $t->timestampTz('issued_at')->useCurrent();
            $t->timestampTz('responded_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['admission_offers', 'application_reviews', 'application_status_history', 'application_versions', 'application_payment_attempts', 'application_documents', 'admission_applications', 'applicant_profiles', 'programme_offerings', 'admission_intakes'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
