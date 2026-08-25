<?php

declare(strict_types=1);

use App\Modules\Admission\Setups\SetupCatalogue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_setup_definitions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('institution_id')->nullable()->index();
            $table->string('setup_key', 120)->unique();
            $table->string('category', 100)->index();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->string('consumer', 190);
            $table->text('missing_behaviour');
            $table->jsonb('validation_schema')->default('{}');
            $table->boolean('supports_import')->default(false);
            $table->boolean('supports_preview')->default(true);
            $table->timestampsTz();
        });
        Schema::create('admin_setup_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_setup_definition_id')->constrained('admin_setup_definitions')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->jsonb('configuration');
            $table->date('effective_from')->nullable()->index();
            $table->date('effective_to')->nullable();
            $table->string('checksum', 64);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('archived_at')->nullable();
            $table->string('change_reason', 500);
            $table->timestampsTz();
            $table->unique(['admin_setup_definition_id', 'version']);
            $table->index(['admin_setup_definition_id', 'status', 'effective_from', 'effective_to'], 'setup_version_resolution_idx');
        });
        Schema::create('admin_setup_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('admin_setup_definition_id')->constrained('admin_setup_definitions')->cascadeOnDelete();
            $table->foreignUuid('depends_on_definition_id')->constrained('admin_setup_definitions')->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->unique(['admin_setup_definition_id', 'depends_on_definition_id'], 'setup_dependency_unique');
        });
        Schema::create('admin_setup_usages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_setup_version_id')->constrained('admin_setup_versions')->restrictOnDelete();
            $table->string('consumer_type', 120);
            $table->string('consumer_id', 64);
            $table->string('purpose', 120);
            $table->timestampTz('used_at')->useCurrent();
            $table->uuid('correlation_id')->nullable();
            $table->unique(['admin_setup_version_id', 'consumer_type', 'consumer_id', 'purpose'], 'setup_usage_unique');
            $table->index(['consumer_type', 'consumer_id']);
        });

        $now = now();
        $definitionIds = [];
        foreach (SetupCatalogue::definitions() as $key => $definition) {
            $definitionIds[$key] = (string) Str::uuid();
            DB::table('admin_setup_definitions')->insert([
                'id' => $definitionIds[$key], 'setup_key' => $key, 'category' => $definition['category'],
                'name' => $definition['name'], 'consumer' => $definition['consumer'],
                'missing_behaviour' => $definition['missing'], 'validation_schema' => '{}',
                'supports_import' => true, 'supports_preview' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach ([
            'payment.application_fee' => ['amount' => 1000, 'currency' => 'KES', 'refundable' => false],
            'payment.channels_providers' => ['channels' => ['MPESA_STK', 'MPESA_C2B', 'CARD', 'BANK', 'CASHIER']],
        ] as $key => $configuration) {
            DB::table('admin_setup_versions')->insert([
                'id' => (string) Str::uuid(), 'admin_setup_definition_id' => $definitionIds[$key], 'version' => 1,
                'status' => 'ACTIVE', 'configuration' => json_encode($configuration, JSON_THROW_ON_ERROR),
                'effective_from' => '2026-01-01', 'checksum' => hash('sha256', json_encode($configuration, JSON_THROW_ON_ERROR)),
                'published_at' => $now, 'change_reason' => 'Initial institutional configuration migrated from the approved admission policy.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_setup_usages');
        Schema::dropIfExists('admin_setup_dependencies');
        Schema::dropIfExists('admin_setup_versions');
        Schema::dropIfExists('admin_setup_definitions');
    }
};
