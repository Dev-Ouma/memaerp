<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class SystemMaintenanceConfig extends Model
{
    use HasUuids;

    protected $table = 'system_maintenance_configs';

    protected $fillable = [
        'is_lockdown',
        'lockdown_type',
        'ip_whitelist',
        'maintenance_message',
        'scheduled_start',
        'scheduled_end',
        'locked_modules',
        'last_cron_run_at',
        'last_optimize_at',
    ];

    protected $casts = [
        'is_lockdown' => 'boolean',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'locked_modules' => 'array',
        'last_cron_run_at' => 'datetime',
        'last_optimize_at' => 'datetime',
    ];
}
