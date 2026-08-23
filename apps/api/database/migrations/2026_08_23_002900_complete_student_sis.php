<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-06: Student Matriculation & Master Records (SIS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student.students', function (Blueprint $table): void {
            $table->foreignUuid('application_id')->nullable()->after('person_id')
                ->constrained('admission.applications')->nullOnDelete();
            $table->foreignUuid('intake_id')->nullable()->after('admission_year_id')
                ->constrained('institution.intakes')->nullOnDelete();
            $table->foreignUuid('study_mode_id')->nullable()->after('intake_id')
                ->constrained('institution.study_modes')->nullOnDelete();
            $table->string('photo_url', 500)->nullable()->after('status');
            $table->string('digital_id_token', 64)->nullable()->unique()->after('photo_url');
            $table->timestampTz('digital_id_issued_at')->nullable()->after('digital_id_token');
            $table->string('digital_id_status', 32)->default('INACTIVE')->after('digital_id_issued_at');

            $table->unique('application_id');
            $table->index(['institution_id', 'intake_id']);
        });

        DB::statement("ALTER TABLE student.students ADD CONSTRAINT students_digital_id_status_valid CHECK (digital_id_status IN ('INACTIVE', 'ACTIVE', 'REVOKED', 'REPLACED'))");

        Schema::create('student.number_sequences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('programme_id')->constrained('curriculum.programmes')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestampsTz();

            $table->unique(['institution_id', 'programme_id', 'academic_year_id'], 'student_number_sequence_unique');
        });

        Schema::create('student.matriculation_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('admission.applications')->restrictOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->restrictOnDelete();
            $table->foreignUuid('matriculated_by')->constrained('iam.users')->restrictOnDelete();
            $table->timestampTz('matriculated_at');
            $table->boolean('original_documents_verified')->default(true);
            $table->boolean('pledge_signed')->default(false);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique('application_id');
            $table->index(['institution_id', 'matriculated_at']);
        });

        Schema::create('student.student_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('original_name');
            $table->string('disk_path');
            $table->string('mime_type', 128);
            $table->unsignedInteger('byte_size');
            $table->string('verification_status', 32)->default('PENDING');
            $table->foreignUuid('verified_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();

            $table->index(['student_id', 'document_type']);
        });

        Schema::create('student.next_of_kin', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('relationship', 64);
            $table->string('phone', 32);
            $table->string('email', 255)->nullable();
            $table->jsonb('address')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestampsTz();

            $table->index(['student_id', 'is_primary']);
        });

        Schema::create('student.status_history', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('student.students')->cascadeOnDelete();
            $table->string('from_status', 32);
            $table->string('to_status', 32);
            $table->text('reason');
            $table->foreignUuid('changed_by')->constrained('iam.users')->restrictOnDelete();
            $table->timestampTz('changed_at');
            $table->timestampsTz();

            $table->index(['student_id', 'changed_at']);
        });

        DB::statement('SELECT public.enforce_governance_columns()');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE student.students DROP CONSTRAINT IF EXISTS students_digital_id_status_valid');

        Schema::dropIfExists('student.status_history');
        Schema::dropIfExists('student.next_of_kin');
        Schema::dropIfExists('student.student_documents');
        Schema::dropIfExists('student.matriculation_logs');
        Schema::dropIfExists('student.number_sequences');

        Schema::table('student.students', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('application_id');
            $table->dropConstrainedForeignId('intake_id');
            $table->dropConstrainedForeignId('study_mode_id');
            $table->dropColumn(['photo_url', 'digital_id_token', 'digital_id_issued_at', 'digital_id_status']);
        });
    }
};
