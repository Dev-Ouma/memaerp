<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'applicant_number', 'date_of_birth', 'phone', 'nationality', 'county', 'identity_type', 'identity_number', 'has_support_need', 'support_details', 'source_channel', 'qr_token'])] final class ApplicantProfile extends Model
{
    protected $hidden = ['identity_number', 'support_details'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'has_support_need' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class);
    }
}
