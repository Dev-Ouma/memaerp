<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['generation_run', 'school', 'cohort', 'run_date', 'total_qualified', 'status'])]
final class GraduationListBatch extends Model
{
    protected $table = 'graduation_list_batches';
}
