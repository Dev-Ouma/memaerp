<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kuccps_index', 'student_name', 'placed_programme', 'gender', 'county', 'cluster_points', 'mema_reg_no', 'reporting_status', 'student_id'])]
final class KuccpsPlacement extends Model
{
    protected function casts(): array
    {
        return ['cluster_points' => 'decimal:2'];
    }
}
