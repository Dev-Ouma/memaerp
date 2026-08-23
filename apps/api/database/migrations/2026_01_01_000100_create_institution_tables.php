<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MOD-01-02 — Institutional Administration & Master Data.
 *
 * `institution_id` appears on every table in the system (ADR-012): the platform is single-tenant
 * today but multi-tenant shaped, so becoming SaaS later never requires a table rewrite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution.institutions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('legal_name');
            $table->string('registration_number', 64)->nullable();
            $table->string('domain')->nullable();
            $table->jsonb('branding')->default('{}');
            $table->jsonb('contact')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('institution.campuses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->string('town')->nullable();
            $table->jsonb('address')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('institution.faculties', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('campus_id')->nullable()->constrained('institution.campuses')->nullOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('institution.departments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('faculty_id')->constrained('institution.faculties')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->string('cost_centre', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('institution.academic_years', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('code', 16);
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestampsTz();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('institution.terms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('institution.academic_years')->cascadeOnDelete();
            $table->string('code', 16);
            $table->string('name');
            $table->unsignedSmallInteger('sequence');
            $table->date('starts_on');
            $table->date('ends_on');

            // Windows that gate behaviour elsewhere in the system. Registration cannot open
            // because someone flipped a boolean — it opens because the calendar says so.
            $table->timestampTz('registration_opens_at')->nullable();
            $table->timestampTz('registration_closes_at')->nullable();
            $table->timestampTz('add_drop_closes_at')->nullable();
            $table->timestampTz('marks_entry_opens_at')->nullable();
            $table->timestampTz('marks_entry_closes_at')->nullable();

            $table->boolean('is_current')->default(false);
            $table->timestampsTz();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'is_current']);
        });

        // Effective-dated grading scales. A 2024 transcript must render under the 2024 scale.
        Schema::create('institution.grading_scales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestampsTz();

            $table->unique(['institution_id', 'code', 'effective_from']);
        });

        Schema::create('institution.grade_bands', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('grading_scale_id')->constrained('institution.grading_scales')->cascadeOnDelete();
            $table->string('letter', 4);
            $table->decimal('min_mark', 5, 2);
            $table->decimal('max_mark', 5, 2);
            $table->decimal('grade_point', 4, 2);
            $table->boolean('is_pass')->default(true);
            $table->timestampsTz();

            $table->unique(['grading_scale_id', 'letter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution.grade_bands');
        Schema::dropIfExists('institution.grading_scales');
        Schema::dropIfExists('institution.terms');
        Schema::dropIfExists('institution.academic_years');
        Schema::dropIfExists('institution.departments');
        Schema::dropIfExists('institution.faculties');
        Schema::dropIfExists('institution.campuses');
        Schema::dropIfExists('institution.institutions');
    }
};
