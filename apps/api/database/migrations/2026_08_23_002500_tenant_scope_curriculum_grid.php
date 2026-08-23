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
        Schema::table('curriculum.curriculum_courses', function (Blueprint $table): void {
            $table->foreignUuid('institution_id')->nullable()->constrained('institution.institutions')->cascadeOnDelete();
        });
        DB::statement('ALTER TABLE curriculum.curriculum_courses DISABLE TRIGGER curriculum_courses_immutable');
        DB::statement('UPDATE curriculum.curriculum_courses cc SET institution_id = cv.institution_id FROM curriculum.curriculum_versions cv WHERE cv.id = cc.curriculum_version_id');
        DB::statement('ALTER TABLE curriculum.curriculum_courses ENABLE TRIGGER curriculum_courses_immutable');
        DB::statement('ALTER TABLE curriculum.curriculum_courses ALTER COLUMN institution_id SET NOT NULL');
        Schema::table('curriculum.curriculum_courses', function (Blueprint $table): void {
            $table->index(['institution_id', 'curriculum_version_id'], 'curriculum_grid_tenant_version_index');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum.curriculum_courses', function (Blueprint $table): void {
            $table->dropIndex('curriculum_grid_tenant_version_index');
            $table->dropConstrainedForeignId('institution_id');
        });
    }
};
