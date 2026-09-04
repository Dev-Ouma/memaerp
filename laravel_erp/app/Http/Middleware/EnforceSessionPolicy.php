<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based idle / absolute session limits and session_version kill-switch (IAM §00.10).
 */
final class EnforceSessionPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $next($request);
        }

        $session = $request->session();
        $now = now()->getTimestamp();

        if (! $session->has('auth_session_started_at')) {
            $session->put('auth_session_started_at', $now);
            $session->put('auth_last_activity_at', $now);
            $session->put('auth_session_version', (int) ($user->session_version ?? 1));
        }

        $storedVersion = (int) $session->get('auth_session_version', 1);
        $currentVersion = (int) ($user->session_version ?? 1);
        if ($storedVersion !== $currentVersion) {
            return $this->revokeAndRedirect($request, 'Your session was revoked. Please sign in again.');
        }

        $role = $user->activeRole();
        $idleMinutes = (int) config("session.idle_timeouts.{$role}", config('session.idle_timeouts.default', 30));
        $absoluteMinutes = (int) config("session.absolute_timeouts.{$role}", config('session.absolute_timeouts.default', 720));

        $lastActivity = (int) $session->get('auth_last_activity_at', $now);
        $startedAt = (int) $session->get('auth_session_started_at', $now);

        if (($now - $lastActivity) > ($idleMinutes * 60)) {
            return $this->revokeAndRedirect($request, 'Your session expired due to inactivity. Please sign in again.');
        }

        if (($now - $startedAt) > ($absoluteMinutes * 60)) {
            return $this->revokeAndRedirect($request, 'Your session reached its maximum duration. Please sign in again.');
        }

        $session->put('auth_last_activity_at', $now);

        return $next($request);
    }

    private function revokeAndRedirect(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
