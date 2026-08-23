<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\PasswordPolicy;
use App\Modules\Iam\Services\SessionManager;
use App\Platform\Support\Uuid7;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PasswordController extends Controller
{
    public function __construct(
        private readonly PasswordPolicy $policy,
        private readonly SessionManager $sessions,
    ) {}

    public function forgot(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);
        $email = mb_strtolower($validated['email']);
        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();
        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            DB::table('iam.password_reset_tokens')->updateOrInsert(['email' => $email], [
                'institution_id' => $user->institution_id,
                'token_hash' => Hash::make($token),
                'expires_at' => now()->addMinutes(15),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            // Delivery is intentionally queued by the notification integration. In local/test,
            // expose the token only when explicitly enabled so browser automation can complete.
            if (app()->environment(['local', 'testing']) && config('app.debug')) {
                return response()->json(['message' => $this->genericMessage(), 'debug_token' => $token]);
            }
        }

        return response()->json(['message' => $this->genericMessage()]);
    }

    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'], 'token' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ]);
        $record = DB::table('iam.password_reset_tokens')->where('email', mb_strtolower($validated['email']))
            ->where('expires_at', '>', now())->first();
        if ($record === null || ! Hash::check($validated['token'], $record->token_hash)) {
            throw ValidationException::withMessages(['token' => ['The password reset token is invalid or expired.']]);
        }
        $user = User::query()->whereRaw('lower(email) = ?', [mb_strtolower($validated['email'])])->firstOrFail();
        $this->setPassword($user, $validated['password']);
        DB::table('iam.password_reset_tokens')->where('email', mb_strtolower($validated['email']))->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function change(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed'],
        ]);
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $this->setPassword($user, $validated['password']);

        return response()->json(['message' => 'Password changed. Sign in again on this device.']);
    }

    private function setPassword(User $user, string $password): void
    {
        $this->policy->validate($password, $user);
        DB::transaction(function () use ($user, $password): void {
            DB::table('iam.password_history')->insert([
                'id' => Uuid7::generate(), 'institution_id' => $user->institution_id,
                'user_id' => $user->id, 'password_hash' => $user->password,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $user->forceFill([
                'password' => Hash::make($password), 'password_changed_at' => now(),
                'must_change_password' => false,
            ])->save();
            $this->sessions->revokeAll($user, 'PASSWORD_CHANGED');
        });
    }

    private function genericMessage(): string
    {
        return 'If that account exists, password reset instructions have been issued.';
    }
}
