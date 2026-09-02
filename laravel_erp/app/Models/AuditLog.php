<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['actor_user_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'ip_address', 'user_agent', 'occurred_at'])]
final class AuditLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'occurred_at' => 'immutable_datetime'];
    }

    public static function record(string $action, Model $subject, ?array $before, ?array $after): self
    {
        return self::create(['actor_user_id' => auth()->id(), 'action' => $action, 'subject_type' => $subject::class, 'subject_id' => (string) $subject->getKey(), 'before' => $before, 'after' => $after, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'occurred_at' => now()]);
    }
}
