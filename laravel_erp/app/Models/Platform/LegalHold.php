<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LegalHold extends Model
{
    use HasUuids;

    protected $table = 'legal_holds';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'subject_type',
        'subject_id',
        'reason',
        'placed_by',
        'placed_at',
        'released_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }
}
