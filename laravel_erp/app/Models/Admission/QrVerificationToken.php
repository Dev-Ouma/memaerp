<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QrVerificationToken extends Model
{
    use HasUuids;

    protected $table = 'qr_verification_tokens';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'token_hash',
        'token_prefix',
        'subject_type',
        'subject_id',
        'generated_document_id',
        'admission_application_id',
        'status',
        'issued_at',
        'expires_at',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
        'rotation_of_token_id',
        'scan_count',
        'last_scanned_at',
        'disclosure_policy',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'disclosure_policy' => 'array',
        ];
    }
}
