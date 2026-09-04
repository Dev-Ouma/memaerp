<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['validation_code', 'school', 'total_candidates', 'dean_signoff', 'registrar_audit', 'status'])]
final class GraduationListValidation extends Model
{
    protected $table = 'graduation_list_validations';
}
