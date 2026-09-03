<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('academic_programmes') && !Schema::hasColumn('academic_programmes', 'image_path')) {
            Schema::table('academic_programmes', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('courses') && !Schema::hasColumn('courses', 'image_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('next_student_serial');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('academic_programmes') && Schema::hasColumn('academic_programmes', 'image_path')) {
            Schema::table('academic_programmes', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'image_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};
