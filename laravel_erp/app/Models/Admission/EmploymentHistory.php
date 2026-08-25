<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    use HasUuids;

    protected $table = 'employment_history';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'employer_name',
        'position',
        'started_on',
        'ended_on',
        'is_current',
        'responsibilities',
        'reference_name',
        'reference_contact',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_current' => 'boolean',
        ];
    }
}
