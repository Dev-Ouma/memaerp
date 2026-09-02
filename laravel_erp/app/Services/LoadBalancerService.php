<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LoadBalancerConfig;
use App\Models\LoadBalancerNode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class LoadBalancerService
{
    /**
     * Get or create active Load Balancer configuration
     */
    public function getConfig(): LoadBalancerConfig
    {
        $config = LoadBalancerConfig::first();

        if (! $config) {
            $config = LoadBalancerConfig::create([
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
            ]);
        }

        return $config;
    }

    /**
     * Update active Load Balancer configuration
     */
    public function updateConfig(array $attributes): LoadBalancerConfig
    {
        $config = $this->getConfig();
        $config->update($attributes);
        Cache::forget('lb_active_config');

        return $config;
    }

    /**
     * Get cluster nodes
     *
     * @return Collection<int, LoadBalancerNode>
     */
    public function getNodes(): Collection
    {
        return LoadBalancerNode::orderBy('role')->orderBy('name')->get();
    }

    /**
     * Route incoming request to optimal backend node using active algorithm
     */
    public function routeRequest(Request $request, string $priority = 'NORMAL'): array
    {
        $config = $this->getConfig();
        $algorithm = $config->active_algorithm;
        $healthyNodes = LoadBalancerNode::where('is_enabled', true)
            ->where('status', '!=', 'OFFLINE')
            ->where('status', '!=', 'DRAINING')
            ->get();

        if ($healthyNodes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No healthy cluster nodes available.',
                'node' => null,
                'algorithm' => $algorithm,
                'latency_ms' => 0.0,
            ];
        }

        $selectedNode = match ($algorithm) {
            'LIFO' => $this->selectNodeLifo($healthyNodes),
            'WEIGHTED_ROUND_ROBIN' => $this->selectNodeWeightedRoundRobin($healthyNodes),
            'LEAST_CONNECTIONS' => $this->selectNodeLeastConnections($healthyNodes),
            'PRIORITY_QUEUE' => $this->selectNodePriorityQueue($healthyNodes, $priority),
            default => $this->selectNodeFifo($healthyNodes),
        };

        // Increment total served and active connections (simulated or real)
        $selectedNode->increment('total_served_requests');

        return [
            'success' => true,
            'node' => $selectedNode,
            'algorithm' => $algorithm,
            'priority' => $priority,
            'queue_latency_ms' => round($selectedNode->latency_ms + (rand(5, 20) / 100), 2),
            'node_active_connections' => $selectedNode->active_connections,
        ];
    }

    /**
     * 1. FIFO (First-In, First-Out) - Fair sequential queue round-robin
     */
    private function selectNodeFifo(Collection $nodes): LoadBalancerNode
    {
        $index = Cache::get('lb_fifo_index', 0);
        $selected = $nodes->get($index % $nodes->count());
        Cache::put('lb_fifo_index', ($index + 1) % $nodes->count(), 3600);

        return $selected;
    }

    /**
     * 2. LIFO (Last-In, First-Out) - Stack-based scheduler prioritizing freshest real-time traffic
     */
    private function selectNodeLifo(Collection $nodes): LoadBalancerNode
    {
        // LIFO assigns incoming bursts to the most recently active / warm node with immediate stack availability
        $sorted = $nodes->sortByDesc('updated_at')->values();

        return $sorted->first() ?? $nodes->first();
    }

    /**
     * 3. Weighted Round Robin (WRR)
     */
    private function selectNodeWeightedRoundRobin(Collection $nodes): LoadBalancerNode
    {
        $totalWeight = $nodes->sum('weight');
        $randomWeight = rand(1, max(1, $totalWeight));
        $current = 0;

        foreach ($nodes as $node) {
            $current += $node->weight;
            if ($current >= $randomWeight) {
                return $node;
            }
        }

        return $nodes->first();
    }

    /**
     * 4. Least Connections
     */
    private function selectNodeLeastConnections(Collection $nodes): LoadBalancerNode
    {
        // Pick node with lowest active connections and lowest CPU load
        return $nodes->sortBy('active_connections')->first() ?? $nodes->first();
    }

    /**
     * 5. Priority Fair Queuing (PFQ)
     */
    private function selectNodePriorityQueue(Collection $nodes, string $priority): LoadBalancerNode
    {
        if (in_array(strtoupper($priority), ['URGENT', 'CRITICAL', 'ADMIN'], true)) {
            // Assign to highest capacity / dedicated primary ingress node
            return $nodes->where('role', 'APPLICATION')->sortByDesc('weight')->first() ?? $nodes->first();
        }

        if (strtoupper($priority) === 'BACKGROUND') {
            // Route background batch jobs to worker nodes if available
            return $nodes->where('role', 'BACKGROUND_WORKER')->first() ?? $nodes->sortBy('active_connections')->first();
        }

        return $this->selectNodeFifo($nodes);
    }

    /**
     * Execute High-Precision Traffic Benchmark Simulation
     */
    public function simulateTraffic(string $algorithm, int $requestCount = 50, array $tierWeights = []): array
    {
        $nodes = LoadBalancerNode::where('is_enabled', true)->get();

        if ($nodes->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No active cluster nodes to simulate.',
            ];
        }

        $algorithm = strtoupper($algorithm);
        $requestQueue = [];
        $nodeDispatchStats = [];

        foreach ($nodes as $node) {
            $nodeDispatchStats[$node->name] = [
                'id' => $node->id,
                'name' => $node->name,
                'role' => $node->role,
                'weight' => $node->weight,
                'dispatched_count' => 0,
                'total_latency_ms' => 0.0,
            ];
        }

        // Generate synthetic request arrival batch with arrival timestamps
        $priorities = ['URGENT', 'HIGH', 'NORMAL', 'NORMAL', 'NORMAL', 'BACKGROUND'];
        for ($i = 1; $i <= $requestCount; $i++) {
            $priority = $priorities[array_rand($priorities)];
            $requestQueue[] = [
                'req_id' => 'REQ-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'arrival_order' => $i,
                'arrival_time_ms' => $i * 1.5,
                'priority' => $priority,
                'path' => $this->getRandomSyntheticEndpoint($priority),
            ];
        }

        // Apply algorithm scheduling order
        $processedOrder = match ($algorithm) {
            'LIFO' => array_reverse($requestQueue), // Stack order
            'PRIORITY_QUEUE' => $this->sortQueueByPriority($requestQueue),
            default => $requestQueue, // FIFO / standard arrival order
        };

        $executionLog = [];
        $wrrIndex = 0;
        $totalWeight = $nodes->sum('weight');

        foreach ($processedOrder as $idx => $req) {
            /** @var LoadBalancerNode $targetNode */
            if ($algorithm === 'WEIGHTED_ROUND_ROBIN') {
                $targetNode = $nodes[$wrrIndex % $nodes->count()];
                $wrrIndex++;
            } elseif ($algorithm === 'LEAST_CONNECTIONS') {
                $targetNode = $nodes->sortBy('active_connections')->first();
            } elseif ($algorithm === 'LIFO') {
                // LIFO routes with priority to highest frequency available slot
                $targetNode = $nodes[$idx % $nodes->count()];
            } elseif ($algorithm === 'PRIORITY_QUEUE') {
                if ($req['priority'] === 'URGENT') {
                    $targetNode = $nodes->where('role', 'APPLICATION')->first() ?? $nodes->first();
                } else {
                    $targetNode = $nodes[$idx % $nodes->count()];
                }
            } else {
                // FIFO
                $targetNode = $nodes[$idx % $nodes->count()];
            }

            $processingTime = round($targetNode->latency_ms + (rand(2, 12) / 10), 2);
            $waitInQueue = round(abs(($idx * 0.8) - ($req['arrival_order'] * 0.5)), 2);

            $executionLog[] = [
                'req_id' => $req['req_id'],
                'arrival_seq' => $req['arrival_order'],
                'execution_seq' => $idx + 1,
                'priority' => $req['priority'],
                'path' => $req['path'],
                'node_name' => $targetNode->name,
                'wait_time_ms' => $waitInQueue,
                'execution_time_ms' => $processingTime,
                'total_turnaround_ms' => round($waitInQueue + $processingTime, 2),
            ];

            $nodeDispatchStats[$targetNode->name]['dispatched_count']++;
            $nodeDispatchStats[$targetNode->name]['total_latency_ms'] += $processingTime;
        }

        // Compute aggregate metrics
        $turnaroundTimes = array_column($executionLog, 'total_turnaround_ms');
        sort($turnaroundTimes);
        $count = count($turnaroundTimes);

        $p50 = $turnaroundTimes[(int) floor($count * 0.50)] ?? 0;
        $p95 = $turnaroundTimes[(int) floor($count * 0.95)] ?? 0;
        $p99 = $turnaroundTimes[(int) floor($count * 0.99)] ?? 0;
        $avgTurnaround = $count > 0 ? round(array_sum($turnaroundTimes) / $count, 2) : 0;

        return [
            'success' => true,
            'algorithm' => $algorithm,
            'total_requests' => $requestCount,
            'avg_latency_ms' => $avgTurnaround,
            'p50_latency_ms' => $p50,
            'p95_latency_ms' => $p95,
            'p99_latency_ms' => $p99,
            'throughput_rps' => round($requestCount / max(0.1, ($p99 / 100)), 1),
            'node_distribution' => array_values($nodeDispatchStats),
            'execution_sample' => array_slice($executionLog, 0, 20),
        ];
    }

    private function sortQueueByPriority(array $queue): array
    {
        $priorityRank = [
            'URGENT' => 1,
            'HIGH' => 2,
            'NORMAL' => 3,
            'BACKGROUND' => 4,
        ];

        usort($queue, function ($a, $b) use ($priorityRank) {
            $rankA = $priorityRank[$a['priority']] ?? 3;
            $rankB = $priorityRank[$b['priority']] ?? 3;

            if ($rankA === $rankB) {
                return $a['arrival_order'] <=> $b['arrival_order'];
            }

            return $rankA <=> $rankB;
        });

        return $queue;
    }

    private function getRandomSyntheticEndpoint(string $priority): string
    {
        $endpoints = [
            'URGENT' => ['/examination/marks-publish', '/smhr/payroll-register/disburse', '/admin/security/audit'],
            'HIGH' => ['/admissions/applications/approve', '/fees/payment-receipt', '/registration/course-registration'],
            'NORMAL' => ['/dashboard', '/curriculum/course-unit', '/smhr/staff-directory', '/lms/course-shells'],
            'BACKGROUND' => ['/reports/advanced-analytics/export-all', '/reports/dynamic-report', '/pg-research/sync'],
        ];

        $pool = $endpoints[$priority] ?? $endpoints['NORMAL'];

        return $pool[array_rand($pool)];
    }
}
