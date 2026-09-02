<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class SystemVersion extends Model
{
    use HasUuids;

    protected $table = 'system_versions';

    protected $fillable = [
        'version',
        'type',
        'changelog',
        'installed_at',
        'rolled_back_at',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];
}
