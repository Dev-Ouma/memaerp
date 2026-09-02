<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AcademicProgramme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_programmes';

    protected $fillable = [
        'code',
        'title',
        'school',
        'department',
        'award',
        'cue_code',
        'level',
        'duration_semesters',
        'total_credits',
        'description',
        'status',
    ];

    protected $casts = [
        'duration_semesters' => 'integer',
        'total_credits' => 'integer',
    ];
}
