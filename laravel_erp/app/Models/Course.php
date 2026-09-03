<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'next_student_serial'])]
final class Course extends Model
{
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function getImageUrlAttribute(): string
    {
        $code = strtolower(str_replace(['-', ' '], ['_', '_'], (string)$this->code));
        $file = "course_{$code}.jpg";
        if (file_exists(public_path("images/courses/{$file}"))) {
            return asset("images/courses/{$file}");
        }
        if (str_starts_with($code, 'phd')) {
            return asset('images/courses/course_phd_cs.jpg');
        }
        if (str_starts_with($code, 'msc') || str_starts_with($code, 'mph') || str_starts_with($code, 'mba')) {
            return asset('images/courses/course_msc_ds.jpg');
        }
        if (str_starts_with($code, 'dip')) {
            return asset('images/courses/course_dip_it.jpg');
        }
        return asset('images/courses/course_bcs.jpg');
    }
}
