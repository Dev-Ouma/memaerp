<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * Shared institutional password rule (IAM § password policy).
 * Used by register, reset, and change-password flows.
 */
final class PasswordPolicy
{
    public static function rules(): Password
    {
        return Password::min(12)->mixedCase()->letters()->numbers();
    }

    /** @return array<string, string> */
    public static function messages(string $attribute = 'password'): array
    {
        return [
            "{$attribute}.min" => 'Password must contain at least 12 characters.',
            "{$attribute}.mixed" => 'Password must include both uppercase and lowercase letters.',
            "{$attribute}.letters" => 'Password must include at least one letter.',
            "{$attribute}.numbers" => 'Password must include at least one number.',
        ];
    }
}
