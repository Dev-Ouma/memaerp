<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AdminSetupVersion extends Model
{
    use HasUuids;

    protected $fillable = ['admin_setup_definition_id', 'version', 'status', 'configuration', 'effective_from', 'effective_to', 'checksum', 'created_by', 'published_by', 'published_at', 'archived_by', 'archived_at', 'change_reason'];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'effective_from' => 'date', 'effective_to' => 'date', 'published_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(AdminSetupDefinition::class, 'admin_setup_definition_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(AdminSetupUsage::class);
    }
}
