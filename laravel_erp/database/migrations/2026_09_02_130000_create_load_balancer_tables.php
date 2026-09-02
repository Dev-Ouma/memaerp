<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('load_balancer_configs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('active_algorithm')->default('FIFO'); // FIFO, LIFO, WEIGHTED_ROUND_ROBIN, LEAST_CONNECTIONS, PRIORITY_QUEUE
            $table->unsignedInteger('max_concurrency_per_node')->default(250);
            $table->unsignedInteger('queue_timeout_seconds')->default(30);
            $table->boolean('circuit_breaker_enabled')->default(true);
            $table->unsignedInteger('failure_threshold')->default(5);
            $table->unsignedInteger('recovery_timeout_seconds')->default(15);
            $table->unsignedInteger('rate_limit_rpm')->default(1200);
            $table->unsignedInteger('health_check_interval')->default(10);
            $table->string('fallback_action')->default('DEGRADE_GRACEFULLY');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('load_balancer_nodes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('host');
            $table->unsignedInteger('port')->default(8000);
            $table->unsignedInteger('weight')->default(100);
            $table->string('role')->default('APPLICATION'); // APPLICATION, BACKGROUND_WORKER, DATABASE_READ_REPLICA
            $table->string('status')->default('HEALTHY'); // HEALTHY, DEGRADED, DRAINING, OFFLINE
            $table->unsignedInteger('active_connections')->default(0);
            $table->decimal('cpu_usage', 5, 2)->default(15.00);
            $table->decimal('memory_usage', 5, 2)->default(32.00);
            $table->decimal('latency_ms', 6, 2)->default(1.20);
            $table->unsignedBigInteger('total_served_requests')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Seed default Load Balancer Configuration
        DB::table('load_balancer_configs')->insert([
            'id' => (string) Str::uuid(),
            'active_algorithm' => 'FIFO',
            'max_concurrency_per_node' => 250,
            'queue_timeout_seconds' => 30,
            'circuit_breaker_enabled' => true,
            'failure_threshold' => 5,
            'recovery_timeout_seconds' => 15,
            'rate_limit_rpm' => 1200,
            'health_check_interval' => 10,
            'fallback_action' => 'DEGRADE_GRACEFULLY',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Default Cluster Nodes
        $nodes = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Web-Node-01 (Primary Ingress)',
                'host' => '10.0.1.10',
                'port' => 8000,
                'weight' => 100,
                'role' => 'APPLICATION',
                'status' => 'HEALTHY',
                'active_connections' => 28,
                'cpu_usage' => 18.50,
                'memory_usage' => 34.20,
                'latency_ms' => 1.15,
                'total_served_requests' => 148290,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Web-Node-02 (Secondary Cluster)',
                'host' => '10.0.1.11',
                'port' => 8000,
                'weight' => 100,
                'role' => 'APPLICATION',
                'status' => 'HEALTHY',
                'active_connections' => 22,
                'cpu_usage' => 16.10,
                'memory_usage' => 31.80,
                'latency_ms' => 1.25,
                'total_served_requests' => 139104,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Batch-Worker-01 (Async & Sync Jobs)',
                'host' => '10.0.1.20',
                'port' => 9000,
                'weight' => 80,
                'role' => 'BACKGROUND_WORKER',
                'status' => 'HEALTHY',
                'active_connections' => 12,
                'cpu_usage' => 24.80,
                'memory_usage' => 45.10,
                'latency_ms' => 2.40,
                'total_served_requests' => 89420,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'DB-Replica-RO1 (Read-Only Pool)',
                'host' => '10.0.1.30',
                'port' => 5432,
                'weight' => 120,
                'role' => 'DATABASE_READ_REPLICA',
                'status' => 'HEALTHY',
                'active_connections' => 45,
                'cpu_usage' => 29.40,
                'memory_usage' => 52.00,
                'latency_ms' => 0.85,
                'total_served_requests' => 412090,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('load_balancer_nodes')->insert($nodes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_balancer_nodes');
        Schema::dropIfExists('load_balancer_configs');
    }
};
