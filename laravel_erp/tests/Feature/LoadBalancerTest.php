<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadBalancerNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoadBalancerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_load_balancer_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.setups.load-balancer'));
        $response->assertOk();
        $response->assertSee('System Load Balancer &amp; Traffic Scheduler', false);
        $response->assertSee('FIFO (First-In, First-Out)');
        $response->assertSee('LIFO (Last-In, First-Out)');
        $response->assertSee('Weighted Round Robin (WRR)');
        $response->assertSee('Least Connections (LC)');
        $response->assertSee('Priority Fair Queuing (PFQ)');
    }

    public function test_switch_algorithm_to_lifo_and_fifo(): void
    {
        // Switch to LIFO
        $response = $this->actingAs($this->admin)->post(route('admin.setups.load-balancer.strategy'), [
            'active_algorithm' => 'LIFO',
        ]);
        $response->assertRedirect(route('admin.setups.load-balancer'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('load_balancer_configs', [
            'active_algorithm' => 'LIFO',
        ]);

        // Switch to FIFO
        $responseFifo = $this->actingAs($this->admin)->post(route('admin.setups.load-balancer.strategy'), [
            'active_algorithm' => 'FIFO',
        ]);
        $responseFifo->assertRedirect(route('admin.setups.load-balancer'));
        $this->assertDatabaseHas('load_balancer_configs', [
            'active_algorithm' => 'FIFO',
        ]);
    }

    public function test_update_load_balancer_configuration(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setups.load-balancer.config'), [
            'max_concurrency_per_node' => 300,
            'queue_timeout_seconds' => 45,
            'circuit_breaker_enabled' => '1',
            'failure_threshold' => 8,
            'recovery_timeout_seconds' => 20,
            'rate_limit_rpm' => 2400,
            'fallback_action' => 'QUEUE_WITH_BACKPRESSURE',
        ]);

        $response->assertRedirect(route('admin.setups.load-balancer'));
        $this->assertDatabaseHas('load_balancer_configs', [
            'max_concurrency_per_node' => 300,
            'queue_timeout_seconds' => 45,
            'circuit_breaker_enabled' => true,
            'rate_limit_rpm' => 2400,
        ]);
    }

    public function test_register_and_drain_cluster_node(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setups.load-balancer.store-node'), [
            'name' => 'Web-Node-04 (High Capacity)',
            'host' => '10.0.1.14',
            'port' => 8000,
            'weight' => 150,
            'role' => 'APPLICATION',
        ]);

        $response->assertRedirect(route('admin.setups.load-balancer'));
        $this->assertDatabaseHas('load_balancer_nodes', [
            'name' => 'Web-Node-04 (High Capacity)',
            'host' => '10.0.1.14',
        ]);
        $this->get(route('admin.setups.load-balancer'))
            ->assertOk()
            ->assertSee('Web-Node-04 (High Capacity)')
            ->assertSee('10.0.1.14');

        $node = LoadBalancerNode::where('name', 'Web-Node-04 (High Capacity)')->firstOrFail();

        // Test draining node
        $drainResponse = $this->actingAs($this->admin)->post(route('admin.setups.load-balancer.toggle-node', $node->id), [
            'action' => 'drain',
        ]);
        $drainResponse->assertRedirect(route('admin.setups.load-balancer'));
        $this->assertEquals('DRAINING', $node->fresh()->status);
    }

    public function test_traffic_simulation_benchmark_endpoint(): void
    {
        // Test FIFO Simulation
        $fifoResponse = $this->actingAs($this->admin)->postJson(route('admin.setups.load-balancer.simulate'), [
            'algorithm' => 'FIFO',
            'request_count' => 30,
        ]);
        $fifoResponse->assertOk();
        $fifoResponse->assertJsonPath('success', true);
        $fifoResponse->assertJsonPath('algorithm', 'FIFO');
        $this->assertCount(20, $fifoResponse->json('execution_sample'));

        // Test LIFO Simulation
        $lifoResponse = $this->actingAs($this->admin)->postJson(route('admin.setups.load-balancer.simulate'), [
            'algorithm' => 'LIFO',
            'request_count' => 30,
        ]);
        $lifoResponse->assertOk();
        $lifoResponse->assertJsonPath('success', true);
        $lifoResponse->assertJsonPath('algorithm', 'LIFO');
    }

    public function test_cluster_health_check_endpoint(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.setups.load-balancer.health-check'));
        $response->assertOk();
        $response->assertJsonPath('success', true);
    }

    public function test_global_middleware_attaches_load_balancer_headers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $this->assertTrue($response->headers->has('X-LoadBalancer-Algorithm'));
        $this->assertTrue($response->headers->has('X-LoadBalancer-Node'));
        $this->assertTrue($response->headers->has('X-LoadBalancer-Latency'));
    }
}
