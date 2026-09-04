<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_id', 'staff_name', 'dept', 'category', 'type', 'description', 'action_taken', 'date', 'resolved', 'status'])]
final class SmhrDisciplinaryRecord extends Model
{
    protected $table = 'smhr_disciplinary_records';
}
