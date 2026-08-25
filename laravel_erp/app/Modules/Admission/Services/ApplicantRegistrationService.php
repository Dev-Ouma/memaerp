<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicantProfile;
use App\Models\Platform\ConsentRecord;
use App\Models\Platform\EmailVerificationToken;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Modules\Platform\Auth\ApiTokenService;
use App\Modules\Platform\Numbering\NumberGenerator;
use App\Modules\Platform\Outbox\OutboxPublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApplicantRegistrationService
{
    public function __construct(
        private readonly NumberGenerator $numbers,
        private readonly ApiTokenService $tokens,
        private readonly OutboxPublisher $outbox,
    ) {}

    /** @param array<string, mixed> $data */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $email = mb_strtolower(trim((string) $data['email']));
            $user = User::create([
                'name' => trim($data['first_name'].' '.($data['middle_name'] ?? '').' '.$data['last_name']),
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $email,
                'password' => $data['password'],
                'role' => 'applicant',
                'is_active' => true,
                'public_id' => (string) Str::uuid(),
            ]);
            $profile = new ApplicantProfile;
            $profile->forceFill([
                'user_id' => $user->id,
                'applicant_number' => $this->numbers->applicantNumber(),
                'phone' => $data['phone'],
                'phone_country_code' => $data['phone_country_code'] ?? '+254',
                'nationality' => $data['nationality'] ?? 'Kenyan',
                'county' => $data['county'] ?? null,
                'acquisition_source' => $data['acquisition_source'] ?? null,
                'marketing_consent' => (bool) ($data['marketing_consent'] ?? false),
                'terms_version' => $data['terms_version'],
                'privacy_version' => $data['privacy_version'],
                'cookie_version' => $data['cookie_version'],
                'qr_token' => Str::random(48),
            ])->save();

            foreach (['terms', 'privacy', 'cookie'] as $policy) {
                ConsentRecord::create([
                    'user_id' => $user->id,
                    'subject_type' => ApplicantProfile::class,
                    'subject_id' => (string) $profile->id,
                    'policy_type' => $policy,
                    'policy_version' => $data[$policy.'_version'],
                    'accepted' => true,
                    'recorded_at' => now(),
                    'source_channel' => 'api',
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            }

            $application = null;
            if (isset($data['programme_offering_id'])) {
                $offering = ProgrammeOffering::query()->with('intake')->where('is_published', true)->findOrFail($data['programme_offering_id']);
                $application = AdmissionApplication::create([
                    'applicant_profile_id' => $profile->id,
                    'programme_offering_id' => $offering->id,
                    'application_number' => $this->numbers->applicationNumber(strtoupper(str_replace('-', '', $offering->intake->code))),
                    'form_data' => [],
                ]);
            }

            $plainVerification = Str::random(64);
            EmailVerificationToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $plainVerification),
                'sent_to' => $email,
                'expires_at' => now()->addMinutes((int) config('admission.verification.email_token_ttl_minutes')),
            ]);
            $this->outbox->publish('applicant.created', 'applicant', (string) $profile->id, ['user_id' => $user->id]);
            $issued = $this->tokens->issue($user, 'applicant-registration');

            return ['user' => $user, 'profile' => $profile, 'application' => $application, 'token' => $issued->plainTextToken];
        });
    }
}
