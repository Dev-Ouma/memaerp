<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic Programmes Brochure & Course Flier 2026/2027 | MEMA College & University</title>
    <meta name="description" content="Official 2026/2027 Academic Programmes Brochure & Admissions Flier. Certificate, Diploma, Higher Diploma and Bachelor Degree courses at MEMA College & University.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Quicksand', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; margin: 0 !important; }
            .print-page { page-break-after: always; break-after: page; }
            .print-card { page-break-inside: avoid; break-inside: avoid; }
            @page {
                size: A4 portrait;
                margin: 12mm 12mm 15mm 12mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen py-8 px-4 sm:px-6 font-quicksand selection:bg-[#E67E22] selection:text-white">

    {{-- Top Action Toolbar (Hidden on Print) --}}
    <div class="max-w-5xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-md flex flex-col sm:flex-row justify-between items-center gap-4 no-print sticky top-4 z-50">
        <div class="flex items-center gap-3">
            <a href="{{ route('admissions.catalogue') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all flex items-center gap-1.5 border border-slate-300">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Programmes
            </a>
            <div>
                <h1 class="font-extrabold text-sm text-slate-900 leading-tight">Official Programmes Brochure &amp; Admissions Flier</h1>
                <span class="text-[11px] text-slate-500 font-medium">Academic Year 2026/2027 • TVET CDACC &amp; CUE Accredited</span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#0A3E50] hover:bg-[#072d3a] text-white font-extrabold text-xs transition-all shadow-md flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-[#E67E22]"></i> Print / Save as PDF
            </button>
            <a href="{{ route('admissions.catalogue') }}#programmes" class="px-4.5 py-2.5 rounded-xl bg-[#E67E22] hover:bg-[#d35400] text-white font-extrabold text-xs transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="send" class="w-4 h-4"></i> Apply Online
            </a>
        </div>
    </div>

    {{-- Printable Brochure Canvas Container --}}
    <div class="max-w-5xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-6 sm:p-10 space-y-10">
        
        {{-- Flier Header / Cover Strip --}}
        <header class="border-b-4 border-[#0A3E50] pb-6 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#0A3E50] via-[#007A8C] to-[#1E8449] text-white flex items-center justify-center font-extrabold text-3xl shadow-md border-2 border-white">
                        M
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-[#0A3E50] tracking-tight uppercase leading-tight">
                            MEMA COLLEGE &amp; UNIVERSITY
                        </h2>
                        <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider mt-0.5">
                            Centre of TVET Excellence, Technical Training &amp; Higher Learning
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium">
                            Accredited by TVET CDACC, KNQA &amp; Commission for University Education (CUE)
                        </div>
                    </div>
                </div>

                <div class="text-right sm:text-right text-xs space-y-1 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="font-extrabold text-[#0A3E50] uppercase tracking-wider text-[10.5px]">Admissions Office</div>
                    <div class="font-bold text-slate-800">Helpline: 0113636154</div>
                    <div class="text-slate-600">Email: admissions@mema.ac.ke</div>
                    <div class="text-slate-600">Web: www.mema.ac.ke/programmes</div>
                </div>
            </div>

            {{-- Brochure Title Box --}}
            <div class="bg-gradient-to-r from-[#0A3E50] via-[#0d546d] to-[#007A8C] text-white rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-md">
                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-amber-300 font-extrabold text-[11px] uppercase tracking-wider">
                        2026/2027 Admissions Flier &amp; Prospectus
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                        Quality Academic &amp; Technical Programmes
                    </h3>
                    <p class="text-xs sm:text-sm text-teal-100 max-w-xl font-medium leading-relaxed">
                        Explore our market-driven <strong>Certificates</strong>, <strong>Diplomas</strong>, <strong>Higher Diplomas</strong>, and <strong>Degree Programmes</strong> designed for immediate industry employment and rapid career progression.
                    </p>
                </div>

                <div class="bg-white/15 backdrop-blur-md p-4 rounded-xl border border-white/20 text-center flex-shrink-0 space-y-1">
                    <div class="text-[10px] font-extrabold text-teal-200 uppercase tracking-wider">Next Cohort</div>
                    <div class="text-xl font-extrabold text-white">September 2026</div>
                    <div class="text-[11px] text-amber-300 font-bold">Jan • May • Sep Intakes</div>
                </div>
            </div>
        </header>

        {{-- Section 1: Overview & Academic Pathways --}}
        <section class="space-y-4">
            <div class="flex items-center gap-3 border-b-2 border-slate-200 pb-2">
                <div class="w-3 h-3 rounded-full bg-[#E67E22]"></div>
                <h4 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">1. Academic Pathways &amp; Entry Tiers</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-1.5 print-card">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-xs text-amber-900 uppercase">Certificates</span>
                        <span class="text-[10px] font-bold bg-amber-200/80 text-amber-900 px-2 py-0.5 rounded">KNQA 5</span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">KCSE D+ (Plus) or D</div>
                    <p class="text-[11px] text-slate-600 leading-snug">1 Year (2 Semesters) • Foundation technical skills • Fast-track to Diploma.</p>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-1.5 print-card">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-xs text-emerald-900 uppercase">Diplomas</span>
                        <span class="text-[10px] font-bold bg-emerald-200/80 text-emerald-900 px-2 py-0.5 rounded">KNQA 6</span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">KCSE C- (Minus) or Cert</div>
                    <p class="text-[11px] text-slate-600 leading-snug">2 Years (4 Semesters) • Practical competency • Leads to Higher Diploma/Degrees.</p>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-1.5 print-card">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-xs text-indigo-900 uppercase">Higher Diplomas</span>
                        <span class="text-[10px] font-bold bg-indigo-200/80 text-indigo-900 px-2 py-0.5 rounded">KNQA 7</span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">Recognized Diploma</div>
                    <p class="text-[11px] text-slate-600 leading-snug">1.5 Years (3 Semesters) • Advanced specialization &amp; supervisory management.</p>
                </div>

                <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-1.5 print-card">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-xs text-blue-900 uppercase">Bachelor Degrees</span>
                        <span class="text-[10px] font-bold bg-blue-200/80 text-blue-900 px-2 py-0.5 rounded">CUE / KNQA 7</span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">KCSE C+ or Diploma</div>
                    <p class="text-[11px] text-slate-600 leading-snug">4 Years (8 Semesters) • Rigorous undergraduate degrees with industry attachments.</p>
                </div>
            </div>
        </section>

        {{-- Section 2: Complete Programmes Directory Table --}}
        <section class="space-y-4 print-page">
            <div class="flex items-center gap-3 border-b-2 border-slate-200 pb-2">
                <div class="w-3 h-3 rounded-full bg-[#0A3E50]"></div>
                <h4 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">2. Full Programmes Directory &amp; Fees Schedule</h4>
            </div>

            {{-- 1. Certificate Programmes --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between bg-amber-100/90 text-amber-950 px-3.5 py-1.5 rounded-lg font-extrabold text-xs">
                    <span>A. CERTIFICATE COURSES (TVET CDACC • KNQA LEVEL 5 • DURATION: 1 YEAR)</span>
                    <span>APP FEE: KES 500</span>
                </div>
                <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px]">
                                <th class="p-2.5 pl-3">Code</th>
                                <th class="p-2.5">Programme Title</th>
                                <th class="p-2.5">Min. Entry Grade</th>
                                <th class="p-2.5">Duration</th>
                                <th class="p-2.5">Campuses</th>
                                <th class="p-2.5 pr-3 text-right">App Fee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">CERT-IT</td>
                                <td class="p-2.5 font-bold text-slate-900">Certificate in Information Technology</td>
                                <td class="p-2.5 text-slate-700">KCSE D+ (Plus)</td>
                                <td class="p-2.5 text-slate-600">1 Year (2 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 500</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">CERT-BM</td>
                                <td class="p-2.5 font-bold text-slate-900">Certificate in Business Management</td>
                                <td class="p-2.5 text-slate-700">KCSE D+ (Plus)</td>
                                <td class="p-2.5 text-slate-600">1 Year (2 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 500</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">CERT-HRIT</td>
                                <td class="p-2.5 font-bold text-slate-900">Certificate in Health Records &amp; Information Tech</td>
                                <td class="p-2.5 text-slate-700">KCSE D+ (Plus)</td>
                                <td class="p-2.5 text-slate-600">1 Year (2 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, Nairobi CBD</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 500</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">CERT-PS</td>
                                <td class="p-2.5 font-bold text-slate-900">Certificate in Public Administration &amp; Governance</td>
                                <td class="p-2.5 text-slate-700">KCSE D+ (Plus)</td>
                                <td class="p-2.5 text-slate-600">1 Year (2 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 500</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Diploma Programmes --}}
            <div class="space-y-2 pt-3">
                <div class="flex items-center justify-between bg-emerald-100/90 text-emerald-950 px-3.5 py-1.5 rounded-lg font-extrabold text-xs">
                    <span>B. DIPLOMA COURSES (TVET / KNQA LEVEL 6 • DURATION: 2 YEARS)</span>
                    <span>APP FEE: KES 750</span>
                </div>
                <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px]">
                                <th class="p-2.5 pl-3">Code</th>
                                <th class="p-2.5">Programme Title</th>
                                <th class="p-2.5">Min. Entry Grade</th>
                                <th class="p-2.5">Duration</th>
                                <th class="p-2.5">Campuses</th>
                                <th class="p-2.5 pr-3 text-right">App Fee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">DIP-IT</td>
                                <td class="p-2.5 font-bold text-slate-900">Diploma in Information Technology</td>
                                <td class="p-2.5 text-slate-700">KCSE C- (Minus) or Cert</td>
                                <td class="p-2.5 text-slate-600">2 Years (4 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 750</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">DIP-CS</td>
                                <td class="p-2.5 font-bold text-slate-900">Diploma in Computer Science</td>
                                <td class="p-2.5 text-slate-700">KCSE C- (Minus) or Cert</td>
                                <td class="p-2.5 text-slate-600">2 Years (4 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 750</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">DIP-BM</td>
                                <td class="p-2.5 font-bold text-slate-900">Diploma in Business Management</td>
                                <td class="p-2.5 text-slate-700">KCSE C- (Minus) or Cert</td>
                                <td class="p-2.5 text-slate-600">2 Years (4 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 750</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">DIP-CHD</td>
                                <td class="p-2.5 font-bold text-slate-900">Diploma in Community Health &amp; Development</td>
                                <td class="p-2.5 text-slate-700">KCSE C- (Minus) or Cert</td>
                                <td class="p-2.5 text-slate-600">2 Years (4 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 750</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">DIP-PSCM</td>
                                <td class="p-2.5 font-bold text-slate-900">Diploma in Procurement &amp; Supply Chain Management</td>
                                <td class="p-2.5 text-slate-700">KCSE C- (Minus) or Cert</td>
                                <td class="p-2.5 text-slate-600">2 Years (4 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 750</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Higher Diploma Programmes --}}
            <div class="space-y-2 pt-3">
                <div class="flex items-center justify-between bg-indigo-100/90 text-indigo-950 px-3.5 py-1.5 rounded-lg font-extrabold text-xs">
                    <span>C. HIGHER DIPLOMA COURSES (KNQA LEVEL 7 • DURATION: 1.5 YEARS)</span>
                    <span>APP FEE: KES 1,000</span>
                </div>
                <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px]">
                                <th class="p-2.5 pl-3">Code</th>
                                <th class="p-2.5">Programme Title</th>
                                <th class="p-2.5">Min. Entry Requirement</th>
                                <th class="p-2.5">Duration</th>
                                <th class="p-2.5">Campuses</th>
                                <th class="p-2.5 pr-3 text-right">App Fee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">HDIP-CS</td>
                                <td class="p-2.5 font-bold text-slate-900">Higher Diploma in Computer Systems &amp; Cybersecurity</td>
                                <td class="p-2.5 text-slate-700">Relevant Diploma / KNQA 6</td>
                                <td class="p-2.5 text-slate-600">1.5 Yrs (3 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,000</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">HDIP-HRM</td>
                                <td class="p-2.5 font-bold text-slate-900">Higher Diploma in Human Resource Management</td>
                                <td class="p-2.5 text-slate-700">Relevant Diploma / KNQA 6</td>
                                <td class="p-2.5 text-slate-600">1.5 Yrs (3 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,000</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">HDIP-PH</td>
                                <td class="p-2.5 font-bold text-slate-900">Higher Diploma in Public Health &amp; Epidemiology</td>
                                <td class="p-2.5 text-slate-700">Relevant Health Diploma</td>
                                <td class="p-2.5 text-slate-600">1.5 Yrs (3 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, Nairobi CBD</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. Bachelor Degree Programmes --}}
            <div class="space-y-2 pt-3">
                <div class="flex items-center justify-between bg-blue-100/90 text-blue-950 px-3.5 py-1.5 rounded-lg font-extrabold text-xs">
                    <span>D. BACHELOR DEGREE PROGRAMMES (CUE APPROVED • DURATION: 4 YEARS)</span>
                    <span>APP FEE: KES 1,500</span>
                </div>
                <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px]">
                                <th class="p-2.5 pl-3">Code</th>
                                <th class="p-2.5">Programme Title</th>
                                <th class="p-2.5">Min. Entry Grade</th>
                                <th class="p-2.5">Duration</th>
                                <th class="p-2.5">Campuses</th>
                                <th class="p-2.5 pr-3 text-right">App Fee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">BCS</td>
                                <td class="p-2.5 font-bold text-slate-900">Bachelor of Science in Computer Science</td>
                                <td class="p-2.5 text-slate-700">KCSE C+ (Plus) or Diploma</td>
                                <td class="p-2.5 text-slate-600">4 Years (8 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main Campus, CBD</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,500</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">BBA</td>
                                <td class="p-2.5 font-bold text-slate-900">Bachelor of Business Administration</td>
                                <td class="p-2.5 text-slate-700">KCSE C+ (Plus) or Diploma</td>
                                <td class="p-2.5 text-slate-600">4 Years (8 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main, CBD, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,500</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 pl-3 font-mono font-bold text-[#0A3E50]">BSE</td>
                                <td class="p-2.5 font-bold text-slate-900">Bachelor of Software Engineering</td>
                                <td class="p-2.5 text-slate-700">KCSE C+ (Plus) or Diploma</td>
                                <td class="p-2.5 text-slate-600">4 Years (8 Sem)</td>
                                <td class="p-2.5 text-slate-600">Main Campus, Virtual</td>
                                <td class="p-2.5 pr-3 text-right font-bold text-slate-900">KES 1,500</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Section 3: How to Apply & Payment Details --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t-2 border-slate-200 print-card">
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                <h4 class="font-extrabold text-sm text-[#0A3E50] uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="compass" class="w-4 h-4 text-[#E67E22]"></i> Easy 4-Step Application
                </h4>
                <ol class="space-y-2 text-xs text-slate-700 font-medium list-decimal list-inside leading-relaxed">
                    <li><strong>Online Registration:</strong> Visit <code class="text-[#0A3E50] font-mono font-bold">http://localhost:8000/programmes/apply</code> and choose your course.</li>
                    <li><strong>Bio-Data &amp; Documents:</strong> Enter your personal bio-data and upload KCSE/Diploma certificates.</li>
                    <li><strong>Fee Payment:</strong> Pay the required fee via M-Pesa STK or Bank Paybill to validate application.</li>
                    <li><strong>Instant Admission Letter:</strong> Download your official Admission Letter with QR code verification.</li>
                </ol>
            </div>

            <div class="bg-teal-50/70 p-5 rounded-2xl border border-teal-200 space-y-3">
                <h4 class="font-extrabold text-sm text-[#0A3E50] uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="smartphone" class="w-4 h-4 text-[#1E8449]"></i> Official Payment Channels
                </h4>
                <div class="space-y-2 text-xs text-slate-700">
                    <div class="p-2.5 bg-white rounded-xl border border-teal-100 flex justify-between items-center">
                        <span><strong>M-Pesa STK Push:</strong> Automatic in portal</span>
                        <span class="font-bold text-emerald-700">Instant</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-teal-100 flex justify-between items-center">
                        <span><strong>Pochi / Buy Goods Till:</strong> 0113636154</span>
                        <span class="font-bold text-[#0A3E50]">MEMA Admissions</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-teal-100 flex justify-between items-center">
                        <span><strong>KCB Paybill:</strong> 522522 • Acc: 0113636154</span>
                        <span class="font-bold text-[#0A3E50]">KCB Bank</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Flier Footer --}}
        <footer class="pt-6 border-t-2 border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <div>
                <strong class="text-slate-800">MEMA COLLEGE &amp; UNIVERSITY</strong> • Admissions Office
                <div class="text-[11px] text-slate-400">P.O. Box 2490-00100, Nairobi • Helpline: 0113636154 • Email: admissions@mema.ac.ke</div>
            </div>
            <div class="text-right text-[11px] text-slate-400">
                <span>ISO 9001:2015 Certified • TVET CDACC &amp; CUE Approved</span><br>
                <span>Document Reference: MEMA-BROCHURE-2026-V1</span>
            </div>
        </footer>

    </div>

    {{-- Interactive Lucide Icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
