<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LoadBalancerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoadBalancerMiddleware
{
    public function __construct(
        private readonly LoadBalancerService $loadBalancer
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $priority = 'NORMAL';

        if ($request->user()?->isAdmin() || $request->is('examination/marks*') || $request->is('smhr/payroll*')) {
            $priority = 'URGENT';
        } elseif ($request->is('admissions*') || $request->is('fees*') || $request->is('registration*')) {
            $priority = 'HIGH';
        } elseif ($request->is('reports/export*') || $request->is('*/sync*')) {
            $priority = 'BACKGROUND';
        }

        try {
            $routing = $this->loadBalancer->routeRequest($request, $priority);
        } catch (\Throwable) {
            $routing = [
                'algorithm' => 'FIFO',
                'node' => null,
                'queue_latency_ms' => 0.5,
                'priority' => $priority,
            ];
        }

        /** @var Response $response */
        $response = $next($request);

        // Attach system-wide Load Balancer & Ingress telemetry headers
        if (method_exists($response, 'header')) {
            $response->header('X-LoadBalancer-Algorithm', (string) ($routing['algorithm'] ?? 'FIFO'));
            $response->header('X-LoadBalancer-Node', (string) ($routing['node']?->name ?? 'Ingress-Primary'));
            $response->header('X-LoadBalancer-Latency', ($routing['queue_latency_ms'] ?? 1.2).'ms');
            $response->header('X-LoadBalancer-Priority', (string) ($routing['priority'] ?? 'NORMAL'));
        }

        return $response;
    }
}
