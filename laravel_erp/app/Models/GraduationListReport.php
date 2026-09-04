<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['report_ref', 'school', 'department', 'total_candidates', 'file_format', 'status'])]
final class GraduationListReport extends Model
{
    protected $table = 'graduation_list_reports';
}
