<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\ProgrammeOffering;
use App\Models\User;
use App\Modules\Platform\Numbering\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

final class PublicAdmissionController extends Controller
{
    public function catalogue(Request $request): View
    {
        $offerings = ProgrammeOffering::with(['course', 'intake'])->where('is_published', true)->whereHas('intake', fn ($q) => $q->where('is_published', true))->when($request->q, fn ($q, $search) => $q->whereHas('course', fn ($c) => $c->where('name', 'ilike', "%{$search}%")->orWhere('code', 'ilike', "%{$search}%")))->get();

        return view('admissions.catalogue', compact('offerings'));
    }

    public function apply(ProgrammeOffering $offering): View
    {
        abort_unless($offering->is_published && $offering->intake->is_published, 404);

        return view('admissions.apply', compact('offering'));
    }

    public function register(Request $request, ProgrammeOffering $offering, NumberGenerator $numbers): RedirectResponse
    {
        $data = $request->validate(['first_name' => ['required', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'phone' => ['required', 'string', 'max:32'], 'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()], 'terms' => ['accepted']]);
        [$user,$application] = DB::transaction(function () use ($data, $offering, $numbers) {
            $user = User::create(['name' => $data['first_name'].' '.$data['last_name'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'email' => strtolower($data['email']), 'password' => $data['password'], 'role' => 'applicant', 'is_active' => true]);
            $intakeToken = strtoupper(str_replace('-', '', $offering->intake->code));
            $profile = ApplicantProfile::create(['user_id' => $user->id, 'applicant_number' => $numbers->applicantNumber(), 'phone' => $data['phone'], 'qr_token' => Str::random(48)]);
            $application = AdmissionApplication::create(['applicant_profile_id' => $profile->id, 'programme_offering_id' => $offering->id, 'application_number' => $numbers->applicationNumber($intakeToken), 'form_data' => []]);
            $application->histories()->create(['to_status' => 'DRAFT', 'actor_user_id' => $user->id, 'reason_code' => 'account_created', 'created_at' => now()]);

            return [$user, $application];
        });
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admissions.portal')->with('success', "Welcome. Your applicant number is {$user->applicantProfile->applicant_number}.");
    }

    public function verify(string $token): View
    {
        $offer = AdmissionOffer::with('application.applicant.user', 'application.offering.course')->where('verification_token', $token)->firstOrFail();

        return view('admissions.verify', compact('offer'));
    }
}
