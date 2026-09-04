<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class MailSmokePasswordResetCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_smoke_command_dispatches_reset_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'smoke.reset@mema.ac.ke',
            'is_active' => true,
        ]);

        $this->artisan('mail:smoke-password-reset', ['email' => $user->email])
            ->assertSuccessful();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
