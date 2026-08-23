<?php

declare(strict_types=1);

namespace Tests\Feature\Iam;

use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\TotpService;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class IamSecurityFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitutionSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DemoUserSeeder::class);
    }

    public function test_user_can_authenticate_with_staff_identifier(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'login' => 'EMP-000001', 'password' => 'password123', 'device_name' => 'Feature test',
        ])->assertOk()->assertJsonPath('user.email', 'admin@mema.ac.ke');

        $this->assertDatabaseHas('iam.user_sessions', ['device_name' => 'Feature test']);
    }

    public function test_five_bad_passwords_lock_the_account(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'login' => 'admin@mema.ac.ke', 'password' => 'incorrect',
            ])->assertUnprocessable();
        }

        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $this->assertTrue($user->isLocked());
        $this->postJson('/api/v1/auth/login', [
            'login' => 'admin@mema.ac.ke', 'password' => 'password123',
        ])->assertForbidden();
    }

    public function test_password_reset_response_does_not_disclose_account_existence(): void
    {
        $known = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'admin@mema.ac.ke']);
        $unknown = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'missing@mema.ac.ke']);

        $known->assertOk();
        $unknown->assertOk();
        $this->assertSame($known->json('message'), $unknown->json('message'));
    }

    public function test_valid_reset_changes_password_revokes_sessions_and_records_history(): void
    {
        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $token = 'known-reset-token';
        $user->createToken('existing');
        DB::table('iam.password_reset_tokens')->insert([
            'email' => $user->email, 'institution_id' => $user->institution_id,
            'token_hash' => Hash::make($token), 'expires_at' => now()->addMinutes(15),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email, 'token' => $token,
            'password' => 'A-New-Secure-Password-2026!',
            'password_confirmation' => 'A-New-Secure-Password-2026!',
        ])->assertOk();

        $this->assertTrue(Hash::check('A-New-Secure-Password-2026!', $user->fresh()->password));
        $this->assertSame('argon2id', password_get_info($user->fresh()->password)['algoName']);
        $this->assertDatabaseCount('iam.personal_access_tokens', 0);
        $this->assertDatabaseHas('iam.password_history', ['user_id' => $user->id]);
    }

    public function test_totp_enrollment_and_login_challenge_complete_end_to_end(): void
    {
        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($user);
        $setup = $this->postJson('/api/v1/auth/mfa/setup')->assertOk();
        $secret = $setup->json('secret');
        $code = app(TotpService::class)->at($secret, intdiv(time(), 30));
        $confirmation = $this->postJson('/api/v1/auth/mfa/confirm', ['code' => $code]);
        $confirmation->assertOk()->assertJsonCount(10, 'recovery_codes');
        $this->assertTrue($user->fresh()->mfa_enabled);

        auth()->forgetGuards();
        $login = $this->postJson('/api/v1/auth/login', [
            'login' => 'admin', 'password' => 'password123',
        ])->assertStatus(202)->assertJsonPath('mfa_required', true);
        $this->postJson('/api/v1/auth/mfa/verify', [
            'challenge_token' => $login->json('challenge_token'),
            'code' => app(TotpService::class)->at($secret, intdiv(time(), 30)),
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_logout_all_increments_session_version_and_revokes_tokens(): void
    {
        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $token = $user->createToken('first')->plainTextToken;
        $version = $user->session_version;

        $this->withToken($token)->postJson('/api/v1/auth/logout-all')->assertOk();

        $this->assertSame($version + 1, $user->fresh()->session_version);
        $this->assertDatabaseCount('iam.personal_access_tokens', 0);
    }

    public function test_revoking_a_tracked_browser_session_invalidates_its_cookie_session(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'login' => 'auditor@mema.ac.ke', 'password' => 'password123', 'device_name' => 'Audit laptop',
        ])->assertOk();
        $sessionId = (string) DB::table('iam.user_sessions')->where('device_name', 'Audit laptop')->value('id');
        $login->assertSessionHas('iam_user_session_id', $sessionId);

        $this->deleteJson("/api/v1/auth/sessions/{$sessionId}")->assertOk();
        $this->getJson('/api/v1/auth/me')->assertUnauthorized()->assertJsonPath('error.code', 'SESSION_REVOKED');
    }

    public function test_authorized_admin_can_view_live_users_and_roles(): void
    {
        $admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/iam/users')->assertOk()->assertJsonPath('meta.total', 8);
        $this->getJson('/api/v1/iam/roles')->assertOk()->assertJsonCount(55, 'data');
        $this->assertCount(11, DB::table('iam.roles')->distinct()->pluck('family'));
    }

    public function test_privileged_role_requires_mfa_when_policy_is_enabled(): void
    {
        config()->set('iam.enforce_mandatory_mfa', true);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'admin@mema.ac.ke', 'password' => 'password123',
        ])->assertForbidden()->assertJsonPath('code', 'MFA_ENROLLMENT_REQUIRED');
    }

    public function test_admin_mfa_reset_clears_factors_and_revokes_sessions(): void
    {
        $admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $target = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $target->forceFill([
            'mfa_enabled' => true,
            'mfa_secret' => 'JBSWY3DPEHPK3PXP',
            'mfa_recovery_codes' => [Hash::make('recovery-code')],
        ])->save();
        $target->createToken('phone');
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/iam/users/{$target->id}/mfa-reset", [
            'reason' => 'Identity verified by ICT helpdesk.',
        ])->assertOk();

        $target->refresh();
        $this->assertFalse($target->mfa_enabled);
        $this->assertNull($target->mfa_secret);
        $this->assertDatabaseMissing('iam.personal_access_tokens', ['tokenable_id' => $target->id]);
    }
}
