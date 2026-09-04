<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['congregation_number', 'date', 'chief_guest', 'gown_return_deadline', 'gown_fine_rate', 'status'])]
final class GraduationCeremony extends Model
{
    protected $table = 'graduation_ceremonies';
}
