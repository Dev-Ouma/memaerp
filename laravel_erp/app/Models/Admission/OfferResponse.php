<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OfferResponse extends Model
{
    use HasUuids;

    protected $table = 'offer_responses';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_offer_id',
        'admission_application_id',
        'response',
        'responded_at',
        'declaration_version',
        'terms_version',
        'comment',
        'admission_intake_id',
        'decision_status',
        'decided_by',
        'decided_at',
        'decision_note',
        'ip_address',
        'user_agent',
        'correlation_id',
        'evidence_hash',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }
}
