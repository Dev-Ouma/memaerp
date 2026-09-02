<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AcademicDepartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_departments';

    protected $fillable = [
        'code',
        'name',
        'school',
        'hod',
        'programmes_count',
        'staff_count',
        'email',
        'phone',
        'description',
        'status',
    ];

    protected $casts = [
        'programmes_count' => 'integer',
        'staff_count' => 'integer',
    ];
}
