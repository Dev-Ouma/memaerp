<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContactPoint extends Model
{
    use HasUuids;

    protected $table = 'contact_points';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'person_id',
        'contact_type',
        'raw_value',
        'normalised_value',
        'country_code',
        'is_primary',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
