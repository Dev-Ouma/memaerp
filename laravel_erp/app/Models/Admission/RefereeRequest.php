<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefereeRequest extends Model
{
    use HasUuids;

    protected $table = 'referee_requests';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'referee_name',
        'referee_email',
        'referee_phone',
        'referee_organisation',
        'relationship',
        'token_hash',
        'status',
        'requested_at',
        'reminded_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reminded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
