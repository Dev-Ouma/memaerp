<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return response()->json(['data' => DB::table('iam.user_sessions')
            ->where('user_id', $user->id)->orderByDesc('last_activity_at')
            ->get(['id', 'device_name', 'ip_address', 'user_agent', 'mfa_verified',
                'last_activity_at', 'idle_expires_at', 'absolute_expires_at', 'revoked_at', 'revoked_reason'])]);
    }

    public function destroy(Request $request, string $session): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $changed = DB::table('iam.user_sessions')->where('id', $session)->where('user_id', $user->id)
            ->whereNull('revoked_at')->update([
                'revoked_at' => now(), 'revoked_reason' => 'USER_REVOKED', 'updated_at' => now(),
            ]);
        abort_if($changed === 0, 404);

        return response()->json(['message' => 'Session revoked.']);
    }
}
