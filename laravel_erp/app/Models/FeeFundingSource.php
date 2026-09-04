<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'name', 'description', 'allocation_rule', 'candidates_count', 'status',
])]
final class FeeFundingSource extends Model
{
    protected function casts(): array
    {
        return ['candidates_count' => 'integer'];
    }
}
