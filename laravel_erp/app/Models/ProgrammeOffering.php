<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'admission_intake_id', 'campus', 'study_mode', 'capacity', 'application_fee', 'requirements', 'is_published'])] final class ProgrammeOffering extends Model
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(AdmissionIntake::class, 'admission_intake_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class, 'programme_offering_id');
    }
}
