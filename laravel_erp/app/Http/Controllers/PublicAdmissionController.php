<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Modules\Platform\Numbering\NumberGenerator;
use App\Support\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PublicAdmissionController extends Controller
{
    public function catalogue(Request $request): View
    {
        $offerings = ProgrammeOffering::with(['course', 'intake'])->where('is_published', true)->whereHas('intake', fn ($q) => $q->where('is_published', true))->when($request->q, fn ($q, $search) => $q->whereHas('course', fn ($c) => $c->where('name', 'ilike', "%{$search}%")->orWhere('code', 'ilike', "%{$search}%")))->get();

        return view('admissions.catalogue', compact('offerings'));
    }

    public function brochure(Request $request): View
    {
        $offerings = ProgrammeOffering::with(['course', 'intake'])
            ->where('is_published', true)
            ->get();

        return view('admissions.brochure', compact('offerings'));
    }

    public function apply(ProgrammeOffering $offering): View
    {
        abort_unless($offering->is_published && $offering->intake->is_published, 404);

        return view('admissions.apply', compact('offering'));
    }

    public function register(Request $request, ProgrammeOffering $offering, NumberGenerator $numbers): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'last_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'email' => ['required', 'string', 'email:filter', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+?254|0)[17]\d{8}$/'],
            'county' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rules()],
            'terms' => ['accepted'],
        ], [
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, and apostrophes.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please enter a valid Kenyan phone number (e.g. +254712345678, 0712345678, or 0113636154).',
            ...PasswordPolicy::messages(),
            'terms.accepted' => 'You must accept the Terms of Admission and Privacy Policy.',
        ]);

        $rawPhone = preg_replace('/\s+/', '', (string) $data['phone']);
        if (str_starts_with($rawPhone, '0')) {
            $normalizedPhone = '+254'.substr($rawPhone, 1);
        } elseif (str_starts_with($rawPhone, '254')) {
            $normalizedPhone = '+'.$rawPhone;
        } else {
            $normalizedPhone = $rawPhone;
        }

        [$user, $application] = DB::transaction(function () use ($data, $normalizedPhone, $offering, $numbers) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'role' => 'applicant',
                'is_active' => true,
            ]);
            $intakeToken = strtoupper(str_replace('-', '', $offering->intake->code ?? 'SEP2026'));
            $profile = ApplicantProfile::create([
                'user_id' => $user->id,
                'applicant_number' => $numbers->applicantNumber(),
                'phone' => $normalizedPhone,
                'county' => $data['county'] ?? null,
                'qr_token' => Str::random(48),
            ]);
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

            return [$user, $application];
        });
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admissions.portal')->with('success', "Welcome to MEMA College & University. Your applicant number is {$user->applicantProfile->applicant_number}.");
    }

    public function verify(string $token): View
    {
        $offer = AdmissionOffer::with('application.applicant.user', 'application.offering.course')->where('verification_token', $token)->firstOrFail();

        return view('admissions.verify', compact('offer'));
    }
}
