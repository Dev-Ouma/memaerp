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
        Schema::table('retention_rules', function (Blueprint $table): void {
            $table->dropUnique('retention_rules_code_unique');
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->date('effective_from')->nullable()->index();
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_reason', 500)->nullable();
            $table->unique(['code', 'version']);
        });

        DB::table('retention_rules')->whereNull('effective_from')->update(['effective_from' => now()->toDateString()]);

        Schema::table('deletion_records', function (Blueprint $table): void {
            $table->foreignUuid('retention_rule_id')->nullable()->after('purge_after')
                ->constrained('retention_rules')->nullOnDelete();
            $table->index(['retention_rule_id', 'status']);
        });
    }

    public function down(): void
    {
        $duplicates = DB::table('retention_rules')->select('code')->groupBy('code')->havingRaw('count(*) > 1')->exists();
        if ($duplicates) {
            throw new RuntimeException('Cannot rollback retention versioning while multiple versions exist. Archive/export and consolidate them first.');
        }

        Schema::table('deletion_records', function (Blueprint $table): void {
            $table->dropForeign(['retention_rule_id']);
            $table->dropIndex(['retention_rule_id', 'status']);
            $table->dropColumn('retention_rule_id');
        });

        Schema::table('retention_rules', function (Blueprint $table): void {
            $table->dropUnique(['code', 'version']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['version', 'status', 'effective_from', 'effective_to', 'created_by', 'change_reason']);
            $table->unique('code');
        });
    }
};
