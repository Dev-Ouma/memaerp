<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All known ERP module keys — seeded on first run with ACTIVE status.
     */
    private const MODULE_KEYS = [
        'smhr',
        'transfers',
        'pg-research',
        'curriculum',
        'student-affairs',
        'imprest',
        'cohort',
        'registration',
        'lms',
        'examination',
        'fees',
        'graduation',
        'task-management',
        'reports',
        'service-providers',
        'budgeting',
    ];

    public function up(): void
    {
        Schema::create('module_states', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->unique(); // e.g. 'registration', 'lms'
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed all modules as ACTIVE on first deploy
        foreach (self::MODULE_KEYS as $key) {
            DB::table('module_states')->insert([
                'module_key' => $key,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('module_states');
    }
};
