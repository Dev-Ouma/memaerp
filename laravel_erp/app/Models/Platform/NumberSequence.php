<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $table = 'number_sequences';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'scope_key',
        'pattern',
        'next_value',
        'pad_length',
    ];
}
