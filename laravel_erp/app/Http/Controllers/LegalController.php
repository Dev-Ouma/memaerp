<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class LegalController extends Controller
{
    public function terms(Request $request): View|Response
    {
        $lastUpdated = 'January 15, 2026';
        $version = 'v3.2';

        return view('legal.terms', compact('lastUpdated', 'version'));
    }

    public function privacy(Request $request): View|Response
    {
        $lastUpdated = 'January 15, 2026';
        $version = 'v3.2';
        $dpoEmail = 'dpo@mema.ac.ke';
        $dpoPhone = '+254 113 636 154';

        return view('legal.privacy', compact('lastUpdated', 'version', 'dpoEmail', 'dpoPhone'));
    }

    public function cookies(Request $request): View|Response
    {
        $lastUpdated = 'January 15, 2026';
        $version = 'v2.1';

        return view('legal.cookies', compact('lastUpdated', 'version'));
    }
}
