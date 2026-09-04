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
        'image_path',
    ];

    protected $casts = [
        'duration_semesters' => 'integer',
        'total_credits' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image_path)) {
            if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
                return $this->image_path;
            }
            if (file_exists(public_path($this->image_path))) {
                return asset($this->image_path);
            }

            return asset('storage/'.ltrim($this->image_path, '/'));
        }

        $code = strtolower(str_replace(['-', ' '], ['_', '_'], (string) $this->code));
        $file = "course_{$code}.jpg";
        if (file_exists(public_path("images/courses/{$file}"))) {
            return asset("images/courses/{$file}");
        }
        if (str_starts_with($code, 'cert')) {
            return asset('images/courses/course_dip_it.jpg');
        }
        if (str_starts_with($code, 'hdip')) {
            return asset('images/courses/course_bse.jpg');
        }
        if (str_starts_with($code, 'dip')) {
            return asset('images/courses/course_dip_it.jpg');
        }
        if (str_starts_with($code, 'phd')) {
            return asset('images/courses/course_phd_cs.jpg');
        }
        if (str_starts_with($code, 'msc') || str_starts_with($code, 'mph') || str_starts_with($code, 'mba')) {
            return asset('images/courses/course_msc_ds.jpg');
        }
        if (str_starts_with($code, 'bba')) {
            return asset('images/courses/course_bba.jpg');
        }

        return asset('images/courses/course_bcs.jpg');
    }
}
