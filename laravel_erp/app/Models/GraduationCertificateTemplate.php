<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['template_code', 'name', 'dimensions', 'security_features', 'signatories', 'status'])]
final class GraduationCertificateTemplate extends Model
{
    protected $table = 'graduation_certificate_templates';
}
