<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

final class ModuleState extends Model
{
    protected $fillable = ['module_key', 'is_active', 'updated_by'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * The staff member who last changed this module's state.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Retrieve a module's active state, using a short cache to avoid DB hits on every request.
     * Cache is busted whenever a toggle is saved.
     */
    public static function isActive(string $moduleKey): bool
    {
        return (bool) Cache::remember("module_state_{$moduleKey}", 60, function () use ($moduleKey) {
            $row = static::where('module_key', $moduleKey)->first();

            return $row ? $row->is_active : true; // default: active if row missing
        });
    }

    /**
     * Toggle a module on or off, bust its cache, and record the actor.
     */
    public static function setActive(string $moduleKey, bool $active, int $userId): self
    {
        $row = self::firstOrNew(['module_key' => $moduleKey]);
        $row->is_active = $active;
        $row->updated_by = $userId;
        $row->save();

        Cache::forget("module_state_{$moduleKey}");

        return $row;
    }

    /**
     * Return all module states keyed by module_key.
     *
     * @return array<string, bool>
     */
    public static function allStates(): array
    {
        return self::all()->pluck('is_active', 'module_key')->map(fn ($v) => (bool) $v)->toArray();
    }

    public static function visibleTo(?User $user, string $moduleKey): bool
    {
        if ($user?->isAdmin()) {
            return true;
        }

        return self::isActive($moduleKey);
    }
}
