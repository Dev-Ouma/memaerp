<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['obligation', 'authority', 'frequency', 'amount', 'ref', 'status'])]
final class SmhrStatutorySchedule extends Model
{
    protected $table = 'smhr_statutory_schedules';
}
