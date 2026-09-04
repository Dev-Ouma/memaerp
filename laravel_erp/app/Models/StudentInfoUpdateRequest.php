<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['request_no', 'student_id', 'student_name', 'reg_no', 'update_type', 'current_details', 'requested_details', 'verification_status'])]
final class StudentInfoUpdateRequest extends Model {}
