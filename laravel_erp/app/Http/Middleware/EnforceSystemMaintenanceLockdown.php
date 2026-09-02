<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemMaintenanceConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceSystemMaintenanceLockdown
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $config = SystemMaintenanceConfig::first();
        } catch (\Throwable) {
            return $next($request);
        }

        if ($config) {
            $isScheduledActive = false;
            if ($config->scheduled_start && $config->scheduled_end) {
                $now = now();
                if ($now->greaterThanOrEqualTo($config->scheduled_start) && $now->lessThanOrEqualTo($config->scheduled_end)) {
                    $isScheduledActive = true;
                }
            }

            if ($config->is_lockdown || $isScheduledActive) {
                // Allow admin users to pass through unconditionally
                if ($request->user()?->isAdmin()) {
                    return $next($request);
                }

                // Check IP Whitelist
                $ip = $request->ip();
                $whitelist = array_filter(array_map('trim', explode(',', $config->ip_whitelist ?? '')));
                if (in_array($ip, $whitelist, true)) {
                    return $next($request);
                }

                // Allow essential Auth routes so admins can log in to disable lockdown
                $routeName = $request->route()?->getName();
                if (in_array($routeName, ['login', 'login.store', 'logout'], true)) {
                    return $next($request);
                }

                // Enforce Lockdown Types
                if ($config->lockdown_type === 'offline') {
                    return response()->view('errors.503', [
                        'message' => $config->maintenance_message ?: 'The system is currently offline for scheduled maintenance.',
                    ], 503);
                }

                if ($config->lockdown_type === 'read_only') {
                    // Block modifying requests for normal users
                    if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                        return response()->view('errors.503', [
                            'message' => 'The system is currently in read-only maintenance mode. Form submissions and changes are temporarily disabled.',
                        ], 503);
                    }
                }
            }
        }

        return $next($request);
    }
}
