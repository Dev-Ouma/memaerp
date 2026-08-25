<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasUuids;

    protected $table = 'addresses';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'person_id',
        'address_type',
        'line1',
        'line2',
        'town',
        'county_code',
        'postal_code',
        'country_code',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
