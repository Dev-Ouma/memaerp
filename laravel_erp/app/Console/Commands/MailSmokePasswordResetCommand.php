<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

/**
 * Staging smoke: send a real password-reset notification through the configured mailer.
 */
final class MailSmokePasswordResetCommand extends Command
{
    protected $signature = 'mail:smoke-password-reset {email : Active user email to notify}';

    protected $description = 'Send one password-reset notification to verify staging mail delivery';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->confirm('Production mail smoke — continue?', false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('lower(email) = ?', [$email])->where('is_active', true)->first();
        if ($user === null) {
            $this->error("No active user found for [{$email}].");

            return self::FAILURE;
        }

        $status = Password::broker()->sendResetLink(['email' => $user->email]);
        if ($status !== Password::RESET_LINK_SENT) {
            $this->error('Broker did not queue a reset link: '.$status);

            return self::FAILURE;
        }

        $this->info("Password reset notification dispatched to {$user->email} via ".config('mail.default').' ('.$status.').');
        $this->line('Confirm delivery in the staging inbox / mail log. Notification class: '.ResetPassword::class);

        return self::SUCCESS;
    }
}
