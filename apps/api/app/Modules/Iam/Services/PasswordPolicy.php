<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Modules\Iam\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PasswordPolicy
{
    public function validate(string $password, ?User $user = null): void
    {
        $errors = [];
        if (strlen($password) < 12 || strlen($password) > 128) {
            $errors[] = 'Password must contain between 12 and 128 characters.';
        }
        if (! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/\d/', $password) || ! preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain uppercase, lowercase, number, and symbol characters.';
        }
        if ($user !== null) {
            foreach (DB::table('iam.password_history')->where('user_id', $user->id)
                ->orderByDesc('created_at')->limit(5)->pluck('password_hash') as $hash) {
                if (Hash::check($password, (string) $hash)) {
                    $errors[] = 'The password was used recently. Choose a different password.';
                    break;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['password' => $errors]);
        }
    }
}
