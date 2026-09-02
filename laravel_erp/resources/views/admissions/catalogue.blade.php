<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic Programmes & Public Admissions | MEMA University College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Quicksand', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-quicksand">

    {{-- Public Navigation Bar --}}
    <header class="bg-[#0A3E50] text-white sticky top-0 z-50 shadow-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a class="flex items-center gap-3 no-underline group" href="{{ route('admissions.catalogue') }}">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#E67E22] to-[#d35400] text-white flex items-center justify-center font-extrabold text-lg shadow-sm">
                    M
                </div>
                <div>
                    <span class="block font-extrabold text-sm sm:text-base tracking-tight text-white uppercase leading-tight">MEMA UNIVERSITY COLLEGE</span>
                    <span class="block text-[11px] text-teal-200 font-medium">Public Admissions & Programme Discovery</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="#programmes" class="hidden sm:inline-block px-3 py-1.5 text-xs font-bold text-teal-100 hover:text-white transition-colors">Programmes</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg bg-teal-800/80 hover:bg-teal-700 text-white text-xs font-bold transition-all border border-white/20 flex items-center gap-1.5 shadow-2xs">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#E67E22]"></i> Back to ERP
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1.5 text-xs font-bold text-teal-100 hover:text-white transition-colors">Sign in</a>
                @endauth
                <a class="px-4 py-1.5 rounded-lg bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5" href="#programmes">
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i> Explore Catalog
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content Shell --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Hero Banner --}}
        <section class="rounded-2xl bg-gradient-to-r from-[#0A3E50] via-[#0b4b61] to-[#007A8C] text-white p-6 sm:p-10 shadow-lg mb-10 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
                <div class="lg:col-span-2 space-y-3">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-teal-100 font-bold text-xs uppercase tracking-wider backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#E67E22] animate-ping"></span> September 2026 Academic Intake
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Transform Your Future with High-Impact Higher Education.
                    </h1>
                    <p class="text-xs sm:text-sm text-teal-100 max-w-2xl leading-relaxed">
                        Explore career-aligned bachelor degrees, postgraduate research programmes, and industry certifications with flexible virtual campus delivery.
                    </p>
                    <div class="pt-2 flex items-center gap-3 flex-wrap">
                        <a href="#programmes" class="px-5 py-2.5 rounded-xl bg-[#E67E22] hover:bg-[#d35400] text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i> Browse All Offerings
                        </a>
                        <a href="{{ route('dashboard.export', ['dataset' => 'programmes', 'format' => 'pdf']) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-xs transition-all flex items-center gap-2 border border-white/20">
                            <i data-lucide="download" class="w-4 h-4 text-teal-200"></i> Download Prospectus (PDF)
                        </a>
                    </div>
                </div>

                {{-- Key Deadline Box --}}
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/20 text-center lg:text-left space-y-2">
                    <div class="text-[11px] font-bold text-teal-200 uppercase tracking-wider">Application Deadline</div>
                    <div class="text-2xl font-extrabold text-white">20 September 2026</div>
                    <p class="text-xs text-teal-100">Standard application processing fee: <strong>KES 1,000 – 2,000</strong> payable upon submission.</p>
                    <div class="pt-2 border-t border-white/15 text-[11px] text-teal-200 flex items-center justify-center lg:justify-start gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#E67E22]"></i> CUE & KNQA Accredited Institution
                    </div>
                </div>
            </div>
        </section>

        {{-- Programmes Catalogue Section --}}
        <section id="programmes" class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 pb-3 border-b border-slate-200">
                <div>
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider">Active Offerings</div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Available Degree & Diploma Programmes</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Select a programme below to review admission criteria and submit your online application.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                        {{ count($offerings) }} Programmes Accepting Applications
                    </span>
                </div>
            </div>

            {{-- Search & Filter Toolbar --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2 relative">
                    <input type="text" id="catalogue-search-input" placeholder="Search programme title, course code, or discipline..." class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs font-medium pl-9">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
                <div>
                    <select id="campus-filter" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">All Campuses</option>
                        <option value="virtual">Virtual Campus (ODeL)</option>
                        <option value="main">Main Campus</option>
                    </select>
                </div>
                <div>
                    <select id="mode-filter" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">All Study Modes</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time / Evening</option>
                        <option value="online">Distance & Online</option>
                    </select>
                </div>
            </div>

            {{-- Programme Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="catalogue-grid">
                @forelse($offerings as $offering)
                    <article class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-[#0A3E50] transition-all flex flex-col justify-between catalogue-card group" data-search="{{ strtolower(($offering->course->code ?? '').' '.($offering->course->name ?? '').' '.($offering->campus ?? '').' '.($offering->study_mode ?? '')) }}" data-campus="{{ strtolower($offering->campus ?? '') }}" data-mode="{{ strtolower($offering->study_mode ?? '') }}">
                        <div class="space-y-3">
                            <div class="flex justify-between items-start gap-2">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                    {{ $offering->course->code ?? 'PRG-'.$offering->id }}
                                </span>
                                <span class="text-[10.5px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    {{ $offering->intake->name ?? 'Sept 2026' }}
                                </span>
                            </div>
                            
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900 group-hover:text-[#0A3E50] transition-colors line-clamp-2 leading-snug">
                                    {{ $offering->course->name ?? 'Degree Programme' }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                    High-impact curriculum with practical coursework, industry certifications, and mentor support.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold text-slate-600 bg-slate-100 border border-slate-200">
                                    {{ $offering->campus ?? 'Virtual Campus' }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold text-slate-600 bg-slate-100 border border-slate-200">
                                    {{ $offering->study_mode ?? 'Full-time' }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold text-slate-600 bg-slate-100 border border-slate-200">
                                    {{ $offering->capacity ?? '150' }} Places
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase">Application Fee</span>
                                <span class="font-extrabold text-sm text-slate-900">KES {{ number_format((float)($offering->application_fee ?? 2000)) }}</span>
                            </div>
                            <a class="px-4 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5" href="{{ route('admissions.apply', $offering) }}">
                                View & Apply <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#E67E22]"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-400">
                        <i data-lucide="book-open" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                        <h3 class="text-sm font-bold text-slate-700">No active programme offerings published</h3>
                        <p class="text-xs text-slate-500 mt-1">Please check back soon or contact the Admissions Office.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    {{-- Public Footer --}}
    <footer class="bg-white border-t border-slate-200 py-6 text-xs text-slate-500 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div>
                © {{ date('Y') }} MEMA University College. All rights reserved. • Enterprise Admissions Platform
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="font-bold text-[#0A3E50] hover:underline">Staff ERP Portal</a>
                <a href="/privacy" class="hover:underline">Privacy Notice</a>
                <a href="/terms" class="hover:underline">Terms & Conditions</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('catalogue-search-input');
            const campusSelect = document.getElementById('campus-filter');
            const modeSelect = document.getElementById('mode-filter');
            const cards = document.querySelectorAll('.catalogue-card');

            function filterCards() {
                const query = (searchInput?.value || '').toLowerCase().trim();
                const campus = (campusSelect?.value || '').toLowerCase().trim();
                const mode = (modeSelect?.value || '').toLowerCase().trim();

                cards.forEach(card => {
                    const searchData = card.dataset.search || '';
                    const cardCampus = card.dataset.campus || '';
                    const cardMode = card.dataset.mode || '';

                    const matchQuery = !query || searchData.includes(query);
                    const matchCampus = !campus || cardCampus.includes(campus);
                    const matchMode = !mode || cardMode.includes(mode);

                    if (matchQuery && matchCampus && matchMode) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput?.addEventListener('input', filterCards);
            campusSelect?.addEventListener('change', filterCards);
            modeSelect?.addEventListener('change', filterCards);
        });
    </script>
</body>
</html>
