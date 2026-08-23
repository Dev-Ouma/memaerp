<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-02 — closes the institutional hierarchy, calendar and universal lookup foundation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution.campuses', function (Blueprint $table): void {
            $table->boolean('is_main_campus')->default(false);
            $table->string('status', 32)->default('ACTIVE');
            $table->string('resolution_reference', 128)->nullable();
        });

        Schema::table('institution.faculties', function (Blueprint $table): void {
            $table->string('status', 32)->default('ACTIVE');
            $table->string('resolution_reference', 128)->nullable();
        });

        Schema::table('institution.departments', function (Blueprint $table): void {
            $table->string('status', 32)->default('ACTIVE');
            $table->string('resolution_reference', 128)->nullable();
        });

        Schema::table('institution.academic_years', function (Blueprint $table): void {
            $table->string('status', 32)->default('DRAFT');
            $table->string('senate_resolution_reference', 128)->nullable();
            $table->timestampTz('senate_approved_at')->nullable();
            $table->timestampTz('published_at')->nullable();
        });

        Schema::create('institution.study_modes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->unique(['institution_id', 'code']);
        });

        Schema::table('institution.terms', function (Blueprint $table): void {
            $table->string('study_mode_code', 32)->default('FULL_TIME');
            $table->string('term_type', 24)->default('SEMESTER');
            $table->string('status', 32)->default('DRAFT');
            $table->timestampTz('fee_payment_closes_at')->nullable();
            $table->date('exam_starts_on')->nullable();
            $table->date('exam_ends_on')->nullable();
            $table->timestampTz('published_at')->nullable();
        });

        Schema::create('institution.intakes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 100);
            $table->date('opens_on');
            $table->date('closes_on');
            $table->date('reporting_on')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'status']);
        });

        Schema::create('institution.calendar_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('institution.academic_years')->cascadeOnDelete();
            $table->foreignUuid('term_id')->nullable()->constrained('institution.terms')->cascadeOnDelete();
            $table->string('event_type', 32);
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->timestampsTz();
            $table->index(['institution_id', 'starts_at']);
        });

        Schema::create('institution.master_lookups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('type', 48);
            $table->string('code', 48);
            $table->string('name', 160);
            $table->jsonb('metadata')->default('{}');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestampsTz();
            $table->unique(['institution_id', 'type', 'code']);
            $table->index(['institution_id', 'type', 'is_active']);
        });

        DB::statement('CREATE UNIQUE INDEX campuses_one_main_per_institution ON institution.campuses (institution_id) WHERE is_main_campus = true AND deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX academic_years_one_current_per_institution ON institution.academic_years (institution_id) WHERE is_current = true');
        DB::statement('CREATE UNIQUE INDEX terms_one_current_per_study_mode ON institution.terms (institution_id, study_mode_code) WHERE is_current = true');
        DB::statement('ALTER TABLE institution.academic_years ADD CONSTRAINT academic_year_dates_valid CHECK (starts_on < ends_on)');
        DB::statement('ALTER TABLE institution.terms ADD CONSTRAINT term_dates_valid CHECK (starts_on < ends_on)');
        DB::statement('ALTER TABLE institution.intakes ADD CONSTRAINT intake_dates_valid CHECK (opens_on < closes_on)');
        DB::statement('ALTER TABLE institution.master_lookups ADD CONSTRAINT lookup_effective_dates_valid CHECK (effective_to IS NULL OR effective_from IS NULL OR effective_from <= effective_to)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS institution.campuses_one_main_per_institution');
        DB::statement('DROP INDEX IF EXISTS institution.academic_years_one_current_per_institution');
        DB::statement('DROP INDEX IF EXISTS institution.terms_one_current_per_study_mode');
        Schema::dropIfExists('institution.master_lookups');
        Schema::dropIfExists('institution.calendar_events');
        Schema::dropIfExists('institution.intakes');

        Schema::table('institution.terms', function (Blueprint $table): void {
            $table->dropColumn(['study_mode_code', 'term_type', 'status', 'fee_payment_closes_at', 'exam_starts_on', 'exam_ends_on', 'published_at']);
        });

        Schema::dropIfExists('institution.study_modes');

        Schema::table('institution.academic_years', function (Blueprint $table): void {
            $table->dropColumn(['status', 'senate_resolution_reference', 'senate_approved_at', 'published_at']);
        });
        Schema::table('institution.departments', fn (Blueprint $table) => $table->dropColumn(['status', 'resolution_reference']));
        Schema::table('institution.faculties', fn (Blueprint $table) => $table->dropColumn(['status', 'resolution_reference']));
        Schema::table('institution.campuses', fn (Blueprint $table) => $table->dropColumn(['is_main_campus', 'status', 'resolution_reference']));
    }
};
