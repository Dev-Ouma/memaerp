<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AdminSetupDefinition extends Model
{
    use HasUuids;

    protected $fillable = ['institution_id', 'setup_key', 'category', 'name', 'description', 'consumer', 'missing_behaviour', 'validation_schema', 'supports_import', 'supports_preview'];

    protected function casts(): array
    {
        return ['validation_schema' => 'array', 'supports_import' => 'boolean', 'supports_preview' => 'boolean'];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AdminSetupVersion::class);
    }
}
