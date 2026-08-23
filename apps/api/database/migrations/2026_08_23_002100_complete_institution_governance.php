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
        Schema::table('institution.campuses', function (Blueprint $table): void {
            $table->uuid('head_of_unit_id')->nullable();
        });
        Schema::table('institution.faculties', function (Blueprint $table): void {
            $table->string('type', 24)->default('FACULTY');
            $table->uuid('head_of_unit_id')->nullable();
        });
        Schema::table('institution.departments', function (Blueprint $table): void {
            $table->uuid('head_of_unit_id')->nullable();
        });

        Schema::create('institution.units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institution.institutions')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('institution.departments')->restrictOnDelete();
            $table->string('code', 32);
            $table->string('name', 200);
            $table->string('type', 32)->default('UNIT');
            $table->uuid('head_of_unit_id')->nullable();
            $table->string('status', 32)->default('DRAFT');
            $table->string('resolution_reference', 128)->nullable();
            $table->boolean('is_active')->default(false);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'department_id', 'is_active']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE institution.faculties ADD CONSTRAINT faculty_type_valid CHECK (type IN ('FACULTY', 'SCHOOL', 'CENTRE'))");
        DB::statement("ALTER TABLE institution.units ADD CONSTRAINT unit_status_valid CHECK (status IN ('DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('institution.units');
        Schema::table('institution.departments', fn (Blueprint $table) => $table->dropColumn('head_of_unit_id'));
        Schema::table('institution.faculties', fn (Blueprint $table) => $table->dropColumn(['type', 'head_of_unit_id']));
        Schema::table('institution.campuses', fn (Blueprint $table) => $table->dropColumn('head_of_unit_id'));
    }
};
