<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class SessionAndPasswordHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_uses_broker_token_and_bumps_session_version(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset.me@example.test',
            'password' => 'OldSecurePass2026',
            'session_version' => 1,
            'is_active' => true,
        ]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('info');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewSecurePass2026',
                'password_confirmation' => 'NewSecurePass2026',
            ])->assertRedirect(route('login'));

            return true;
        });

        $user->refresh();
        $this->assertSame(2, (int) $user->session_version);
        $this->assertTrue(Hash::check('NewSecurePass2026', $user->password));
    }

    public function test_sign_out_everywhere_bumps_session_version(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'is_active' => true, 'session_version' => 3]);

        $this->actingAs($user)
            ->post(route('account.security.sessions.revoke-others'))
            ->assertRedirect();

        $this->assertSame(4, (int) $user->fresh()->session_version);
    }

    public function test_stale_session_version_forces_reauthentication(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'is_active' => true, 'session_version' => 1]);

        $this->actingAs($user);
        session([
            'auth_session_started_at' => now()->getTimestamp(),
            'auth_last_activity_at' => now()->getTimestamp(),
            'auth_session_version' => 1,
        ]);

        $user->bumpSessionVersion();

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_staff_without_rbac_cannot_run_admission_mutations(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)
            ->post(route('admissions.work-queues.auto-assign'))
            ->assertForbidden();
    }

    public function test_password_policy_rejects_short_passwords_on_reset(): void
    {
        $user = User::factory()->create(['email' => 'short@example.test', 'is_active' => true]);
        $token = Password::broker()->createToken($user);

        $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'Short1Aa',
                'password_confirmation' => 'Short1Aa',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_production_session_defaults_use_host_prefix_and_secure_cookie(): void
    {
        $previousEnv = getenv('APP_ENV') ?: null;
        $previousSecure = getenv('SESSION_SECURE_COOKIE');
        $previousCookie = getenv('SESSION_COOKIE');

        putenv('APP_ENV=production');
        $_ENV['APP_ENV'] = 'production';
        $_SERVER['APP_ENV'] = 'production';
        putenv('SESSION_COOKIE');
        putenv('SESSION_SECURE_COOKIE');
        unset($_ENV['SESSION_COOKIE'], $_SERVER['SESSION_COOKIE'], $_ENV['SESSION_SECURE_COOKIE'], $_SERVER['SESSION_SECURE_COOKIE']);

        try {
            $session = require config_path('session.php');
            $this->assertSame('__Host-ERPSESSION', $session['cookie']);
            $this->assertTrue((bool) $session['secure']);
            $this->assertTrue((bool) $session['encrypt']);
            $this->assertSame('/', $session['path']);
        } finally {
            if ($previousEnv === null) {
                putenv('APP_ENV');
                unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
            } else {
                putenv('APP_ENV='.$previousEnv);
                $_ENV['APP_ENV'] = $previousEnv;
                $_SERVER['APP_ENV'] = $previousEnv;
            }
            if ($previousCookie === false || $previousCookie === null) {
                putenv('SESSION_COOKIE');
                unset($_ENV['SESSION_COOKIE'], $_SERVER['SESSION_COOKIE']);
            } else {
                putenv('SESSION_COOKIE='.$previousCookie);
                $_ENV['SESSION_COOKIE'] = $previousCookie;
            }
            if ($previousSecure === false || $previousSecure === null) {
                putenv('SESSION_SECURE_COOKIE');
                unset($_ENV['SESSION_SECURE_COOKIE'], $_SERVER['SESSION_SECURE_COOKIE']);
            } else {
                putenv('SESSION_SECURE_COOKIE='.$previousSecure);
                $_ENV['SESSION_SECURE_COOKIE'] = $previousSecure;
            }
        }
    }

    public function test_payment_sandbox_auto_confirm_defaults_off_outside_phpunit_override(): void
    {
        // phpunit.xml forces true for Feature payment journeys; the template
        // must stay false so staging copies of .env.example remain safe.
        $contents = file_get_contents(base_path('.env.example'));
        $this->assertNotFalse($contents);
        $this->assertStringContainsString('PAYMENT_SANDBOX_AUTO_CONFIRM=false', $contents);
        $this->assertStringContainsString('__Host-ERPSESSION', $contents);
        $this->assertStringContainsString("env('PAYMENT_SANDBOX_AUTO_CONFIRM', false)", file_get_contents(config_path('admission.php')) ?: '');
    }
}
