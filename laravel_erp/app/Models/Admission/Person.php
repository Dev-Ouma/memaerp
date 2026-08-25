<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'people';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'given_name',
        'middle_name',
        'family_name',
        'preferred_name',
        'previous_names',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'nationality_code',
        'country_of_residence_code',
        'county_code',
        'identity_type',
        'identity_number_encrypted',
        'identity_number_hash',
        'identity_number_masked',
        'created_by',
        'updated_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'previous_names' => 'array',
            'date_of_birth' => 'date',
            'deleted_at' => 'datetime',
        ];
    }
}
