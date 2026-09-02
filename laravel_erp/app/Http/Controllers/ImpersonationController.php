<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        abort_if($request->session()->has('impersonator_id'), 403, 'End the current impersonation first.');
        abort_if($request->user()->is($user) || ! $user->is_active, 422, 'This account cannot be impersonated.');
        $request->session()->put('impersonator_id', $request->user()->id);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', "You are now viewing the site as {$user->name}.");
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        abort_unless($impersonatorId, 403);
        $admin = User::where('id', $impersonatorId)->where('role', 'admin')->where('is_active', true)->firstOrFail();
        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'You have returned to your administrator account.');
    }
}
