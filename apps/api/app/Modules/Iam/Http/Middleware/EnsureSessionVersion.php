<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Middleware;

use App\Modules\Iam\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSessionVersion
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user instanceof User && $request->hasSession()) {
            $issuedVersion = $request->session()->get('iam_session_version');
            if ($issuedVersion !== null && (int) $issuedVersion !== $user->session_version) {
                auth('web')->logout();
                $request->session()->invalidate();

                return response()->json([
                    'error' => ['code' => 'SESSION_REVOKED', 'message' => 'This session has been revoked.'],
                ], 401);
            }

            $trackedSessionId = $request->session()->get('iam_user_session_id');
            if (is_string($trackedSessionId)) {
                $isActive = DB::table('iam.user_sessions')->where('id', $trackedSessionId)
                    ->where('user_id', $user->id)->whereNull('revoked_at')
                    ->where('idle_expires_at', '>', now())->where('absolute_expires_at', '>', now())
                    ->exists();
                if (! $isActive) {
                    auth('web')->logout();
                    $request->session()->invalidate();

                    return response()->json([
                        'error' => ['code' => 'SESSION_REVOKED', 'message' => 'This session has expired or been revoked.'],
                    ], 401);
                }

                DB::table('iam.user_sessions')->where('id', $trackedSessionId)->update([
                    'last_activity_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
