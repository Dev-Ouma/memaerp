<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class LoadBalancerConfig extends Model
{
    use HasUuids;

    protected $table = 'load_balancer_configs';

    protected $fillable = [
        'active_algorithm',
        'max_concurrency_per_node',
        'queue_timeout_seconds',
        'circuit_breaker_enabled',
        'failure_threshold',
        'recovery_timeout_seconds',
        'rate_limit_rpm',
        'health_check_interval',
        'fallback_action',
        'is_active',
    ];

    protected $casts = [
        'max_concurrency_per_node' => 'integer',
        'queue_timeout_seconds' => 'integer',
        'circuit_breaker_enabled' => 'boolean',
        'failure_threshold' => 'integer',
        'recovery_timeout_seconds' => 'integer',
        'rate_limit_rpm' => 'integer',
        'health_check_interval' => 'integer',
        'is_active' => 'boolean',
    ];
}
