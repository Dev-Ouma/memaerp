<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\SessionManager;
use App\Modules\Iam\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class MfaController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly SessionManager $sessions,
    ) {}

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'challenge_token' => ['required', 'string', 'size:64'],
            'code' => ['required', 'string', 'min:6', 'max:64'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);
        $challenge = DB::table('iam.mfa_challenges')
            ->where('token_hash', hash('sha256', $validated['challenge_token']))
            ->whereNull('consumed_at')->where('expires_at', '>', now())->first();

        if ($challenge === null || $challenge->attempts >= 5) {
            throw ValidationException::withMessages(['code' => ['The MFA challenge is invalid or expired.']]);
        }

        $user = User::query()->whereKey($challenge->user_id)->firstOrFail();
        $validTotp = is_string($user->mfa_secret) && $this->totp->verify($user->mfa_secret, $validated['code']);
        $recoveryCodes = $user->mfa_recovery_codes ?? [];
        $recoveryIndex = null;
        foreach ($recoveryCodes as $index => $hash) {
            if (Hash::check($validated['code'], (string) $hash)) {
                $recoveryIndex = $index;
                break;
            }
        }

        if (! $validTotp && $recoveryIndex === null) {
            DB::table('iam.mfa_challenges')->where('id', $challenge->id)->increment('attempts');
            throw ValidationException::withMessages(['code' => ['The authentication code is invalid.']]);
        }

        if ($recoveryIndex !== null) {
            unset($recoveryCodes[$recoveryIndex]);
            $user->forceFill(['mfa_recovery_codes' => array_values($recoveryCodes)])->save();
        }
        DB::table('iam.mfa_challenges')->where('id', $challenge->id)->update([
            'consumed_at' => now(), 'updated_at' => now(),
        ]);
        Auth::guard('web')->login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->put('iam_session_version', $user->session_version);
        }
        $newToken = $user->createToken($validated['device_name'] ?? 'Web browser');
        $trackedSession = $this->sessions->create($user, $request, true, $newToken->accessToken->id);
        if ($request->hasSession()) {
            $request->session()->put('iam_user_session_id', $trackedSession['id']);
        }

        return response()->json([
            'message' => 'Multi-factor authentication completed.',
            'token' => $newToken->plainTextToken,
            'user' => ['id' => $user->id, 'email' => $user->email],
        ]);
    }

    public function setup(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $secret = $this->totp->generateSecret();
        // Persist the pending secret encrypted by the model cast. It is not an active factor
        // until confirm() verifies a code, and this also supports bearer-token API clients that
        // do not have a Laravel cookie session.
        $user->forceFill(['mfa_secret' => $secret, 'mfa_enabled' => false])->save();

        return response()->json([
            'secret' => $secret,
            'provisioning_uri' => $this->totp->provisioningUri($secret, $user->email),
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $this->user($request);
        $secret = $user->mfa_secret;
        if (! is_string($secret) || ! $this->totp->verify($secret, $validated['code'])) {
            throw ValidationException::withMessages(['code' => ['The authentication code is invalid.']]);
        }
        $plainCodes = collect(range(1, 10))->map(fn (): string => strtoupper(Str::random(5).'-'.Str::random(5)))->all();
        $user->forceFill([
            'mfa_enabled' => true,
            'mfa_secret' => $secret,
            'mfa_recovery_codes' => array_map(
                static fn (string $code): string => Hash::make($code),
                $plainCodes,
            ),
        ])->save();
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['message' => 'MFA is enabled.', 'recovery_codes' => $plainCodes]);
    }

    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate(['password' => ['required', 'current_password']]);
        unset($validated);
        $user = $this->user($request);
        $user->forceFill(['mfa_enabled' => false, 'mfa_secret' => null, 'mfa_recovery_codes' => null])->save();
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['message' => 'MFA is disabled.']);
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
