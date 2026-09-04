<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic Programmes & Online Admissions Portal | MEMA College & University</title>
    <meta name="description" content="Explore Certificate, Diploma, Higher Diploma and Bachelor Degree programmes at MEMA College & University. Apply online for the 2026/2027 academic intake with instant admission letters.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .gradient-teal { background: linear-gradient(135deg, #0A3E50 0%, #0d546d 50%, #007A8C 100%); }
        .card-shadow { box-shadow: 0 4px 20px -2px rgba(10, 62, 80, 0.07), 0 2px 6px -1px rgba(0, 0, 0, 0.04); }
        .card-shadow-hover:hover { box-shadow: 0 20px 35px -4px rgba(10, 62, 80, 0.16), 0 6px 16px -2px rgba(0, 0, 0, 0.06); transform: translateY(-3px); }
        .tier-cert { background: #FEF3C7; color: #92400E; border-color: #FDE68A; }
        .tier-dip { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
        .tier-hdip { background: #E0E7FF; color: #3730A3; border-color: #C7D2FE; }
        .tier-deg { background: #E0F2FE; color: #075985; border-color: #BAE6FD; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex flex-col font-quicksand selection:bg-[#E67E22] selection:text-white">

    {{-- Top Announcement Bar --}}
    <div class="bg-[#072B38] text-white text-xs py-2.5 px-4 border-b border-white/10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-2.5 flex-wrap text-center sm:text-left">
                <span class="px-2.5 py-0.5 rounded-full bg-[#E67E22] text-white font-extrabold text-[10.5px] tracking-wide uppercase shadow-2xs">2026/2027 Admissions</span>
                <span class="text-slate-100 font-medium">Admissions ongoing for <strong>Certificates (D+)</strong>, <strong>Diplomas (C-)</strong>, <strong>Higher Diplomas</strong> &amp; <strong>Degrees</strong>.</span>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="tel:0113636154" class="text-amber-300 hover:text-white transition-colors flex items-center gap-1.5">
                    <i data-lucide="phone-call" class="w-3.5 h-3.5 text-[#E67E22]"></i> 0113636154
                </a>
                <span class="text-white/30">•</span>
                <a href="mailto:admissions@mema.ac.ke" class="text-slate-200 hover:text-white transition-colors flex items-center gap-1.5">
                    <i data-lucide="mail" class="w-3.5 h-3.5 text-[#E67E22]"></i> admissions@mema.ac.ke
                </a>
            </div>
        </div>
    </div>

    {{-- Public Navigation Bar --}}
    <header class="bg-[#0A3E50] text-white sticky top-0 z-50 shadow-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <a class="flex items-center gap-3.5 no-underline group" href="{{ route('admissions.catalogue') }}">
                <div class="w-11 h-11 rounded-2xl bg-white p-1 text-[#0A3E50] flex items-center justify-center font-extrabold text-xl shadow-md border border-white/20 group-hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA" class="w-full h-full object-contain" onerror="this.outerHTML='<span class=\'font-extrabold text-lg text-[#0A3E50]\'>M</span>'">
                </div>
                <div>
                    <span class="block font-extrabold text-base sm:text-lg tracking-tight text-white uppercase leading-tight">MEMA UNIVERSITY COLLEGE</span>
                    <span class="block text-[11px] text-teal-200 font-semibold tracking-wide">TVET Centre of Excellence • Higher Education • Career Pathways</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-xs font-bold text-teal-100">
                <a href="#programmes" class="hover:text-white transition-colors">Programmes</a>
                <a href="#eligibility" class="hover:text-white transition-colors">Grade Matcher</a>
                <a href="#steps" class="hover:text-white transition-colors">How to Apply</a>
                <a href="#faqs" class="hover:text-white transition-colors">FAQs</a>
            </nav>

            <div class="flex items-center gap-2.5">
                @auth
                    @if(auth()->user()->role === 'applicant')
                        <a href="{{ route('admissions.portal') }}" class="px-4 py-2 rounded-xl bg-[#1E8449] hover:bg-[#196f3d] text-white text-xs font-bold transition-all border border-white/20 flex items-center gap-1.5 shadow-2xs">
                            <i data-lucide="file-check" class="w-4 h-4 text-amber-300"></i> My Portal
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-teal-800/90 hover:bg-teal-700 text-white text-xs font-bold transition-all border border-white/20 flex items-center gap-1.5 shadow-2xs">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-[#E67E22]"></i> Staff ERP
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-xs font-bold text-teal-100 hover:text-white transition-colors flex items-center gap-1">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Sign In
                    </a>
                @endauth
                <a class="px-4 py-2 rounded-xl bg-[#E67E22] hover:bg-[#d35400] text-white font-extrabold text-xs transition-all shadow-sm hover:shadow flex items-center gap-1.5" href="#programmes">
                    <i data-lucide="compass" class="w-4 h-4"></i> Browse Catalog
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content Shell --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        
        {{-- Hero Presentation Banner --}}
        <section class="rounded-3xl gradient-teal text-white p-6 sm:p-10 lg:p-12 shadow-xl relative overflow-hidden border border-white/10">
            <div class="absolute right-0 top-0 w-96 h-96 bg-gradient-to-br from-white/10 to-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#E67E22]/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row items-stretch justify-between gap-8 lg:gap-12">
                {{-- Left Hero Text Column --}}
                <div class="flex-1 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 text-teal-100 font-bold text-xs uppercase tracking-wider backdrop-blur-md border border-white/20">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#E67E22] animate-ping"></span> 
                        <span>September 2026 / 2027 Academic Intake</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                        Build Practical Skills.<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-orange-300 to-emerald-300">Earn Accredited Qualifications.</span>
                    </h1>

                    <p class="text-xs sm:text-sm text-teal-100 max-w-2xl leading-relaxed font-medium">
                        MEMA College &amp; University offers industry-aligned <strong>Certificates</strong>, <strong>Diplomas</strong>, <strong>Higher Diplomas</strong>, and selected <strong>Bachelor Degrees</strong> with full-time, evening, weekend, and virtual e-learning study modes.
                    </p>

                    <div class="pt-2 flex items-center gap-3.5 flex-wrap">
                        <a href="#programmes" class="px-6 py-3 rounded-xl bg-[#E67E22] hover:bg-[#d35400] text-white font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center gap-2 group">
                            <span>Browse All {{ count($offerings) }} Programmes</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 group-hover:translate-y-0.5 transition-transform"></i>
                        </a>
                        <a href="{{ route('admissions.brochure') }}" target="_blank" class="px-5 py-3 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs sm:text-sm backdrop-blur-md transition-all flex items-center gap-2 border border-white/20 shadow-xs">
                            <i data-lucide="file-text" class="w-4 h-4 text-amber-300"></i> Download Brochure (PDF)
                        </a>
                    </div>

                    {{-- Live Trust Badges (Horizontal Row) --}}
                    <div class="pt-4 flex flex-wrap items-center gap-4 sm:gap-6 border-t border-white/15 text-xs text-teal-100 font-semibold">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>TVET CDACC Certified</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>KNQA Level 5, 6 &amp; 7</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>CUE Degree Approval</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Instant Offer Letter</span>
                        </div>
                    </div>
                </div>

                {{-- Right Intake Summary Card --}}
                <div class="w-full lg:w-96 flex-shrink-0 bg-white/10 backdrop-blur-md rounded-2xl p-6 sm:p-7 border border-white/25 space-y-4 shadow-xl text-white flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-amber-300 uppercase tracking-wider">Current Intake</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/90 text-white text-[10.5px] font-bold">Fast-Track</span>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">September 2026</div>
                            <p class="text-xs text-teal-200 mt-1">Application Deadline: <strong>25 September 2026</strong></p>
                        </div>

                        <div class="space-y-2 py-3 border-y border-white/15 text-xs text-teal-100">
                            <div class="flex justify-between items-center">
                                <span>Certificates Fee:</span>
                                <strong class="text-white font-extrabold">KES 500</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Diplomas Fee:</span>
                                <strong class="text-white font-extrabold">KES 750</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Higher Dip &amp; Degrees:</span>
                                <strong class="text-white font-extrabold">KES 1,000 – 1,500</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-teal-200 flex items-center gap-2 pt-2">
                        <i data-lucide="smartphone" class="w-4 h-4 text-[#E67E22] flex-shrink-0"></i>
                        <span>Pay via M-Pesa STK Push or Till <strong>0113636154</strong></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Quick Interactive Eligibility Checker Section --}}
        <section id="eligibility" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 card-shadow space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Interactive Course Matcher
                    </div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                        What is your KCSE Grade or Qualification?
                    </h2>
                    <p class="text-xs text-slate-500">Select your grade below to instantly highlight and filter the programmes you qualify for:</p>
                </div>
                <div class="text-xs text-slate-500 font-bold px-3 py-1 bg-slate-100 rounded-lg border border-slate-200" id="matching-count">
                    Showing all {{ count($offerings) }} courses
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <button type="button" onclick="selectGrade('d_plus')" class="grade-btn text-left p-3.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 transition-all cursor-pointer group">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-sm text-amber-900">KCSE D+ or D</span>
                        <i data-lucide="award" class="w-4 h-4 text-amber-600"></i>
                    </div>
                    <p class="text-[11px] text-amber-800 font-medium mt-1">Direct Entry into <strong>Certificate Courses</strong></p>
                </button>

                <button type="button" onclick="selectGrade('c_minus')" class="grade-btn text-left p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 transition-all cursor-pointer group">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-sm text-emerald-900">KCSE C- or C</span>
                        <i data-lucide="book-check" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-[11px] text-emerald-800 font-medium mt-1">Direct Entry into <strong>Diploma Courses</strong></p>
                </button>

                <button type="button" onclick="selectGrade('diploma')" class="grade-btn text-left p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 transition-all cursor-pointer group">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-sm text-indigo-900">Completed Diploma</span>
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <p class="text-[11px] text-indigo-800 font-medium mt-1">Advance to <strong>Higher Diploma &amp; Degrees</strong></p>
                </button>

                <button type="button" onclick="selectGrade('c_plus')" class="grade-btn text-left p-3.5 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 transition-all cursor-pointer group">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-sm text-blue-900">KCSE C+ &amp; Above</span>
                        <i data-lucide="landmark" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <p class="text-[11px] text-blue-800 font-medium mt-1">Direct Entry into <strong>Bachelor's Degrees</strong></p>
                </button>
            </div>
        </section>

        {{-- Programmes Catalogue Section --}}
        <section id="programmes" class="space-y-6">
            
            {{-- Header & Total Count --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 pb-3 border-b border-slate-200">
                <div>
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider">Academic Catalogue</div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Programmes Directory
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Explore available courses, view detailed entry criteria, tuition fees, and apply online.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold text-[#0A3E50] bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-2xs" id="active-courses-count">
                        {{ count($offerings) }} Programmes Published
                    </span>
                </div>
            </div>

            {{-- Category Tier Filter Tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none" id="tier-filter-tabs">
                <button type="button" data-tier="all" class="tier-tab-btn active px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all bg-[#0A3E50] text-white shadow-xs flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i> All Courses ({{ count($offerings) }})
                </button>
                <button type="button" data-tier="cert" class="tier-tab-btn px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Certificates (KNQA 5 • D+)
                </button>
                <button type="button" data-tier="dip" class="tier-tab-btn px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Diplomas (KNQA 6 • C-)
                </button>
                <button type="button" data-tier="hdip" class="tier-tab-btn px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Higher Diplomas (KNQA 7)
                </button>
                <button type="button" data-tier="deg" class="tier-tab-btn px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Bachelor's Degrees (C+)
                </button>
            </div>

            {{-- Search & Filter Toolbar --}}
            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 card-shadow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                <div class="lg:col-span-6 relative">
                    <input type="text" id="catalogue-search-input" placeholder="Search by programme name (e.g. Computer Science, Business, IT, Nursing)..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] shadow-2xs font-semibold pl-10">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                </div>
                <div class="lg:col-span-3">
                    <select id="campus-filter" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">All Campuses (Main, CBD, Virtual)</option>
                        <option value="main">Main Campus</option>
                        <option value="nairobi">Nairobi CBD Campus</option>
                        <option value="virtual">Virtual Campus (ODeL Online)</option>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <select id="mode-filter" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">All Study Modes</option>
                        <option value="full-time">Full-time Regular</option>
                        <option value="weekend">Weekend / Evening / Part-time</option>
                        <option value="online">Online / Distance Learning</option>
                    </select>
                </div>
            </div>

            {{-- Programme Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="catalogue-grid">
                @forelse($offerings as $offering)
                    @php
                        $code = strtoupper((string)($offering->course->code ?? ''));
                        $courseName = $offering->course->name ?? 'Academic Programme';
                        $tier = 'deg';
                        $tierLabel = "Bachelor's Degree";
                        $tierClass = 'tier-deg';
                        $reqText = 'KCSE Mean Grade C+ (Plus) or Diploma';
                        $duration = '4 Years (8 Semesters)';
                        $levelCode = 'KNQA Level 7 (Undergraduate)';

                        if (str_starts_with($code, 'CERT')) {
                            $tier = 'cert';
                            $tierLabel = 'Certificate Course';
                            $tierClass = 'tier-cert';
                            $reqText = 'KCSE Mean Grade D+ (Plus)';
                            $duration = '1 Year (2 Semesters)';
                            $levelCode = 'KNQA Level 5 (TVET CDACC)';
                        } elseif (str_starts_with($code, 'HDIP')) {
                            $tier = 'hdip';
                            $tierLabel = 'Higher Diploma Course';
                            $tierClass = 'tier-hdip';
                            $reqText = 'Recognized Diploma / Equivalent';
                            $duration = '1.5 Years (3 Semesters)';
                            $levelCode = 'KNQA Level 7 (Post-Diploma)';
                        } elseif (str_starts_with($code, 'DIP')) {
                            $tier = 'dip';
                            $tierLabel = 'Diploma Course';
                            $tierClass = 'tier-dip';
                            $reqText = 'KCSE Mean Grade C- (Minus) or Cert';
                            $duration = '2 Years (4 Semesters)';
                            $levelCode = 'KNQA Level 6 (TVET / KNQA)';
                        }
                    @endphp

                    <article class="bg-white rounded-3xl border border-slate-200 overflow-hidden card-shadow card-shadow-hover transition-all duration-300 flex flex-col justify-between catalogue-card group" 
                             data-tier="{{ $tier }}"
                             data-search="{{ strtolower($code.' '.$courseName.' '.$tierLabel.' '.($offering->campus ?? '').' '.($offering->study_mode ?? '')) }}" 
                             data-campus="{{ strtolower($offering->campus ?? '') }}" 
                             data-mode="{{ strtolower($offering->study_mode ?? '') }}"
                             data-code="{{ $code }}"
                             data-name="{{ $courseName }}"
                             data-tier-label="{{ $tierLabel }}"
                             data-level-code="{{ $levelCode }}"
                             data-req="{{ $reqText }}"
                             data-duration="{{ $duration }}"
                             data-offering-campus="{{ $offering->campus ?? 'Main Campus' }}"
                             data-offering-mode="{{ $offering->study_mode ?? 'Full-time' }}"
                             data-fee="{{ number_format((float)($offering->application_fee ?? 500)) }}"
                             data-image="{{ $offering->course->image_url ?? asset('images/courses/course_bcs.jpg') }}"
                             data-apply-url="{{ route('admissions.apply', $offering) }}">
                        
                        <div>
                            {{-- Course Image Header --}}
                            <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                                <img src="{{ $offering->course->image_url ?? asset('images/courses/course_bcs.jpg') }}" alt="{{ $courseName }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out" onerror="this.src='{{ asset('images/courses/course_bcs.jpg') }}'">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/30 to-transparent"></div>
                                
                                {{-- Top Floating Badges --}}
                                <div class="absolute top-3.5 left-3.5 right-3.5 flex justify-between items-center">
                                    <span class="font-mono text-xs font-extrabold text-white bg-[#0A3E50]/95 backdrop-blur-md px-3 py-1 rounded-lg border border-white/30 shadow-md">
                                        {{ $code }}
                                    </span>
                                    <span class="text-[10.5px] font-extrabold text-white bg-[#1E8449]/95 backdrop-blur-md px-3 py-1 rounded-full border border-white/30 shadow-md">
                                        {{ $offering->intake->name ?? 'Sep 2026 Intake' }}
                                    </span>
                                </div>

                                {{-- Bottom Level Tier Badge on Image --}}
                                <div class="absolute bottom-3.5 left-3.5 right-3.5 flex items-center justify-between">
                                    <span class="inline-block px-3 py-1 rounded-lg text-[11px] font-extrabold uppercase tracking-wider {{ $tierClass }} border shadow-sm">
                                        {{ $tierLabel }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-200">
                                        {{ $levelCode }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body: PROMINENT PROGRAMME NAME & CRITERIA --}}
                            <div class="p-6 space-y-4">
                                
                                {{-- Crystal Clear Programme Title --}}
                                <div>
                                    <h3 class="font-extrabold text-lg text-[#0A3E50] leading-snug tracking-tight group-hover:text-[#E67E22] transition-colors line-clamp-2" title="{{ $courseName }}">
                                        {{ $courseName }}
                                    </h3>
                                </div>

                                {{-- Minimum Requirement Callout Box --}}
                                <div class="bg-slate-50/90 rounded-2xl p-3 border border-slate-200/90 text-xs flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Minimum Entry Requirement</span>
                                        <strong class="text-slate-900 text-xs leading-snug block font-bold">{{ $reqText }}</strong>
                                    </div>
                                </div>

                                {{-- Key Metadata Badges Grid --}}
                                <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                                    <div class="flex items-center gap-1.5 text-slate-600 bg-slate-100/70 px-2.5 py-1.5 rounded-lg border border-slate-200/60 font-semibold">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#0A3E50] flex-shrink-0"></i>
                                        <span class="truncate">{{ $duration }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-slate-600 bg-slate-100/70 px-2.5 py-1.5 rounded-lg border border-slate-200/60 font-semibold">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#0A3E50] flex-shrink-0"></i>
                                        <span class="truncate">{{ $offering->campus ?? 'Main Campus' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-slate-600 bg-slate-100/70 px-2.5 py-1.5 rounded-lg border border-slate-200/60 font-semibold">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#1E8449] flex-shrink-0"></i>
                                        <span class="truncate">{{ $offering->study_mode ?? 'Full-time' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-slate-600 bg-slate-100/70 px-2.5 py-1.5 rounded-lg border border-slate-200/60 font-semibold">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-[#E67E22] flex-shrink-0"></i>
                                        <span class="truncate">{{ $offering->capacity ?? '100' }} Places</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer with Application Fee & Dual Action Buttons --}}
                        <div class="p-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Application Fee</span>
                                <span class="font-extrabold text-base text-slate-900">KES {{ number_format((float)($offering->application_fee ?? 500)) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        onclick="openCardQuickView(this)"
                                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-extrabold text-xs transition-colors shadow-2xs cursor-pointer"
                                        title="View course specifications">
                                    Details
                                </button>
                                <a class="px-4.5 py-2 rounded-xl bg-[#0A3E50] hover:bg-[#E67E22] text-white font-extrabold text-xs transition-all shadow-sm hover:shadow flex items-center gap-1.5" href="{{ route('admissions.apply', $offering) }}">
                                    <span>Apply Now</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-16 text-center text-slate-400 card-shadow space-y-3">
                        <i data-lucide="book-open" class="w-12 h-12 mx-auto text-slate-300"></i>
                        <h3 class="text-base font-extrabold text-slate-700">No matching programmes found</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">Try clearing your search query or selecting a different qualification tier above.</p>
                        <button type="button" onclick="resetFilters()" class="px-4 py-2 rounded-xl bg-[#0A3E50] text-white font-bold text-xs cursor-pointer">Reset All Filters</button>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- 4-Step How to Apply Stepper Section --}}
        <section id="steps" class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 card-shadow space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider">Fast &amp; Simple Process</div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">How to Submit Your Application</h2>
                <p class="text-xs text-slate-500">Streamlined 4-step online admissions process with instant mobile fee verification and immediate offer issuance.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 space-y-3 relative group hover:border-[#0A3E50] transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-[#0A3E50] text-white font-extrabold flex items-center justify-center text-sm shadow-sm">1</div>
                    <h3 class="font-extrabold text-sm text-slate-900">Choose Programme</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Select your preferred Certificate, Diploma, Higher Diploma, or Degree course and click Apply.</p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 space-y-3 relative group hover:border-[#0A3E50] transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-[#0A3E50] text-white font-extrabold flex items-center justify-center text-sm shadow-sm">2</div>
                    <h3 class="font-extrabold text-sm text-slate-900">Fill Bio-Data</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Create your applicant account, fill in your personal contact details, and upload academic certificates.</p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 space-y-3 relative group hover:border-[#0A3E50] transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-[#E67E22] text-white font-extrabold flex items-center justify-center text-sm shadow-sm">3</div>
                    <h3 class="font-extrabold text-sm text-slate-900">Pay Application Fee</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Pay via instant M-Pesa STK Push, Pochi, or Bank Paybill to activate your admissions file.</p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 space-y-3 relative group hover:border-[#0A3E50] transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-[#1E8449] text-white font-extrabold flex items-center justify-center text-sm shadow-sm">4</div>
                    <h3 class="font-extrabold text-sm text-slate-900">Get Admission Letter</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Download your official Admission Letter with QR verification and complete your reporting.</p>
                </div>
            </div>
        </section>

        {{-- Frequently Asked Questions (Accordion) --}}
        <section id="faqs" class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 card-shadow space-y-6">
            <div class="max-w-3xl">
                <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider">Admissions Support</div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Frequently Asked Questions</h2>
            </div>

            <div class="divide-y divide-slate-100 space-y-3 pt-2">
                <details class="group pt-3 cursor-pointer">
                    <summary class="flex justify-between items-center font-extrabold text-sm text-slate-900 list-none">
                        <span>What are the entry requirements for Certificate vs Diploma courses?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
                        Certificate courses require a minimum KCSE Mean Grade of <strong>D+ (Plus)</strong> or D (Plain). Diploma programmes require a minimum KCSE Mean Grade of <strong>C- (Minus)</strong> or a relevant certificate qualification. Higher Diplomas require a completed recognized Diploma.
                    </p>
                </details>

                <details class="group pt-3 cursor-pointer">
                    <summary class="flex justify-between items-center font-extrabold text-sm text-slate-900 list-none">
                        <span>Are the TVET and Degree courses accredited?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
                        Yes. All Certificate and Diploma programmes are fully accredited by <strong>TVET CDACC</strong> and aligned to the <strong>Kenya National Qualifications Authority (KNQA)</strong> framework. Bachelor degree programmes are accredited by the <strong>Commission for University Education (CUE)</strong>.
                    </p>
                </details>

                <details class="group pt-3 cursor-pointer">
                    <summary class="flex justify-between items-center font-extrabold text-sm text-slate-900 list-none">
                        <span>Can I study online through the Virtual Campus?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
                        Yes! Our Virtual Campus (ODeL) supports interactive e-learning, live lecturer sessions, and downloadable course modules with weekend evening practical laboratories.
                    </p>
                </details>

                <details class="group pt-3 cursor-pointer">
                    <summary class="flex justify-between items-center font-extrabold text-sm text-slate-900 list-none">
                        <span>How do I pay my application fee?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
                        You can pay instantly via <strong>M-Pesa STK Push</strong> within the portal, or send funds directly to Pochi la Biashara / Till <strong>0113636154</strong>, or KCB Paybill <strong>522522</strong> Account: 0113636154.
                    </p>
                </details>
            </div>
        </section>

    </main>

    {{-- Course Quick View Modal --}}
    <div id="quick-view-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-all" role="dialog" aria-modal="true">
        <div class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full overflow-hidden shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">
            <div class="relative h-44 w-full overflow-hidden bg-slate-100">
                <img id="qv-image" src="" alt="Course Preview" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/30 to-transparent"></div>
                <button type="button" onclick="closeQuickViewModal()" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/60 transition-colors cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
                <div class="absolute bottom-3 left-4 right-4 flex items-center gap-2">
                    <span id="qv-code" class="font-mono text-xs font-extrabold text-white bg-[#0A3E50]/95 px-2.5 py-0.5 rounded-md border border-white/20"></span>
                    <span id="qv-tier" class="text-[11px] font-bold text-amber-300"></span>
                </div>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div>
                    <h3 id="qv-title" class="text-lg font-extrabold text-[#0A3E50] leading-snug"></h3>
                    <p id="qv-level" class="text-[11px] text-slate-500 font-semibold mt-0.5"></p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200 space-y-1.5">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Minimum Entry Requirement</span>
                    <p id="qv-req" class="text-slate-800 font-bold text-xs"></p>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11.5px] text-slate-600">
                    <div><strong>Duration:</strong> <span id="qv-duration"></span></div>
                    <div><strong>Campus:</strong> <span id="qv-campus"></span></div>
                    <div><strong>Study Mode:</strong> <span id="qv-mode"></span></div>
                    <div><strong>Application Fee:</strong> <span id="qv-fee" class="font-extrabold text-[#0A3E50]"></span></div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeQuickViewModal()" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 cursor-pointer">Close</button>
                    <a id="qv-apply-btn" href="#" class="px-5 py-2 rounded-xl bg-[#0A3E50] hover:bg-[#E67E22] text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                        <span>Proceed to Apply</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Public Footer --}}
    <footer class="bg-[#072B38] text-white border-t border-white/10 py-12 text-xs mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white p-1 text-[#0A3E50] flex items-center justify-center font-extrabold text-base">
                        <img src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA" class="w-full h-full object-contain" onerror="this.outerHTML='<span class=\'font-extrabold text-base text-[#0A3E50]\'>M</span>'">
                    </div>
                    <span class="font-extrabold text-sm uppercase tracking-wider text-white">MEMA UNIVERSITY COLLEGE</span>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed">
                    Accredited Centre of Excellence for Technical, Vocational &amp; Higher Education in East Africa.
                </p>
                <div class="text-slate-400 text-[11.5px] space-y-1">
                    <div>P.O. Box 2490-00100, Nairobi, Kenya</div>
                    <div>Admissions Helpline: <strong class="text-white">0113636154</strong></div>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-sm text-white uppercase tracking-wider mb-3">Academic Pathways</h4>
                <ul class="space-y-2 text-slate-400 text-xs">
                    <li><a href="#programmes" onclick="filterTier('cert')" class="hover:text-amber-300 transition-colors">Certificate Programmes (KNQA 5)</a></li>
                    <li><a href="#programmes" onclick="filterTier('dip')" class="hover:text-amber-300 transition-colors">Diploma Programmes (KNQA 6)</a></li>
                    <li><a href="#programmes" onclick="filterTier('hdip')" class="hover:text-amber-300 transition-colors">Higher Diplomas (KNQA 7)</a></li>
                    <li><a href="#programmes" onclick="filterTier('deg')" class="hover:text-amber-300 transition-colors">Bachelor Degree Offerings</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-sm text-white uppercase tracking-wider mb-3">Admissions &amp; Fees</h4>
                <ul class="space-y-2 text-slate-400 text-xs">
                    <li><a href="#eligibility" class="hover:text-amber-300 transition-colors">Grade Matcher</a></li>
                    <li><a href="{{ route('admissions.brochure') }}" target="_blank" class="hover:text-amber-300 transition-colors font-bold text-teal-200">Download Brochure (PDF)</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-amber-300 transition-colors">Applicant Login Portal</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-amber-300 transition-colors">Staff ERP Access</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-sm text-white uppercase tracking-wider mb-3">Payment Methods</h4>
                <p class="text-slate-400 text-xs mb-3">Instant mobile verification enabled:</p>
                <div class="bg-white/10 p-3 rounded-xl border border-white/15 space-y-1 text-slate-300 text-[11.5px]">
                    <div>M-Pesa STK Push Automated</div>
                    <div>Pochi / Till: <strong class="text-white">0113636154</strong></div>
                    <div>KCB Paybill: <strong class="text-white">522522</strong> Acc: <strong>0113636154</strong></div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 mt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-3 text-slate-400 text-[11.5px]">
            <div>© {{ date('Y') }} MEMA College &amp; University. All rights reserved. • ISO 9001:2015 Certified System</div>
            <div class="flex items-center gap-4">
                <a href="/privacy" class="hover:text-white transition-colors">Privacy Notice</a>
                <a href="/terms" class="hover:text-white transition-colors">Terms of Admission</a>
                <a href="/cookies" class="hover:text-white transition-colors">Cookies Policy</a>
            </div>
        </div>
    </footer>

    {{-- Interactive Client Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }

            const searchInput = document.getElementById('catalogue-search-input');
            const campusSelect = document.getElementById('campus-filter');
            const modeSelect = document.getElementById('mode-filter');
            const tabButtons = document.querySelectorAll('.tier-tab-btn');
            const cards = document.querySelectorAll('.catalogue-card');
            const matchingCount = document.getElementById('matching-count');
            const activeCoursesCount = document.getElementById('active-courses-count');
            let currentTier = 'all';

            function filterCards() {
                const query = (searchInput?.value || '').toLowerCase().trim();
                const campus = (campusSelect?.value || '').toLowerCase().trim();
                const mode = (modeSelect?.value || '').toLowerCase().trim();
                let visibleCount = 0;

                cards.forEach(card => {
                    const searchData = card.dataset.search || '';
                    const cardCampus = card.dataset.campus || '';
                    const cardMode = card.dataset.mode || '';
                    const cardTier = card.dataset.tier || '';

                    const matchQuery = !query || searchData.includes(query);
                    const matchCampus = !campus || cardCampus.includes(campus);
                    const matchMode = !mode || cardMode.includes(mode);
                    const matchTier = currentTier === 'all' || cardTier === currentTier;

                    if (matchQuery && matchCampus && matchMode && matchTier) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (matchingCount) {
                    matchingCount.textContent = `Showing ${visibleCount} matching programmes`;
                }
                if (activeCoursesCount) {
                    activeCoursesCount.textContent = `${visibleCount} Programmes Displayed`;
                }
            }

            window.filterTier = function(tier) {
                tabButtons.forEach(btn => {
                    if (btn.dataset.tier === tier) {
                        tabButtons.forEach(b => {
                            b.classList.remove('active', 'bg-[#0A3E50]', 'text-white');
                            b.classList.add('bg-white', 'text-slate-700');
                        });
                        btn.classList.add('active', 'bg-[#0A3E50]', 'text-white');
                        btn.classList.remove('bg-white', 'text-slate-700');
                        currentTier = tier;
                        filterCards();
                    }
                });
            };

            window.selectGrade = function(type) {
                if (type === 'd_plus') {
                    filterTier('cert');
                } else if (type === 'c_minus') {
                    filterTier('dip');
                } else if (type === 'diploma') {
                    filterTier('hdip');
                } else if (type === 'c_plus') {
                    filterTier('deg');
                }
                const el = document.getElementById('programmes');
                if (el) el.scrollIntoView({ behavior: 'smooth' });
            };

            window.resetFilters = function() {
                if (searchInput) searchInput.value = '';
                if (campusSelect) campusSelect.value = '';
                if (modeSelect) modeSelect.value = '';
                filterTier('all');
            };

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterTier(btn.dataset.tier);
                });
            });

            searchInput?.addEventListener('input', filterCards);
            campusSelect?.addEventListener('change', filterCards);
            modeSelect?.addEventListener('change', filterCards);
        });

        // Quick View Modal Handlers from data- attributes
        function openCardQuickView(btn) {
            const card = btn.closest('.catalogue-card');
            if (!card) return;

            document.getElementById('qv-image').src = card.dataset.image;
            document.getElementById('qv-code').textContent = card.dataset.code;
            document.getElementById('qv-tier').textContent = card.dataset.tierLabel;
            document.getElementById('qv-title').textContent = card.dataset.name;
            document.getElementById('qv-level').textContent = card.dataset.levelCode;
            document.getElementById('qv-req').textContent = card.dataset.req;
            document.getElementById('qv-duration').textContent = card.dataset.duration;
            document.getElementById('qv-campus').textContent = card.dataset.offeringCampus;
            document.getElementById('qv-mode').textContent = card.dataset.offeringMode;
            document.getElementById('qv-fee').textContent = 'KES ' + card.dataset.fee;
            document.getElementById('qv-apply-btn').href = card.dataset.applyUrl;

            const modal = document.getElementById('quick-view-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeQuickViewModal() {
            const modal = document.getElementById('quick-view-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeQuickViewModal();
        });
    </script>

    {{-- World-Class Cookie Consent Hub --}}
    @include('components.cookie-consent')
</body>
</html>
