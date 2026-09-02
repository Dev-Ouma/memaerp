<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'language', 'timezone', 'email_notifications', 'browser_notifications', 'profile_discoverable', 'theme', 'accessibility_settings', 'privacy_settings', 'communication_settings', 'learning_settings'])]
final class UserPreference extends Model
{
    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
            'profile_discoverable' => 'boolean',
            'accessibility_settings' => 'json',
            'privacy_settings' => 'json',
            'communication_settings' => 'json',
            'learning_settings' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
