<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Middleware;

use App\Modules\Iam\Models\User;
use Closure;
use Illuminate\Http\Request;
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
        }

        return $next($request);
    }
}
