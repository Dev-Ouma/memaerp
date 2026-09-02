<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['center_code', 'name', 'location', 'capacity', 'proctors_allocated', 'special_needs_access', 'status'])]
final class ExamCenter extends Model {}
