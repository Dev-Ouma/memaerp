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
        Schema::table('course.course_prerequisites', function (Blueprint $table): void {
            $table->foreignUuid('institution_id')->nullable()->constrained('institution.institutions')->cascadeOnDelete();
        });
        DB::statement('UPDATE course.course_prerequisites r SET institution_id = c.institution_id FROM course.courses c WHERE c.id = r.course_id');
        DB::statement('ALTER TABLE course.course_prerequisites ALTER COLUMN institution_id SET NOT NULL');
        Schema::table('course.course_prerequisites', function (Blueprint $table): void {
            $table->index(['institution_id', 'curriculum_version_id'], 'course_requirements_tenant_version_index');
        });
    }

    public function down(): void
    {
        Schema::table('course.course_prerequisites', function (Blueprint $table): void {
            $table->dropIndex('course_requirements_tenant_version_index');
            $table->dropConstrainedForeignId('institution_id');
        });
    }
};
