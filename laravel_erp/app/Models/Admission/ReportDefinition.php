<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportDefinition extends Model
{
    use HasUuids;

    protected $table = 'report_definitions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'permission_code',
        'grain',
        'date_basis',
        'default_columns',
        'available_columns',
        'sensitive_columns',
        'available_filters',
        'max_rows',
        'small_cell_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_columns' => 'array',
            'available_columns' => 'array',
            'sensitive_columns' => 'array',
            'available_filters' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
