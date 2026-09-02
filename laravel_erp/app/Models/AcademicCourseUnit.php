<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AcademicCourseUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_course_units';

    protected $fillable = [
        'unit_code',
        'unit_title',
        'department',
        'credit_hours',
        'lecture_hours',
        'practical_hours',
        'classification',
        'prerequisites',
        'description',
        'status',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
        'lecture_hours' => 'integer',
        'practical_hours' => 'integer',
    ];
}
