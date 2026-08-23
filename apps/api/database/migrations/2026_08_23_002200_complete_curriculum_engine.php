<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum.programmes', function (Blueprint $table): void {
            $table->string('status', 24)->default('ACTIVE');
            $table->string('qualification_framework_code', 50)->nullable();
            $table->string('accreditation_body', 100)->nullable();
            $table->string('accreditation_reference', 100)->nullable();
            $table->date('accreditation_expires_on')->nullable();
            $table->unsignedSmallInteger('minimum_residency_credits')->default(30);
        });

        Schema::table('curriculum.curriculum_versions', function (Blueprint $table): void {
            $table->string('status', 24)->default('DRAFT');
            $table->unsignedSmallInteger('graduation_credits_required')->default(120);
            $table->unsignedSmallInteger('minimum_elective_credits')->default(0);
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('locked_at')->nullable();
            $table->string('structure_hash', 64)->nullable();
        });
        DB::statement("UPDATE curriculum.curriculum_versions cv SET status = CASE WHEN is_approved THEN 'APPROVED' ELSE 'DRAFT' END, locked_at = CASE WHEN is_approved THEN approved_at ELSE NULL END, graduation_credits_required = p.total_credits_required FROM curriculum.programmes p WHERE p.id = cv.programme_id");

        Schema::create('curriculum.elective_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('curriculum_version_id')->constrained('curriculum.curriculum_versions')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 120);
            $table->unsignedSmallInteger('minimum_courses')->default(1);
            $table->unsignedSmallInteger('minimum_credits')->default(0);
            $table->timestampsTz();
            $table->unique(['curriculum_version_id', 'code']);
        });

        Schema::table('curriculum.curriculum_courses', function (Blueprint $table): void {
            $table->foreignUuid('elective_group_id')->nullable()->constrained('curriculum.elective_groups')->nullOnDelete();
        });

        Schema::table('course.course_prerequisites', function (Blueprint $table): void {
            $table->foreignUuid('curriculum_version_id')->nullable()->constrained('curriculum.curriculum_versions')->cascadeOnDelete();
        });

        Schema::create('curriculum.review_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('curriculum_version_id')->constrained('curriculum.curriculum_versions')->cascadeOnDelete();
            $table->string('stage', 24);
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 24)->default('PENDING');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('iam.users')->nullOnDelete();
            $table->string('reference', 128)->nullable();
            $table->text('comments')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
            $table->unique(['curriculum_version_id', 'stage']);
        });

        Schema::create('curriculum.approval_ledger', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('curriculum_version_id')->constrained('curriculum.curriculum_versions')->restrictOnDelete();
            $table->string('previous_hash', 64)->nullable();
            $table->string('entry_hash', 64)->unique();
            $table->jsonb('payload');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::table('student.students', function (Blueprint $table): void {
            $table->foreignUuid('curriculum_version_id')->nullable()->constrained('curriculum.curriculum_versions')->restrictOnDelete();
            $table->index(['institution_id', 'curriculum_version_id']);
        });

        DB::statement('ALTER TABLE course.course_prerequisites ADD CONSTRAINT prerequisite_not_self CHECK (course_id <> prerequisite_course_id)');
        DB::statement("ALTER TABLE curriculum.curriculum_courses ADD CONSTRAINT curriculum_course_type_valid CHECK (course_type IN ('CORE', 'ELECTIVE', 'REQUIRED_AUDIT'))");
        DB::statement("ALTER TABLE course.course_prerequisites ADD CONSTRAINT course_requirement_type_valid CHECK (requirement_type IN ('PREREQUISITE', 'COREQUISITE', 'ANTIREQUISITE'))");

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION curriculum.guard_locked_version()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status IN ('APPROVED', 'SUPERSEDED') THEN
                    IF TG_OP = 'UPDATE'
                       AND OLD.status = 'APPROVED' AND NEW.status = 'SUPERSEDED'
                       AND NEW.institution_id = OLD.institution_id
                       AND NEW.programme_id = OLD.programme_id
                       AND NEW.effective_year_id = OLD.effective_year_id
                       AND NEW.version_code = OLD.version_code
                       AND NEW.senate_approval_ref IS NOT DISTINCT FROM OLD.senate_approval_ref
                       AND NEW.is_approved = OLD.is_approved
                       AND NEW.approved_at IS NOT DISTINCT FROM OLD.approved_at
                       AND NEW.graduation_credits_required = OLD.graduation_credits_required
                       AND NEW.minimum_elective_credits = OLD.minimum_elective_credits
                       AND NEW.structure_hash IS NOT DISTINCT FROM OLD.structure_hash THEN
                        RETURN NEW;
                    END IF;
                    RAISE EXCEPTION 'ERR-CUR-002: approved curriculum versions are immutable' USING ERRCODE = 'integrity_constraint_violation';
                END IF;
                IF TG_OP = 'DELETE' THEN RETURN OLD; END IF;
                RETURN NEW;
            END;
            $$;

            CREATE TRIGGER curriculum_version_immutable
            BEFORE UPDATE OR DELETE ON curriculum.curriculum_versions
            FOR EACH ROW EXECUTE FUNCTION curriculum.guard_locked_version();

            CREATE OR REPLACE FUNCTION curriculum.guard_locked_child()
            RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE version_id uuid;
            BEGIN
                IF TG_OP = 'DELETE' THEN
                    version_id := OLD.curriculum_version_id;
                ELSE
                    version_id := NEW.curriculum_version_id;
                END IF;
                IF EXISTS (SELECT 1 FROM curriculum.curriculum_versions WHERE id = version_id AND status IN ('APPROVED', 'SUPERSEDED')) THEN
                    RAISE EXCEPTION 'ERR-CUR-002: approved curriculum versions are immutable' USING ERRCODE = 'integrity_constraint_violation';
                END IF;
                IF TG_OP = 'DELETE' THEN RETURN OLD; END IF;
                RETURN NEW;
            END;
            $$;

            CREATE TRIGGER curriculum_courses_immutable
            BEFORE INSERT OR UPDATE OR DELETE ON curriculum.curriculum_courses
            FOR EACH ROW EXECUTE FUNCTION curriculum.guard_locked_child();

            CREATE TRIGGER elective_groups_immutable
            BEFORE INSERT OR UPDATE OR DELETE ON curriculum.elective_groups
            FOR EACH ROW EXECUTE FUNCTION curriculum.guard_locked_child();

            CREATE OR REPLACE FUNCTION curriculum.prevent_approval_ledger_mutation()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'curriculum.approval_ledger is append-only; % is not permitted', TG_OP USING ERRCODE = 'integrity_constraint_violation';
            END;
            $$;
            CREATE TRIGGER approval_ledger_no_update BEFORE UPDATE ON curriculum.approval_ledger FOR EACH ROW EXECUTE FUNCTION curriculum.prevent_approval_ledger_mutation();
            CREATE TRIGGER approval_ledger_no_delete BEFORE DELETE ON curriculum.approval_ledger FOR EACH ROW EXECUTE FUNCTION curriculum.prevent_approval_ledger_mutation();
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS approval_ledger_no_delete ON curriculum.approval_ledger');
        DB::statement('DROP TRIGGER IF EXISTS approval_ledger_no_update ON curriculum.approval_ledger');
        DB::statement('DROP TRIGGER IF EXISTS elective_groups_immutable ON curriculum.elective_groups');
        DB::statement('DROP TRIGGER IF EXISTS curriculum_courses_immutable ON curriculum.curriculum_courses');
        DB::statement('DROP TRIGGER IF EXISTS curriculum_version_immutable ON curriculum.curriculum_versions');
        DB::statement('DROP FUNCTION IF EXISTS curriculum.prevent_approval_ledger_mutation()');
        DB::statement('DROP FUNCTION IF EXISTS curriculum.guard_locked_child()');
        DB::statement('DROP FUNCTION IF EXISTS curriculum.guard_locked_version()');
        Schema::table('student.students', fn (Blueprint $table) => $table->dropConstrainedForeignId('curriculum_version_id'));
        Schema::dropIfExists('curriculum.approval_ledger');
        Schema::dropIfExists('curriculum.review_steps');
        Schema::table('course.course_prerequisites', fn (Blueprint $table) => $table->dropConstrainedForeignId('curriculum_version_id'));
        Schema::table('curriculum.curriculum_courses', fn (Blueprint $table) => $table->dropConstrainedForeignId('elective_group_id'));
        Schema::dropIfExists('curriculum.elective_groups');
        Schema::table('curriculum.curriculum_versions', fn (Blueprint $table) => $table->dropColumn(['status', 'graduation_credits_required', 'minimum_elective_credits', 'submitted_at', 'locked_at', 'structure_hash']));
        Schema::table('curriculum.programmes', fn (Blueprint $table) => $table->dropColumn(['status', 'qualification_framework_code', 'accreditation_body', 'accreditation_reference', 'accreditation_expires_on', 'minimum_residency_credits']));
    }
};
