<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_maintenance_configs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->boolean('is_lockdown')->default(false);
            $table->string('lockdown_type', 30)->default('read_only'); // read_only, offline
            $table->text('ip_whitelist')->nullable(); // comma-separated IPs
            $table->string('maintenance_message', 500)->nullable();
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
            $table->jsonb('locked_modules')->nullable(); // locked down module keys
            $table->timestamps();
        });

        Schema::create('system_backups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('filename', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('completed'); // completed, failed
            $table->timestamps();
        });

        Schema::create('system_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('version', 30);
            $table->string('type', 30)->default('patch'); // major, minor, patch
            $table->text('changelog')->nullable();
            $table->dateTime('installed_at')->useCurrent();
            $table->dateTime('rolled_back_at')->nullable();
            $table->timestamps();
        });

        // Insert default maintenance configurations
        DB::table('system_maintenance_configs')->insert([
            'id' => Str::uuid()->toString(),
            'is_lockdown' => false,
            'lockdown_type' => 'read_only',
            'ip_whitelist' => '127.0.0.1,::1',
            'maintenance_message' => 'MEMA ERP is currently undergoing scheduled systems upgrade and maintenance. Please check back shortly.',
            'locked_modules' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert initial system versions history
        DB::table('system_versions')->insert([
            [
                'id' => Str::uuid()->toString(),
                'version' => '1.0.0',
                'type' => 'major',
                'changelog' => 'Initial bootstrap of the MEMA ERP enterprise portal.',
                'installed_at' => now()->subDays(60),
                'created_at' => now()->subDays(60),
                'updated_at' => now()->subDays(60),
            ],
            [
                'id' => Str::uuid()->toString(),
                'version' => '1.1.0',
                'type' => 'minor',
                'changelog' => 'Added academic calendar registration pipeline and student admission roll publishing.',
                'installed_at' => now()->subDays(30),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'id' => Str::uuid()->toString(),
                'version' => '1.2.0',
                'type' => 'minor',
                'changelog' => 'Implemented system-wide soft-delete recovery and enterprise recycle bin.',
                'installed_at' => now()->subDays(1),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_versions');
        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('system_maintenance_configs');
    }
};
