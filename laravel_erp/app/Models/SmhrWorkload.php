<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_id', 'name', 'dept', 'units', 'teaching_hours', 'supervision_hours', 'admin_hours', 'total_hours', 'status'])]
final class SmhrWorkload extends Model
{
    protected $table = 'smhr_workloads';
}
