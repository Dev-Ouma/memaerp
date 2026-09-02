<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['admission_application_id', 'version', 'snapshot', 'checksum', 'created_at'])] final class ApplicationVersion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }
}
