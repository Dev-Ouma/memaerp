<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'programmes';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'department_id',
        'qualification_level_id',
        'study_area_id',
        'course_id',
        'code',
        'name',
        'description',
        'duration_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }
}
