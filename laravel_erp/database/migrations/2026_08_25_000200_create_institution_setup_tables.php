<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Institution, academic-structure and catalogue master data.
 *
 * Purpose: the ERP is the system of record for programme offerings. This migration introduces the
 * institution → campus → faculty → department → programme → offering spine plus the Kenyan county and
 * classification masters, then backfills the legacy `courses` / `programme_offerings` rows onto it so
 * there is exactly one source of truth.
 *
 * Additive only: `courses`, `admission_intakes` and `programme_offerings` gain columns; nothing is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 20)->unique();
            $t->string('name', 190);
            $t->string('short_name', 60);
            $t->string('registration_number', 60)->nullable();
            $t->string('logo_path', 255)->nullable();
            $t->string('website', 190)->nullable();
            $t->string('support_email', 190)->nullable();
            $t->string('support_phone', 32)->nullable();
            $t->string('timezone', 60)->default('Africa/Nairobi');
            $t->string('default_currency', 3)->default('KES');
            $t->string('country_code', 2)->default('KE');
            $t->jsonb('policy_versions')->default('{}');
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->softDeletesTz();
        });

        Schema::create('campuses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            $t->string('code', 30);
            $t->string('name', 190);
            $t->string('town', 120)->nullable();
            $t->string('county_code', 10)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['institution_id', 'code']);
        });

        Schema::create('faculties', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            $t->string('code', 30);
            $t->string('name', 190);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['institution_id', 'code']);
        });

        Schema::create('departments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('faculty_id')->constrained('faculties')->restrictOnDelete();
            $t->string('code', 30);
            $t->string('name', 190);
            $t->foreignId('head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['faculty_id', 'code']);
        });

        Schema::create('qualification_levels', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 30)->unique();
            $t->string('name', 120);
            // KNQF level — orders the catalogue and drives minimum-entry rules.
            $t->unsignedSmallInteger('rank');
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        Schema::create('study_modes', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 30)->unique();
            $t->string('name', 120);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        Schema::create('study_areas', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('code', 30)->unique();
            $t->string('name', 120);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        Schema::create('counties', function (Blueprint $t): void {
            $t->string('code', 10)->primary();
            $t->string('name', 80)->unique();
            $t->string('region', 60)->nullable();
            $t->boolean('is_active')->default(true);
        });

        Schema::create('programmes', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            $t->foreignUuid('department_id')->constrained('departments')->restrictOnDelete();
            $t->foreignUuid('qualification_level_id')->constrained('qualification_levels')->restrictOnDelete();
            $t->foreignUuid('study_area_id')->nullable()->constrained('study_areas')->nullOnDelete();
            // Legacy bridge: the pre-existing `courses` row this programme was derived from.
            $t->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $t->string('code', 40);
            $t->string('name', 190);
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('duration_months')->default(48);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['institution_id', 'code']);
        });

        Schema::table('courses', function (Blueprint $t): void {
            $t->uuid('programme_id')->nullable()->index();
        });

        Schema::table('admission_intakes', function (Blueprint $t): void {
            $t->uuid('institution_id')->nullable()->index();
            // "September 2026" — the authoritative cohort label used by admission letters.
            $t->string('cohort_label', 80)->nullable();
            $t->string('academic_year', 20)->nullable();
            $t->date('reporting_date')->nullable();
            $t->date('starts_on')->nullable();
            // Numbering fragment: MC/APL/{intake_token}/000001
            $t->string('numbering_token', 20)->nullable();
            $t->string('timezone', 60)->default('Africa/Nairobi');
            $t->unsignedSmallInteger('sla_review_days')->default(10);
            $t->timestampTz('published_at')->nullable();
            $t->softDeletesTz();
        });

        Schema::table('programme_offerings', function (Blueprint $t): void {
            $t->uuid('programme_id')->nullable()->index();
            $t->uuid('campus_id')->nullable()->index();
            $t->uuid('study_mode_id')->nullable()->index();
            $t->string('slug', 190)->nullable()->unique();
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('duration_months')->nullable();
            $t->unsignedInteger('reserved_seats')->default(0);
            $t->unsignedInteger('confirmed_seats')->default(0);
            $t->jsonb('eligibility_rules')->default('[]');
            $t->jsonb('required_documents')->default('[]');
            $t->string('application_form_code', 60)->nullable();
            $t->timestampTz('published_at')->nullable();
            $t->date('effective_from')->nullable();
            $t->date('effective_to')->nullable();
            $t->unsignedInteger('catalogue_version')->default(1);
            $t->softDeletesTz();
        });

        $this->backfill();
    }

    /**
     * Backfill: one institution, one campus per distinct legacy campus string, a default
     * faculty/department, and one programme per legacy course. Reconciliation queries are documented in
     * docs/admission/MIGRATION-PLAN.md §4.
     */
    private function backfill(): void
    {
        $now = now();
        $institutionId = (string) Str::uuid();

        DB::table('institutions')->insert([
            'id' => $institutionId,
            'code' => 'MEMA',
            'name' => 'Mema College',
            'short_name' => 'Mema',
            'support_email' => 'admissions@mema.ac.ke',
            'timezone' => 'Africa/Nairobi',
            'default_currency' => 'KES',
            'country_code' => 'KE',
            'policy_versions' => json_encode(['terms' => '2026.1', 'privacy' => '2026.1', 'cookie' => '2026.1']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['KNQF4', 'Certificate', 4], ['KNQF5', 'Diploma', 5], ['KNQF6', 'Higher Diploma', 6],
            ['KNQF7', 'Bachelor Degree', 7], ['KNQF8', 'Postgraduate Diploma', 8], ['KNQF9', 'Masters Degree', 9],
        ] as [$code, $name, $rank]) {
            DB::table('qualification_levels')->insert([
                'id' => (string) Str::uuid(), 'code' => $code, 'name' => $name, 'rank' => $rank,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach ([
            ['FULL_TIME', 'Full-time'], ['PART_TIME', 'Part-time'], ['EVENING', 'Evening'],
            ['WEEKEND', 'Weekend'], ['DISTANCE', 'Distance learning'], ['BLENDED', 'Blended'],
        ] as [$code, $name]) {
            DB::table('study_modes')->insert([
                'id' => (string) Str::uuid(), 'code' => $code, 'name' => $name,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach ([
            ['ICT', 'Information & Communication Technology'], ['BUS', 'Business & Management'],
            ['HEALTH', 'Health Sciences'], ['EDU', 'Education'], ['ENG', 'Engineering & Technology'],
            ['SSC', 'Social Sciences'], ['AGRI', 'Agriculture & Environment'], ['HOSP', 'Hospitality & Tourism'],
        ] as [$code, $name]) {
            DB::table('study_areas')->insert([
                'id' => (string) Str::uuid(), 'code' => $code, 'name' => $name,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (self::KENYAN_COUNTIES as $code => $name) {
            DB::table('counties')->insert(['code' => $code, 'name' => $name]);
        }

        $facultyId = (string) Str::uuid();
        DB::table('faculties')->insert([
            'id' => $facultyId, 'institution_id' => $institutionId, 'code' => 'GEN',
            'name' => 'General Studies', 'created_at' => $now, 'updated_at' => $now,
        ]);

        $departmentId = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $departmentId, 'faculty_id' => $facultyId, 'code' => 'GEN',
            'name' => 'General Studies', 'created_at' => $now, 'updated_at' => $now,
        ]);

        $campusNames = DB::table('programme_offerings')->distinct()->pluck('campus')->filter()->all();
        if ($campusNames === []) {
            $campusNames = ['Main Campus'];
        }
        $campusIds = [];
        foreach ($campusNames as $index => $name) {
            $campusIds[$name] = (string) Str::uuid();
            DB::table('campuses')->insert([
                'id' => $campusIds[$name], 'institution_id' => $institutionId,
                'code' => Str::upper(Str::slug((string) $name, '_')) ?: 'CAMPUS_'.$index,
                'name' => $name, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $defaultLevelId = DB::table('qualification_levels')->where('code', 'KNQF5')->value('id');
        $fullTimeId = DB::table('study_modes')->where('code', 'FULL_TIME')->value('id');

        foreach (DB::table('courses')->get() as $course) {
            $programmeId = (string) Str::uuid();
            DB::table('programmes')->insert([
                'id' => $programmeId,
                'institution_id' => $institutionId,
                'department_id' => $departmentId,
                'qualification_level_id' => $defaultLevelId,
                'course_id' => $course->id,
                'code' => $course->code ?: 'PRG'.str_pad((string) $course->id, 4, '0', STR_PAD_LEFT),
                'name' => $course->name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('courses')->where('id', $course->id)->update(['programme_id' => $programmeId]);
            DB::table('programme_offerings')->where('course_id', $course->id)->update(['programme_id' => $programmeId]);
        }

        foreach (DB::table('programme_offerings')->get() as $offering) {
            DB::table('programme_offerings')->where('id', $offering->id)->update([
                'campus_id' => $campusIds[$offering->campus] ?? reset($campusIds),
                'study_mode_id' => DB::table('study_modes')->where('name', $offering->study_mode)->value('id') ?? $fullTimeId,
                'slug' => Str::slug($offering->id.'-'.($offering->campus ?? '').'-'.($offering->study_mode ?? '')),
                'published_at' => $offering->is_published ? $now : null,
                'effective_from' => $now->toDateString(),
            ]);
        }

        DB::table('admission_intakes')->update(['institution_id' => $institutionId]);
        foreach (DB::table('admission_intakes')->get() as $intake) {
            DB::table('admission_intakes')->where('id', $intake->id)->update([
                'cohort_label' => $intake->name,
                'academic_year' => substr((string) $intake->opens_at, 0, 4),
                'numbering_token' => Str::upper(Str::slug((string) $intake->code, '')),
                'starts_on' => $intake->opens_at,
                'published_at' => $intake->is_published ? $now : null,
            ]);
        }
    }

    private const KENYAN_COUNTIES = [
        '001' => 'Mombasa', '002' => 'Kwale', '003' => 'Kilifi', '004' => 'Tana River', '005' => 'Lamu',
        '006' => 'Taita Taveta', '007' => 'Garissa', '008' => 'Wajir', '009' => 'Mandera', '010' => 'Marsabit',
        '011' => 'Isiolo', '012' => 'Meru', '013' => 'Tharaka Nithi', '014' => 'Embu', '015' => 'Kitui',
        '016' => 'Machakos', '017' => 'Makueni', '018' => 'Nyandarua', '019' => 'Nyeri', '020' => 'Kirinyaga',
        '021' => "Murang'a", '022' => 'Kiambu', '023' => 'Turkana', '024' => 'West Pokot', '025' => 'Samburu',
        '026' => 'Trans Nzoia', '027' => 'Uasin Gishu', '028' => 'Elgeyo Marakwet', '029' => 'Nandi',
        '030' => 'Baringo', '031' => 'Laikipia', '032' => 'Nakuru', '033' => 'Narok', '034' => 'Kajiado',
        '035' => 'Kericho', '036' => 'Bomet', '037' => 'Kakamega', '038' => 'Vihiga', '039' => 'Bungoma',
        '040' => 'Busia', '041' => 'Siaya', '042' => 'Kisumu', '043' => 'Homa Bay', '044' => 'Migori',
        '045' => 'Kisii', '046' => 'Nyamira', '047' => 'Nairobi City',
    ];

    public function down(): void
    {
        Schema::table('programme_offerings', function (Blueprint $t): void {
            $t->dropColumn([
                'programme_id', 'campus_id', 'study_mode_id', 'slug', 'description', 'duration_months',
                'reserved_seats', 'confirmed_seats', 'eligibility_rules', 'required_documents',
                'application_form_code', 'published_at', 'effective_from', 'effective_to',
                'catalogue_version', 'deleted_at',
            ]);
        });
        Schema::table('admission_intakes', function (Blueprint $t): void {
            $t->dropColumn([
                'institution_id', 'cohort_label', 'academic_year', 'reporting_date', 'starts_on',
                'numbering_token', 'timezone', 'sla_review_days', 'published_at', 'deleted_at',
            ]);
        });
        Schema::table('courses', function (Blueprint $t): void {
            $t->dropColumn('programme_id');
        });
        foreach ([
            'programmes', 'counties', 'study_areas', 'study_modes', 'qualification_levels',
            'departments', 'faculties', 'campuses', 'institutions',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
