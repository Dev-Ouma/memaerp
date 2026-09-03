<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 font-quicksand">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cookie Policy | MEMA University College</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 12pt !important; }
            .print-container { max-width: 100% !important; padding: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased bg-slate-50">

    {{-- Header --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs no-print">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admissions.catalogue') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#0A3E50] text-white flex items-center justify-center font-black text-sm shadow-xs">
                        M
                    </div>
                    <div>
                        <span class="block font-black text-slate-900 text-sm tracking-tight leading-none">MEMA UNIVERSITY</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Cookie Governance</span>
                    </div>
                </a>
                <span class="hidden sm:inline-block text-slate-300">|</span>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#0A3E50]/10 text-[#0A3E50]">
                    Cookie Policy {{ $version }}
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ url()->previous() ?: route('admissions.catalogue') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back
                </a>
                <a href="{{ route('legal.privacy') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                    Privacy Policy
                </a>
                <button type="button" onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white text-xs font-extrabold transition-all shadow-xs flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#E67E22]"></i> Export PDF / Print
                </button>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-10 shadow-xs print-container space-y-8">
            <div class="border-b border-slate-200 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider mb-1">Web Telemetry &amp; Cookies</div>
                    <h1 class="text-2xl sm:text-3xl font-black text-[#0A3E50] tracking-tight">Institutional Cookie Policy</h1>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Clear information on how MEMA ERP uses cookies and how to control your preferences.</p>
                </div>
                <div class="sm:text-right text-xs text-slate-500 font-mono bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div><strong>Version:</strong> {{ $version }}</div>
                    <div><strong>Last Revised:</strong> {{ $lastUpdated }}</div>
                </div>
            </div>

            <div class="space-y-6 text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                <section class="space-y-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1">1. What Are Cookies?</h2>
                    <p>Cookies are small text files placed on your device by our web server to remember your authentication session, secure forms against CSRF tampering, and maintain user preferences across pages.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1">2. Types of Cookies We Use</h2>
                    <div class="space-y-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <strong class="text-slate-900 block text-xs">Strictly Necessary Cookies (Mandatory)</strong>
                            <p class="text-[11.5px] text-slate-600 mt-0.5">Essential for logging in, protecting student data, verifying CSRF tokens, and navigating the applicant portal.</p>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <strong class="text-slate-900 block text-xs">Performance &amp; Telemetry Cookies (Optional)</strong>
                            <p class="text-[11.5px] text-slate-600 mt-0.5">Measures system latency and page load speeds to help our engineers optimize the platform during peak admissions intake.</p>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <strong class="text-slate-900 block text-xs">Functional Preferences Cookies (Optional)</strong>
                            <p class="text-[11.5px] text-slate-600 mt-0.5">Remembers your selected course catalog filters, campus choices, and report layout options.</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1">3. Managing Your Cookie Choices</h2>
                    <p>You can adjust your cookie settings at any time by clicking the button below or using your web browser's privacy controls:</p>
                    <div class="pt-2 no-print">
                        <button type="button" onclick="openCookiePreferences()" class="px-4 py-2 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-xs transition-colors shadow-xs">
                            Manage Cookie Preferences
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </main>

    @include('components.cookie-consent')
    <script>lucide.createIcons();</script>
</body>
</html>
