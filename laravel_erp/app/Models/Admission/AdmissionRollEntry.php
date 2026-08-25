<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdmissionRollEntry extends Model
{
    use HasUuids;

    protected $table = 'admission_roll_entries';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_roll_id',
        'admission_application_id',
        'applicant_number',
        'application_number',
        'applicant_name',
        'programme_name',
        'campus_name',
        'study_mode_name',
        'offer_reference',
        'decision_outcome',
        'is_eligible',
        'eligibility_note',
        'sort_order',
        'entry_checksum',
    ];

    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
        ];
    }
}
