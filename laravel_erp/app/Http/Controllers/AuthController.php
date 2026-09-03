<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Models\AdmissionApplication;
use App\Models\ApplicantProfile;
use App\Models\LoginActivity;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Models\UserTrustedDevice;
use App\Modules\Platform\Numbering\NumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

final class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'demoEmail' => app()->environment('local') ? 'admin@mema.ac.ke' : null,
        ]);
    }

    public function showRegister(Request $request): View
    {
        $offerings = ProgrammeOffering::with(['course', 'intake'])
            ->where('is_published', true)
            ->get();

        $selectedOfferingId = $request->query('offering');

        return view('auth.register', compact('offerings', 'selectedOfferingId'));
    }

    public function register(Request $request, NumberGenerator $numbers): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'county' => ['nullable', 'string', 'max:100'],
            'programme_offering_id' => ['nullable', 'exists:programme_offerings,id'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ]);

        [$user, $application] = DB::transaction(function () use ($data, $numbers) {
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'role' => 'applicant',
                'is_active' => true,
            ]);

            $profile = ApplicantProfile::create([
                'user_id' => $user->id,
                'applicant_number' => $numbers->applicantNumber(),
                'phone' => $data['phone'],
                'county' => $data['county'] ?? null,
                'qr_token' => Str::random(48),
            ]);

            $application = null;
            if (!empty($data['programme_offering_id'])) {
                $offering = ProgrammeOffering::with('intake')->find($data['programme_offering_id']);
                if ($offering) {
                    $intakeToken = strtoupper(str_replace('-', '', $offering->intake->code ?? 'SEP2026'));
                    $application = AdmissionApplication::create([
                        'applicant_profile_id' => $profile->id,
                        'programme_offering_id' => $offering->id,
                        'application_number' => $numbers->applicationNumber($intakeToken),
                        'form_data' => [],
                    ]);
                    $application->histories()->create([
                        'to_status' => 'DRAFT',
                        'actor_user_id' => $user->id,
                        'reason_code' => 'account_created',
                        'created_at' => now(),
                    ]);
                }
            }

            return [$user, $application];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admissions.portal')->with('success', "Welcome to MEMA College & University. Your applicant account ({$user->applicantProfile->applicant_number}) has been created successfully. Proceed to complete your application below.");
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'], // support username or email
            'password' => ['required', 'string'],
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? '';
        $uaData = $this->parseUserAgent($userAgent);

        // Retrieve lockout settings from Admin Setup
        $lockoutSettings = $this->getLockoutSettings();
        $maxAttempts = $lockoutSettings['max_failed_attempts'];
        $lockoutMinutes = $lockoutSettings['lockout_duration_minutes'];

        // Find user by email or username
        $user = User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();

        // Safe generic error message to prevent account enumeration
        $genericError = 'The credentials do not match our records or the account is temporarily locked.';

        if ($user) {
            // Check if account is locked out
            if ($user->locked_until && Carbon::parse($user->locked_until)->isFuture()) {
                $remainingMinutes = Carbon::parse($user->locked_until)->diffInMinutes(now()) + 1;

                // Record failed activity due to lockout
                LoginActivity::create([
                    'user_id' => $user->id,
                    'email_or_username' => $credentials['email'],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device' => $uaData['device'],
                    'browser' => $uaData['browser'],
                    'location' => $uaData['location'],
                    'status' => 'failed_locked',
                ]);

                return back()->withErrors([
                    'email' => "This account is temporarily locked. Please try again in {$remainingMinutes} minutes.",
                ])->onlyInput('email');
            }
        }

        // Attempt login
        // Check credentials manually to record failed count on user before attempt
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $authAttempt = false;

        if ($user && Hash::check($credentials['password'], $user->password) && $user->is_active) {
            Auth::login($user, $request->boolean('remember'));
            $authAttempt = true;
        }

        if (! $authAttempt) {
            if ($user) {
                $user->failed_login_count += 1;
                if ($user->failed_login_count >= $maxAttempts) {
                    $user->locked_until = now()->addMinutes($lockoutMinutes)->toIso8601String();
                }
                $user->save();

                LoginActivity::create([
                    'user_id' => $user->id,
                    'email_or_username' => $credentials['email'],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device' => $uaData['device'],
                    'browser' => $uaData['browser'],
                    'location' => $uaData['location'],
                    'status' => 'failed',
                ]);
            }

            return back()->withErrors(['email' => $genericError])->onlyInput('email');
        }

        // Authentication Success
        $user = Auth::user();
        $before = $user->toArray();

        $user->failed_login_count = 0;
        $user->locked_until = null;
        if (! $user->first_login_at) {
            $user->first_login_at = now();
        }
        $user->last_login_at = now();
        $user->last_login_ip = $ip;
        $user->last_successful_login_at = now();
        $user->save();

        LoginActivity::create([
            'user_id' => $user->id,
            'email_or_username' => $credentials['email'],
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device' => $uaData['device'],
            'browser' => $uaData['browser'],
            'location' => $uaData['location'],
            'status' => 'success',
        ]);

        // Trust device if "remember me" checked
        if ($request->boolean('remember')) {
            UserTrustedDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_name' => $uaData['device'],
                    'browser' => $uaData['browser'],
                ],
                [
                    'ip_address' => $ip,
                    'location' => $uaData['location'],
                    'token' => Str::random(60),
                    'last_used_at' => now(),
                ]
            );
        }

        $request->session()->regenerate();

        return redirect()->intended($user->role === 'applicant' ? route('admissions.portal') : route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // --- Forgot Password Flow (Generic response to prevent enumeration) ---

    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(60);
            $user->email_verification_token = $token;
            $user->save();

            // Simulating sending password reset email
            session()->flash('info', 'If the email exists in our records, a secure password reset link has been dispatched.');
        } else {
            session()->flash('info', 'If the email exists in our records, a secure password reset link has been dispatched.');
        }

        return back();
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email_verification_token', $request->token)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        $user->password = Hash::make($request->password);
        $user->email_verification_token = null;
        $user->password_changed_at = now();
        $user->save();

        session()->flash('success', 'Your password has been successfully reset. Please log in.');

        return redirect()->route('login');
    }

    // --- Helper User Agent Parser ---

    private function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown Browser';
        $device = 'Unknown Device';

        if (preg_match('/MSIE/i', $userAgent) && ! preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $browser = 'Opera';
        }

        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            $device = 'Mobile Device';
            if (preg_match('/iPhone/i', $userAgent)) {
                $device = 'iPhone';
            } elseif (preg_match('/iPad/i', $userAgent)) {
                $device = 'iPad';
            } elseif (preg_match('/Android/i', $userAgent)) {
                $device = 'Android Device';
            }
        } else {
            $device = 'Desktop Computer';
        }

        return [
            'browser' => $browser,
            'device' => $device,
            'location' => 'Nairobi, Kenya',
        ];
    }

    private function getLockoutSettings(): array
    {
        $definition = AdminSetupDefinition::where('setup_key', 'security.account_lockout')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['max_failed_attempts'])) {
                return [
                    'max_failed_attempts' => (int) $activeVersion->configuration['max_failed_attempts'],
                    'lockout_duration_minutes' => (int) $activeVersion->configuration['lockout_duration_minutes'],
                ];
            }
        }

        return [
            'max_failed_attempts' => 5,
            'lockout_duration_minutes' => 15,
        ];
    }
}
