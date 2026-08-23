<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Http\Requests\LoginRequest;
use App\Modules\Iam\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    /**
     * Handle authentication for both API and Web sessions.
     */
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $login = trim($request->input('login'));
        $password = $request->input('password');
        $ip = $request->ip() ?? '127.0.0.1';
        $userAgent = $request->userAgent();

        // 1. Resolve user by email or username
        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();

        // 2. Validate user existence and credentials
        if (! $user || ! Hash::check($password, $user->password)) {
            $this->recordLoginAttempt(
                user: $user,
                email: $login,
                succeeded: false,
                reason: $user ? 'INVALID_PASSWORD' : 'USER_NOT_FOUND',
                ip: $ip,
                userAgent: $userAgent
            );

            if ($user) {
                $user->increment('failed_login_attempts');
                if ($user->failed_login_attempts >= 5) {
                    // forceFill, not update: the lockout columns are deliberately outside
                    // $fillable so that no request payload can ever reach them.
                    $user->forceFill(['locked_until' => Carbon::now()->addMinutes(15)])->save();
                }
            }

            if ($request->wantsJson() || $request->is('api/*')) {
                throw ValidationException::withMessages([
                    'login' => [__('auth.failed')],
                ]);
            }

            return back()->withErrors(['login' => 'Invalid credentials provided.'])->withInput($request->only('login'));
        }

        // 3. Verify user status
        if (! $user->canAuthenticate()) {
            $this->recordLoginAttempt(
                user: $user,
                email: $login,
                succeeded: false,
                reason: $user->isLocked() ? 'ACCOUNT_LOCKED' : 'ACCOUNT_INACTIVE',
                ip: $ip,
                userAgent: $userAgent
            );

            $msg = $user->isLocked()
                ? 'Account temporarily locked due to failed attempts. Please try again later.'
                : 'Account is deactivated. Please contact ICT support.';

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => $msg], 403);
            }

            return back()->withErrors(['login' => $msg])->withInput($request->only('login'));
        }

        // 4. Successful authentication - reset lockout counters & log
        // Server-derived session state; see the lockout note above for why this bypasses $fillable.
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $ip,
        ])->save();

        $this->recordLoginAttempt(
            user: $user,
            email: $login,
            succeeded: true,
            reason: null,
            ip: $ip,
            userAgent: $userAgent
        );

        // 5. Establish session if web or issue token if API
        Auth::login($user, (bool) $request->input('remember', false));

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            $token = $user->createToken($request->input('device_name', 'default-device'))->plainTextToken;

            return response()->json([
                'message' => 'Authenticated successfully.',
                'token' => $token,
                'user' => $this->formatUserProfile($user),
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Terminate current session / token.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully.']);
        }

        return redirect('/login');
    }

    /**
     * Retrieve authenticated user profile with roles & active permissions.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => $this->formatUserProfile($user),
        ]);
    }

    private function formatUserProfile(User $user): array
    {
        $user->loadMissing(['person.identities', 'institution', 'roleAssignments.role.permissions']);

        $roles = $user->roleAssignments->map(fn ($assignment) => [
            'role_code' => $assignment->role?->code,
            'role_name' => $assignment->role?->name,
            'family' => $assignment->role?->family,
            'scope_type' => $assignment->scope_type,
            'scope_id' => $assignment->scope_id,
        ]);

        $permissions = $user->roleAssignments
            ->flatMap(fn ($ra) => $ra->role?->permissions ?? [])
            ->pluck('name')
            ->unique()
            ->values();

        return [
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'is_active' => $user->is_active,
            'must_change_password' => $user->must_change_password,
            'last_login_at' => $user->last_login_at?->toISOString(),
            'person' => $user->person ? [
                'id' => $user->person->id,
                'full_name' => $user->person->full_name,
                'given_name' => $user->person->given_name,
                'family_name' => $user->person->family_name,
                'primary_email' => $user->person->primary_email,
                'identities' => $user->person->identities->map(fn ($id) => [
                    'type' => $id->identity_type,
                    'identifier' => $id->identifier,
                    'status' => $id->status,
                ]),
            ] : null,
            'institution' => $user->institution ? [
                'id' => $user->institution->id,
                'code' => $user->institution->code,
                'name' => $user->institution->name,
            ] : null,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    private function recordLoginAttempt(
        ?User $user,
        string $email,
        bool $succeeded,
        ?string $reason,
        string $ip,
        ?string $userAgent
    ): void {
        DB::table('iam.login_attempts')->insert([
            'id' => Str::uuid()->toString(),
            'institution_id' => $user?->institution_id,
            'user_id' => $user?->id,
            'email' => $email,
            'succeeded' => $succeeded,
            'failure_reason' => $reason,
            'ip_address' => substr($ip, 0, 45),
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'attempted_at' => Carbon::now(),
        ]);
    }
}
