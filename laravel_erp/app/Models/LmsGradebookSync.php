<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sync_ref', 'course_code', 'cohort', 'enrolled_students', 'total_cat_synced', 'cat1_weight', 'cat2_weight', 'erp_exam_engine_sync', 'sync_timestamp', 'status'])]
final class LmsGradebookSync extends Model
{
    protected $table = 'lms_gradebook_syncs';
}
