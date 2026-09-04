<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['asset_title', 'course_shell', 'resource_type', 'file_size', 'uploaded_by', 'upload_date', 'downloads_views', 'access_rule'])]
final class LmsEResource extends Model
{
    protected $table = 'lms_e_resources';
}
