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
        Schema::table('system_backups', function (Blueprint $table): void {
            $table->string('disk_path', 255)->nullable()->after('filename');
            $table->string('format', 30)->default('logical')->after('status');
            $table->timestamp('restored_at')->nullable()->after('format');
        });

        Schema::table('system_versions', function (Blueprint $table): void {
            $table->boolean('is_current')->default(false)->after('rolled_back_at');
        });

        Schema::table('system_maintenance_configs', function (Blueprint $table): void {
            $table->timestamp('last_cron_run_at')->nullable()->after('locked_modules');
            $table->timestamp('last_optimize_at')->nullable()->after('last_cron_run_at');
        });

        Schema::create('system_broadcasts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('message', 255);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach (['admissions', 'recycle-bin'] as $key) {
            $exists = DB::table('module_states')->where('module_key', $key)->exists();
            if (! $exists) {
                DB::table('module_states')->insert([
                    'module_key' => $key,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $latest = DB::table('system_versions')->orderByDesc('installed_at')->first();
        if ($latest) {
            DB::table('system_versions')->where('id', $latest->id)->update(['is_current' => true]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_broadcasts');

        Schema::table('system_maintenance_configs', function (Blueprint $table): void {
            $table->dropColumn(['last_cron_run_at', 'last_optimize_at']);
        });

        Schema::table('system_versions', function (Blueprint $table): void {
            $table->dropColumn('is_current');
        });

        Schema::table('system_backups', function (Blueprint $table): void {
            $table->dropColumn(['disk_path', 'format', 'restored_at']);
        });

        DB::table('module_states')->whereIn('module_key', ['admissions', 'recycle-bin'])->delete();
    }
};
