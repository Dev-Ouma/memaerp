<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['publication_code', 'list_title', 'date_published', 'published_by', 'total_graduands', 'status'])]
final class GraduationPublication extends Model
{
    protected $table = 'graduation_publications';
}
