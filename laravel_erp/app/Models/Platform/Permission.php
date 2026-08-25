<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'code',
        'module',
        'resource',
        'action',
        'description',
        'classification',
        'is_segregated',
    ];

    protected function casts(): array
    {
        return [
            'is_segregated' => 'boolean',
        ];
    }
}
