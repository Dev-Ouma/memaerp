<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'institutions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'code',
        'name',
        'short_name',
        'registration_number',
        'logo_path',
        'website',
        'support_email',
        'support_phone',
        'timezone',
        'default_currency',
        'country_code',
        'policy_versions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'policy_versions' => 'array',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }
}
