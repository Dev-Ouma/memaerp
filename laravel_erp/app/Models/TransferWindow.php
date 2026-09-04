<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'academic_year', 'notification_date', 'start_date', 'end_date', 'status',
])]
final class TransferWindow extends Model
{
    protected function casts(): array
    {
        return [
            'notification_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
