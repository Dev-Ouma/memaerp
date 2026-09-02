<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('entity_type', 60)->index();
            $table->string('model_type', 190);
            $table->string('record_id', 64);
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deleted_by_role', 60)->nullable();
            $table->string('reason', 500);
            $table->string('original_location', 255);
            $table->string('owner_type', 120)->nullable();
            $table->string('owner_id', 64)->nullable();
            $table->json('snapshot');
            $table->timestampTz('deleted_at')->index();
            $table->timestampTz('purge_after')->nullable()->index();
            $table->timestampTz('restored_at')->nullable();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('purged_at')->nullable();
            $table->foreignId('purged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('deleted')->index();
            $table->timestampsTz();
            $table->index(['model_type', 'record_id', 'status'], 'deletion_record_lookup');
        });

        Schema::create('deletion_action_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('deletion_record_id')->constrained('deletion_records')->cascadeOnDelete();
            $table->string('action', 30);
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('reason', 500);
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('decided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            $table->timestampsTz();
            $table->index(['deletion_record_id', 'action', 'status'], 'deletion_action_lookup');
        });

        if (! DB::table('retention_rules')->where('code', 'CURRICULUM-MASTER-DATA')->exists()) {
            DB::table('retention_rules')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'CURRICULUM-MASTER-DATA',
                'subject_type' => 'curriculum_master_data',
                'description' => 'Recoverable curriculum and academic setup master data.',
                'retention_months' => 1,
                'disposal_action' => 'PURGE',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_action_requests');
        Schema::dropIfExists('deletion_records');
        DB::table('retention_rules')->where('code', 'CURRICULUM-MASTER-DATA')->delete();
    }
};
