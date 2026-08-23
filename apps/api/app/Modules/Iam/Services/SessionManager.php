<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\User;
use App\Platform\Support\Uuid7;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SessionManager
{
    /** @return array{raw: string, id: string} */
    public function create(User $user, Request $request, bool $mfaVerified, ?int $tokenId = null): array
    {
        $raw = bin2hex(random_bytes(32));
        [$idleMinutes, $absoluteHours] = $this->timeouts($user);
        $now = CarbonImmutable::now();
        $id = Uuid7::generate();

        DB::table('iam.user_sessions')->insert([
            'id' => $id,
            'institution_id' => $user->institution_id,
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'session_hash' => hash('sha256', $raw),
            'session_version' => $user->session_version,
            'ip_address' => substr($request->ip() ?? '127.0.0.1', 0, 45),
            'user_agent' => $request->userAgent(),
            'device_name' => $request->string('device_name')->limit(255)->value() ?: 'Web browser',
            'mfa_verified' => $mfaVerified,
            'idle_expires_at' => $now->addMinutes($idleMinutes),
            'absolute_expires_at' => $now->addHours($absoluteHours),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return ['raw' => $raw, 'id' => $id];
    }

    public function revokeAll(User $user, string $reason): void
    {
        DB::transaction(function () use ($user, $reason): void {
            $user->forceFill(['session_version' => $user->session_version + 1])->save();
            DB::table('iam.user_sessions')->where('user_id', $user->id)->whereNull('revoked_at')->update([
                'revoked_at' => now(), 'revoked_reason' => $reason, 'updated_at' => now(),
            ]);
            $user->tokens()->delete();
        });
    }

    /** @return array{0: int, 1: int} */
    private function timeouts(User $user): array
    {
        $families = $user->activeAssignments()->pluck('role.family');
        if ($families->intersect([
            Role::FAMILY_SYSTEM_ADMIN, Role::FAMILY_EXECUTIVE,
            Role::FAMILY_FINANCE, Role::FAMILY_EXAMINATION,
        ])->isNotEmpty()) {
            return [15, 4];
        }
        if ($families->intersect(['administrative', 'academic'])->isNotEmpty()) {
            return [20, 8];
        }

        return [30, 12];
    }
}
