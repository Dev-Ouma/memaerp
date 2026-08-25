<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QualificationLevel extends Model
{
    use HasUuids;

    protected $table = 'qualification_levels';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'code',
        'name',
        'rank',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
