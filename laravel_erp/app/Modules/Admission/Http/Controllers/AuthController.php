<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Models\Platform\LoginAttempt;
use App\Models\User;
use App\Modules\Admission\Services\ApplicantRegistrationService;
use App\Modules\Platform\Api\ApiException;
use App\Modules\Platform\Api\ApiResponse;
use App\Modules\Platform\Auth\ApiTokenService;
use App\Support\PasswordPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthController
{
    public function register(Request $request, ApplicantRegistrationService $service): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', PasswordPolicy::rules()],
            'first_name' => ['required', 'string', 'max:100'], 'middle_name' => ['nullable', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:32'], 'phone_country_code' => ['nullable', 'string', 'max:5'], 'nationality' => ['nullable', 'string', 'max:80'],
            'county' => ['nullable', 'string', 'max:80'], 'acquisition_source' => ['nullable', 'string', 'max:60'], 'programme_offering_id' => ['nullable', 'integer', 'exists:programme_offerings,id'],
            'terms_version' => ['required', 'string', 'max:40'], 'privacy_version' => ['required', 'string', 'max:40'], 'cookie_version' => ['required', 'string', 'max:40'],
            'acknowledgement_accepted' => ['accepted'], 'terms_accepted' => ['accepted'], 'privacy_accepted' => ['accepted'], 'marketing_consent' => ['sometimes', 'boolean'],
        ]);
        $result = $service->register($data);

        return ApiResponse::created(['access_token' => $result['token'], 'token_type' => 'Bearer', 'applicant_number' => $result['profile']->applicant_number,
            'application_id' => $result['application']?->id, 'email_verification_required' => true]);
    }

    public function login(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'device_name' => ['nullable', 'string', 'max:120']]);
        $email = mb_strtolower(trim($data['email']));
        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();
        $valid = $user !== null && $user->is_active && ($user->locked_until === null || $user->locked_until->isPast()) && Hash::check($data['password'], $user->password);
        LoginAttempt::create(['email_hash' => hash('sha256', $email), 'user_id' => $user?->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'successful' => $valid, 'failure_reason' => $valid ? null : 'invalid_credentials', 'occurred_at' => now()]);
        if (! $valid) {
            throw ApiException::make(401, 'INVALID_CREDENTIALS', 'The email or password is incorrect.');
        }
        $user->forceFill(['failed_login_count' => 0, 'locked_until' => null, 'last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $issued = $tokens->issue($user, $data['device_name'] ?? 'api-client');

        return ApiResponse::data(['access_token' => $issued->plainTextToken, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $tokens->revoke($request->attributes->get('api_token'));

        return ApiResponse::noContent();
    }
}
