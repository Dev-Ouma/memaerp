<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'opens_at', 'closes_at', 'acceptance_deadline', 'is_published'])] final class AdmissionIntake extends Model
{
    protected function casts(): array
    {
        return ['opens_at' => 'date', 'closes_at' => 'date', 'acceptance_deadline' => 'date', 'is_published' => 'boolean'];
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(ProgrammeOffering::class);
    }
}
