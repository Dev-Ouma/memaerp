<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class LoadBalancerNode extends Model
{
    use HasUuids;

    protected $table = 'load_balancer_nodes';

    protected $fillable = [
        'name',
        'host',
        'port',
        'weight',
        'role',
        'status',
        'active_connections',
        'cpu_usage',
        'memory_usage',
        'latency_ms',
        'total_served_requests',
        'is_enabled',
    ];

    protected $casts = [
        'port' => 'integer',
        'weight' => 'integer',
        'active_connections' => 'integer',
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'latency_ms' => 'float',
        'total_served_requests' => 'integer',
        'is_enabled' => 'boolean',
    ];
}
