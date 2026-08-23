<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE course.course_prerequisites DROP CONSTRAINT course_req_unique');
        DB::statement('CREATE UNIQUE INDEX course_req_global_unique ON course.course_prerequisites (course_id, prerequisite_course_id, requirement_type) WHERE curriculum_version_id IS NULL');
        DB::statement('CREATE UNIQUE INDEX course_req_version_unique ON course.course_prerequisites (curriculum_version_id, course_id, prerequisite_course_id, requirement_type) WHERE curriculum_version_id IS NOT NULL');
        DB::statement('CREATE TRIGGER course_requirements_immutable BEFORE INSERT OR UPDATE OR DELETE ON course.course_prerequisites FOR EACH ROW EXECUTE FUNCTION curriculum.guard_locked_child()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS course_requirements_immutable ON course.course_prerequisites');
        DB::statement('DROP INDEX IF EXISTS course.course_req_version_unique');
        DB::statement('DROP INDEX IF EXISTS course.course_req_global_unique');
        DB::statement('ALTER TABLE course.course_prerequisites ADD CONSTRAINT course_req_unique UNIQUE (course_id, prerequisite_course_id, requirement_type)');
    }
};
