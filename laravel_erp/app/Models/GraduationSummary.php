<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school', 'degree_count', 'diploma_count', 'masters_count', 'phd_count', 'total', 'status'])]
final class GraduationSummary extends Model
{
    protected $table = 'graduation_summaries';
}
