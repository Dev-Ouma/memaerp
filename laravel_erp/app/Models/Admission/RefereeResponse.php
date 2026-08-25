<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefereeResponse extends Model
{
    use HasUuids;

    protected $table = 'referee_responses';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'referee_request_id',
        'overall_rating',
        'answers',
        'comments',
        'recommends',
        'submitted_ip',
        'submitted_at',
        'evidence_hash',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'recommends' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }
}
