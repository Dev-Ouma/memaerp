@extends('layouts.app')

@section('title', 'Reports Dashboard')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Admissions Reports Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Admissions report centre · Real-time intake analytics, statutory reporting, capacity utilisation, and trend metrics</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admissions.reports.applications') }}" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-2" style="color: #ffffff !important; text-decoration: none !important;">
                <i data-lucide="download" class="w-3.5 h-3.5" style="color: #ffffff !important;"></i>
                <span style="color: #ffffff !important;">Export Master Register (CSV)</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                Print Summary
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Applications</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format($reportStats['applications']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current September 2026 intake.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">&uarr; +18.4% vs Last Year</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Verified &amp; Shortlisted</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($reportStats['verified']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Met academic entry criteria.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">74.2% Stage Velocity</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Offers &amp; Acceptance Yield</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ number_format($reportStats['offers']) }} Offers</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">{{ number_format($reportStats['accepted']) }} accepted offers.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">{{ $reportStats['yieldRate'] }} Yield Rate</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Application Revenue</div>
            <div class="text-3xl font-extrabold text-[#1E8449] mt-2 mb-1.5 leading-none">KES {{ number_format($reportStats['revenue']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">M-Pesa &amp; Bank Collections.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">100% Settled</span></div>
        </div>
    </div>

    {{-- Report Cards Grid (Pure Admissions Domain - Click 'View Report' to view in-page below) --}}
    <div class="mb-8">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Admissions Operational &amp; Governance Reports</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Card 1: APPLICANT PIPELINE REGISTER --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-emerald-700 uppercase">APPLICANT PIPELINE REGISTER</span>
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Full applicant dossier register with contact details, verification status, and submission dates.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('pipeline')" class="px-3.5 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="search" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 2: DOCUMENT VERIFICATION AUDIT --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-sky-600 uppercase">DOCUMENT VERIFICATION AUDIT</span>
                        <i data-lucide="file-check-2" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Inspect verified, pending, and flagged KCSE certificates, result slips, and ID documents.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('documents')" class="px-3.5 py-1.5 rounded-md bg-[#0284c7] hover:bg-[#0369a1] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="search" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 3: PROGRAMME QUOTAS & CAPACITY --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-blue-600 uppercase">PROGRAMME QUOTA &amp; CAPACITY</span>
                        <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">View intake capacity utilisation, applied vs admitted candidates, and remaining vacancies.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('quotas')" class="px-3.5 py-1.5 rounded-md bg-[#2563eb] hover:bg-[#1d4ed8] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="bar-chart-2" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 4: MERIT RANKING & CUT-OFFS --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-amber-600 uppercase">MERIT CUT-OFFS &amp; SELECTION</span>
                        <i data-lucide="award" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Analyze cluster point thresholds, mean grade distribution, and shortlisted merit rankings.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('merit')" class="px-3.5 py-1.5 rounded-md bg-[#d97706] hover:bg-[#b45309] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="search" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 5: ADMISSION OFFERS & YIELD --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-indigo-600 uppercase">ADMISSION OFFERS &amp; YIELD</span>
                        <i data-lucide="mail-check" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Track issued admission offer letters, acceptance response rates, and reporting deadlines.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('offers')" class="px-3.5 py-1.5 rounded-md bg-[#6366f1] hover:bg-[#4f46e5] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="eye" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 6: APPLICATION FEE SETTLEMENTS --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-emerald-700 uppercase">APPLICATION FEE SETTLEMENTS</span>
                        <i data-lucide="wallet" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Reconcile Safaricom Daraja 2.0 M-Pesa receipts, bank slips, and applicant payment attempts.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('payments')" class="px-3.5 py-1.5 rounded-md bg-[#059669] hover:bg-[#047857] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="search" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 7: STUDENT MATRICULATION & ROLLS --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-purple-700 uppercase">MATRICULATION &amp; STUDENT ROLLS</span>
                        <i data-lucide="user-check" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">View official student conversion ledger and allocated university registration numbers.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('conversions')" class="px-3.5 py-1.5 rounded-md bg-[#9333ea] hover:bg-[#7e22ce] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="user-check" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>

            {{-- Card 8: STATUTORY CUE & KUCCPS RETURNS --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between report-card">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-slate-700 uppercase">STATUTORY CUE &amp; KUCCPS RETURNS</span>
                        <i data-lucide="landmark" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">Generate official statutory regulatory returns for the Commission for University Education.</p>
                </div>
                <div>
                    <button type="button" onclick="switchReport('statutory')" class="px-3.5 py-1.5 rounded-md bg-[#475569] hover:bg-[#334155] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer" style="color:#ffffff !important;">
                        <i data-lucide="file-text" class="w-3.5 h-3.5" style="color:#ffffff !important;"></i>
                        <span style="color:#ffffff !important;">View Report</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Charts & Trend Lines Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Chart 1: Application Intake Velocity Trend Line (2 cols) --}}
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-6 shadow-xs relative flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xs font-extrabold text-[#0A3E50] uppercase tracking-wider">Application Intake Velocity &amp; Monthly Trend Line</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-200 inline-flex items-center gap-1 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Peak: July (320 Apps &middot; +28.4%)
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 font-medium">Smooth cubic spline curve of monthly submissions vs final admitted enrolments</p>
                    </div>

                    {{-- Interactive Series Toggles --}}
                    <div class="flex items-center gap-2 text-xs self-end sm:self-center shrink-0">
                        <button type="button" id="toggleSeriesApps" onclick="toggleChartSeries('apps')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#0A3E50] text-white shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer" style="color: #ffffff !important;">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Applications</span>
                        </button>
                        <button type="button" id="toggleSeriesAdm" onclick="toggleChartSeries('adm')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#1E8449] text-white shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer" style="color: #ffffff !important;">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Admitted</span>
                        </button>
                    </div>
                </div>

                {{-- SVG Smooth Bézier Curve Chart Container --}}
                <div class="relative w-full h-64 select-none" id="chartWrapper" onmouseleave="hideChartTooltip()">
                    {{-- Interactive Floating Tooltip (Smart lateral positioning, never overlaps header) --}}
                    <div id="chartTooltip" class="absolute pointer-events-none z-30 hidden transition-all duration-100 bg-slate-900/95 backdrop-blur-md text-white px-3.5 py-2.5 rounded-xl shadow-2xl border border-slate-700 text-xs min-w-48">
                        <div id="tooltipMonth" class="font-extrabold text-slate-200 border-b border-slate-700/80 pb-1.5 mb-1.5 text-[11.5px] flex justify-between items-center">
                            <span>July 2026</span>
                            <span class="text-[10.5px] text-emerald-400 font-mono font-bold">+28.4%</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-300 flex items-center gap-1.5 text-[11px]">
                                    <span class="w-2 h-2 rounded-full bg-teal-400"></span> Applications
                                </span>
                                <span id="tooltipApps" class="font-mono font-extrabold text-white text-xs">320</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-300 flex items-center gap-1.5 text-[11px]">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Admitted
                                </span>
                                <span id="tooltipAdm" class="font-mono font-extrabold text-emerald-300 text-xs">210</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 pt-1.5 mt-1 border-t border-slate-800 text-[10.5px] text-slate-400 font-mono">
                                <span>Fee Revenue</span>
                                <span id="tooltipRev" class="text-amber-300 font-bold">KES 320,000</span>
                            </div>
                        </div>
                    </div>

                    {{-- SVG Canvas --}}
                    <svg class="w-full h-full overflow-visible" viewBox="0 0 650 200" preserveAspectRatio="none">
                        <defs>
                            {{-- Applications Gradient Fill --}}
                            <linearGradient id="appSmoothGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0A3E50" stop-opacity="0.28"/>
                                <stop offset="70%" stop-color="#0A3E50" stop-opacity="0.06"/>
                                <stop offset="100%" stop-color="#0A3E50" stop-opacity="0.0"/>
                            </linearGradient>

                            {{-- Admitted Gradient Fill --}}
                            <linearGradient id="admSmoothGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1E8449" stop-opacity="0.32"/>
                                <stop offset="70%" stop-color="#1E8449" stop-opacity="0.08"/>
                                <stop offset="100%" stop-color="#1E8449" stop-opacity="0.0"/>
                            </linearGradient>

                            {{-- Glow Filters --}}
                            <filter id="glowApps" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#0A3E50" flood-opacity="0.35"/>
                            </filter>
                            <filter id="glowAdm" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#1E8449" flood-opacity="0.35"/>
                            </filter>
                        </defs>

                        {{-- Horizontal Y-Gridlines & Clear Numbers with Adequate Left Margin --}}
                        <g class="grid-lines">
                            <line x1="42" y1="25" x2="630" y2="25" stroke="#f1f5f9" stroke-width="1.5" stroke-dasharray="4"/>
                            <text x="34" y="29" font-size="10" fill="#94a3b8" font-family="monospace" font-weight="600" text-anchor="end">350</text>

                            <line x1="42" y1="72" x2="630" y2="72" stroke="#f1f5f9" stroke-width="1.5" stroke-dasharray="4"/>
                            <text x="34" y="76" font-size="10" fill="#94a3b8" font-family="monospace" font-weight="600" text-anchor="end">250</text>

                            <line x1="42" y1="120" x2="630" y2="120" stroke="#f1f5f9" stroke-width="1.5" stroke-dasharray="4"/>
                            <text x="34" y="124" font-size="10" fill="#94a3b8" font-family="monospace" font-weight="600" text-anchor="end">150</text>

                            <line x1="42" y1="168" x2="630" y2="168" stroke="#e2e8f0" stroke-width="1.5"/>
                            <text x="34" y="172" font-size="10" fill="#94a3b8" font-family="monospace" font-weight="600" text-anchor="end">50</text>
                        </g>

                        {{-- Vertical Cursor Guideline --}}
                        <line id="cursorGuideLine" x1="0" y1="15" x2="0" y2="175" stroke="#0A3E50" stroke-width="1.5" stroke-dasharray="3 3" opacity="0" class="transition-opacity duration-150"/>

                        {{-- Series 1: Applications Area & Smooth Spline Path --}}
                        <g id="seriesAppsGroup" class="transition-opacity duration-300">
                            <path d="M 55,175 L 55,142 C 100,135 130,125 165,118 C 205,110 240,95 275,84 C 315,72 350,52 385,48 C 425,44 460,26 495,30 C 535,34 570,60 605,72 L 605,175 Z" fill="url(#appSmoothGradient)"/>
                            <path d="M 55,142 C 100,135 130,125 165,118 C 205,110 240,95 275,84 C 315,72 350,52 385,48 C 425,44 460,26 495,30 C 535,34 570,60 605,72" fill="none" stroke="#0A3E50" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" filter="url(#glowApps)"/>
                        </g>

                        {{-- Series 2: Admitted Area & Smooth Spline Path --}}
                        <g id="seriesAdmGroup" class="transition-opacity duration-300">
                            <path d="M 55,175 L 55,162 C 100,158 130,154 165,150 C 205,145 240,138 275,126 C 315,112 350,95 385,88 C 425,80 460,68 495,65 C 535,62 570,72 605,78 L 605,175 Z" fill="url(#admSmoothGradient)"/>
                            <path d="M 55,162 C 100,158 130,154 165,150 C 205,145 240,138 275,126 C 315,112 350,95 385,88 C 425,80 460,68 495,65 C 535,62 570,72 605,78" fill="none" stroke="#1E8449" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" filter="url(#glowAdm)"/>
                        </g>

                        {{-- Interactive Touch/Hover Column Zones --}}
                        {{-- March (x=55) --}}
                        <rect x="0" y="0" width="110" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(0, 55, 142, 162)"/>
                        {{-- April (x=165) --}}
                        <rect x="110" y="0" width="110" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(1, 165, 118, 150)"/>
                        {{-- May (x=275) --}}
                        <rect x="220" y="0" width="110" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(2, 275, 84, 126)"/>
                        {{-- June (x=385) --}}
                        <rect x="330" y="0" width="110" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(3, 385, 48, 88)"/>
                        {{-- July (x=495) --}}
                        <rect x="440" y="0" width="110" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(4, 495, 30, 65)"/>
                        {{-- August (x=605) --}}
                        <rect x="550" y="0" width="100" height="200" fill="transparent" class="cursor-pointer" onmouseenter="showChartTooltip(5, 605, 72, 78)"/>

                        {{-- Data Circles on Applications --}}
                        <g id="appPointsGroup">
                            <circle cx="55" cy="142" r="4.5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                            <circle cx="165" cy="118" r="4.5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                            <circle cx="275" cy="84" r="4.5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                            <circle cx="385" cy="48" r="4.5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                            {{-- Peak July Glowing Halo Circle --}}
                            <circle cx="495" cy="30" r="9" fill="#0A3E50" fill-opacity="0.25" class="animate-pulse"/>
                            <circle cx="495" cy="30" r="5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                            <circle cx="605" cy="72" r="4.5" fill="#0A3E50" stroke="#ffffff" stroke-width="2.5"/>
                        </g>

                        {{-- Data Circles on Admitted --}}
                        <g id="admPointsGroup">
                            <circle cx="55" cy="162" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="165" cy="150" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="275" cy="126" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="385" cy="88" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="495" cy="65" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="605" cy="78" r="4" fill="#1E8449" stroke="#ffffff" stroke-width="2"/>
                        </g>
                    </svg>

                    {{-- X-Axis Labels --}}
                    <div class="flex justify-between text-[11px] font-bold text-slate-500 mt-2 px-6">
                        <span class="cursor-pointer hover:text-[#0A3E50] transition-colors" onclick="showChartTooltip(0, 55, 142, 162)">March</span>
                        <span class="cursor-pointer hover:text-[#0A3E50] transition-colors" onclick="showChartTooltip(1, 165, 118, 150)">April</span>
                        <span class="cursor-pointer hover:text-[#0A3E50] transition-colors" onclick="showChartTooltip(2, 275, 84, 126)">May</span>
                        <span class="cursor-pointer hover:text-[#0A3E50] transition-colors" onclick="showChartTooltip(3, 385, 48, 88)">June</span>
                        <span class="cursor-pointer text-[#0A3E50] font-extrabold flex items-center gap-1 transition-colors" onclick="showChartTooltip(4, 495, 30, 65)">
                            July <span class="w-1.5 h-1.5 rounded-full bg-[#0A3E50]"></span>
                        </span>
                        <span class="cursor-pointer hover:text-[#0A3E50] transition-colors" onclick="showChartTooltip(5, 605, 72, 78)">August</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Funnel & Lifecycle Stage Distribution (1 col) --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wider">Admission Funnel Distribution</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Applicant flow across lifecycle stages</p>
                </div>

                <div class="space-y-3.5 text-xs">
                    @foreach($statusBreakdown as $stage)
                        <div>
                            <div class="flex justify-between items-center mb-1 font-medium">
                                <span class="text-slate-800">{{ $stage['label'] }}</span>
                                <span class="font-mono font-bold text-slate-900">{{ number_format($stage['count']) }} ({{ $stage['percent'] }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width: {{ $stage['percent'] }}%; background-color: {{ $stage['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between items-center">
                <span>Overall Conversion Rate</span>
                <span class="font-extrabold text-[#1E8449] text-xs">{{ $reportStats['conversionRate'] }}</span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- IN-PAGE INTERACTIVE REPORT VIEWER (SEAMLESS IN-PAGE LIVE SEARCH & FILTER) --}}
    {{-- ========================================================================= --}}
    <div id="interactiveReportContainer" class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-8 scroll-mt-6">
        {{-- Report Card Header --}}
        <div class="p-6 pb-4 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h2 id="activeReportTitle" class="text-xl font-bold text-slate-900">Applicant Pipeline Register</h2>
                
                {{-- In-Page Report Switcher Quick Tabs --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" onclick="switchReport('pipeline')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-[#0A3E50] text-white" data-target="pipeline">Pipeline</button>
                    <button type="button" onclick="switchReport('documents')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="documents">Documents</button>
                    <button type="button" onclick="switchReport('quotas')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="quotas">Quotas</button>
                    <button type="button" onclick="switchReport('merit')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="merit">Merit Ranking</button>
                    <button type="button" onclick="switchReport('offers')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="offers">Offers</button>
                    <button type="button" onclick="switchReport('payments')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="payments">Payments</button>
                    <button type="button" onclick="switchReport('conversions')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="conversions">Matriculation</button>
                    <button type="button" onclick="switchReport('statutory')" class="report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="statutory">Statutory</button>
                </div>
            </div>

            {{-- Filter Bar (Matching reference design) --}}
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mt-4 items-end">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Academic Session / Intake</label>
                    <select id="sessionFilter" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-800 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="September 2026 Intake" selected>September 2026 Intake</option>
                        <option value="May-August 2026">May-August 2026</option>
                        <option value="January-April 2026">January-April 2026</option>
                        <option value="All Sessions">All Academic Sessions</option>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">School / Faculty (Optional)</label>
                    <select id="schoolFilter" onchange="filterActiveReport()" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-800 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">All Schools</option>
                        <option value="School of Computing">School of Computing</option>
                        <option value="School of Business">School of Business</option>
                        <option value="School of Health">School of Health</option>
                        <option value="School of Engineering">School of Engineering</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <button type="button" onclick="filterActiveReport()" class="w-full py-2 rounded-lg bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold text-xs transition-colors shadow-2xs text-center" style="color: #ffffff !important;">
                        Filter
                    </button>
                </div>

                <div class="sm:col-span-2">
                    <button type="button" onclick="resetReportFilters()" class="w-full py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors text-center">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Live Instant Search & Action Buttons (Export Excel & Export PDF) --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mt-4 pt-3 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    {{-- Export Excel Button (Green #1E8449) --}}
                    <button type="button" onclick="exportTableToCSV()" class="px-3.5 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors" style="color: #ffffff !important;">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5" style="color: #ffffff !important;"></i>
                        <span style="color: #ffffff !important;">Export Excel</span>
                    </button>

                    {{-- Export PDF Button (Red #dc2626) --}}
                    <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-md bg-[#dc2626] hover:bg-[#b91c1c] font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors" style="color: #ffffff !important;">
                        <i data-lucide="file-text" class="w-3.5 h-3.5" style="color: #ffffff !important;"></i>
                        <span style="color: #ffffff !important;">Export PDF</span>
                    </button>
                </div>

                {{-- Live Search Input --}}
                <div class="relative w-full sm:w-80">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" id="liveSearchInput" onkeyup="searchActiveReport()" placeholder="Search applicants, refs, courses, dates..." class="w-full pl-9 pr-3 py-1.5 rounded-md border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
        </div>

        {{-- In-Page Report Tables --}}
        <div class="overflow-x-auto">
            {{-- 1. APPLICANT PIPELINE REGISTER TABLE --}}
            <table id="table-pipeline" class="report-table w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">App Reference</th>
                        <th class="py-3 px-4 text-slate-900">Applicant Name &amp; Contact</th>
                        <th class="py-3 px-4 text-slate-900">Programme of Study</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Fee Payment</th>
                        <th class="py-3 px-4 text-slate-900 text-center w-32">Stage Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($pipelineReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $row['ref'] }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $row['name'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $row['email'] }} &middot; {{ $row['phone'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $row['programme'] }} ({{ $row['campus'] }})</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ $row['payment'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold
                                    @if($row['status'] === 'ENROLLED') bg-emerald-100 text-emerald-800
                                    @elseif(in_array($row['status'], ['ADMITTED', 'READY_TO_ENROL', 'ACCEPTED'])) bg-blue-100 text-blue-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ str_replace('_', ' ', $row['status']) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 2. DOCUMENT VERIFICATION AUDIT TABLE --}}
            <table id="table-documents" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">App Ref</th>
                        <th class="py-3 px-4 text-slate-900">Applicant Name</th>
                        <th class="py-3 px-4 text-slate-900">Document Type</th>
                        <th class="py-3 px-4 text-slate-900">SHA-256 Checksum &amp; Audit Note</th>
                        <th class="py-3 px-4 text-slate-900 text-center w-28">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($documentAuditReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $row['ref'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $row['doc_type'] }}</td>
                            <td class="py-3 px-4 text-slate-600">
                                <span class="font-mono text-[10px] text-slate-400 block">{{ substr($row['sha256'], 0, 24) }}...</span>
                                <span class="text-[11px] text-slate-700">{{ $row['note'] }} ({{ $row['verified_by'] }})</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($row['status'] === 'VERIFIED') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 3. PROGRAMME CAPACITY & QUOTAS TABLE --}}
            <table id="table-quotas" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 text-slate-900">Programme Code &amp; Title</th>
                        <th class="py-3 px-4 text-slate-900">School</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Target</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Applied</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Admitted</th>
                        <th class="py-3 px-4 text-slate-900 w-44">Capacity Fill Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($programmeQuotas as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row" data-school="{{ $row['school'] }}">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <span class="font-mono text-[11px] font-extrabold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded mr-1">{{ $row['code'] }}</span>
                                {{ $row['name'] }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $row['school'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ $row['capacity'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-800">{{ $row['applied'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-emerald-700">{{ $row['admitted'] }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#1E8449]" style="width: {{ min(100, $row['fill']) }}%;"></div>
                                    </div>
                                    <span class="font-mono font-bold text-[11px] text-slate-700">{{ $row['fill'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 4. MERIT CUT-OFFS & SELECTION TABLE --}}
            <table id="table-merit" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">Candidate Name</th>
                        <th class="py-3 px-4 text-slate-900">Mean Grade</th>
                        <th class="py-3 px-4 text-slate-900">Cluster Points / Cut-Off</th>
                        <th class="py-3 px-4 text-slate-900">Target Programme</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Outcome</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($meritCutoffsReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-purple-900">{{ $row['mean_grade'] }}</td>
                            <td class="py-3 px-4 text-slate-700">
                                <strong>{{ $row['cluster'] }}</strong> (Cut-off: {{ $row['cutoff'] }})
                                <span class="text-emerald-700 font-bold text-[11px] ml-1">{{ $row['variance'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $row['programme'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">
                                    {{ $row['outcome'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 5. ADMISSION OFFERS & YIELD TABLE --}}
            <table id="table-offers" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">Offer Ref</th>
                        <th class="py-3 px-4 text-slate-900">Applicant Name</th>
                        <th class="py-3 px-4 text-slate-900">Offered Programme</th>
                        <th class="py-3 px-4 text-slate-900">Issued &amp; Deadline</th>
                        <th class="py-3 px-4 text-slate-900 text-center w-36">Response Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($offersReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-purple-900">{{ $row['offer_ref'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $row['programme'] }}</td>
                            <td class="py-3 px-4 text-slate-700">
                                <div>Issued: <strong>{{ $row['issued_date'] }}</strong></div>
                                <div class="text-[10.5px] text-slate-400">Deadline: {{ $row['deadline'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 6. APPLICATION FEE PAYMENTS TABLE --}}
            <table id="table-payments" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">Transaction ID</th>
                        <th class="py-3 px-4 text-slate-900">Applicant Name</th>
                        <th class="py-3 px-4 text-slate-900">Channel / Receipt</th>
                        <th class="py-3 px-4 text-slate-900 font-bold text-right">Amount</th>
                        <th class="py-3 px-4 text-slate-900 text-center w-28">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($paymentBatchesReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $row['trans_id'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-slate-700">
                                <div>{{ $row['channel'] }} &middot; {{ $row['phone'] }}</div>
                                <div class="font-mono text-[10px] text-slate-400">Receipt: {{ $row['receipt'] }} &middot; {{ $row['date'] }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-extrabold text-[#1E8449] text-right">{{ $row['amount'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 7. STUDENT MATRICULATION & ROLLS TABLE --}}
            <table id="table-conversions" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">Student Reg No</th>
                        <th class="py-3 px-4 text-slate-900">Student Name</th>
                        <th class="py-3 px-4 text-slate-900">Programme</th>
                        <th class="py-3 px-4 text-slate-900">Faculty / School</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Matriculation Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($conversionsReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row" data-school="{{ $row['school'] }}">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50] bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200">{{ $row['student_no'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $row['programme'] }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $row['school'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($row['status'] === 'ACTIVE STUDENT') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 8. STATUTORY CUE & KUCCPS RETURNS TABLE --}}
            <table id="table-statutory" class="report-table w-full text-left border-collapse text-xs hidden">
                <thead>
                    <tr class="bg-white border-b border-slate-200 text-slate-800 font-bold">
                        <th class="py-3 px-4 w-12 text-slate-900">#</th>
                        <th class="py-3 px-4 text-slate-900">Accredited Programme</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Male</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Female</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Special Needs</th>
                        <th class="py-3 px-4 text-slate-900 text-center">Counties Represented</th>
                        <th class="py-3 px-4 text-slate-900 text-center font-bold">Total Matriculated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($statutoryReturnsReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $row['id'] }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div>{{ $row['programme'] }}</div>
                                <div class="text-[10.5px] text-slate-500 font-normal">{{ $row['accreditation'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">{{ $row['male'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">{{ $row['female'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-purple-700">{{ $row['special_needs'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-700">{{ $row['counties'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-[#1E8449] text-sm">{{ $row['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Interactive JavaScript for Instant In-Page Switching, Filtering, and Search --}}
<script>
    let currentReportKey = 'pipeline';

    const reportTitles = {
        'pipeline': 'Applicant Pipeline & Status Register',
        'documents': 'Document Verification & KNEC Audit',
        'quotas': 'Programme Capacity & Quotas',
        'merit': 'Merit Cut-Offs & Selection Matrix',
        'offers': 'Admission Offers & Yield Registry',
        'payments': 'Application Fee Settlements & M-Pesa Ledger',
        'conversions': 'Matriculation & Student Conversion Rolls',
        'statutory': 'Statutory CUE & KUCCPS Returns'
    };

    function switchReport(key) {
        currentReportKey = key;

        // Update Title
        const titleElem = document.getElementById('activeReportTitle');
        if (titleElem && reportTitles[key]) {
            titleElem.textContent = reportTitles[key];
        }

        // Switch visible table
        document.querySelectorAll('.report-table').forEach(t => t.classList.add('hidden'));
        const targetTable = document.getElementById('table-' + key);
        if (targetTable) {
            targetTable.classList.remove('hidden');
        }

        // Update quick tabs
        document.querySelectorAll('.report-tab-btn').forEach(btn => {
            if (btn.dataset.target === key) {
                btn.className = 'report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-[#0A3E50] text-white';
            } else {
                btn.className = 'report-tab-btn px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200';
            }
        });

        // Reset search input and perform search/filter
        const searchInput = document.getElementById('liveSearchInput');
        if (searchInput) searchInput.value = '';
        filterActiveReport();

        // Smooth scroll to container
        const container = document.getElementById('interactiveReportContainer');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function searchActiveReport() {
        const query = (document.getElementById('liveSearchInput')?.value || '').toLowerCase().trim();
        const activeTable = document.getElementById('table-' + currentReportKey);
        if (!activeTable) return;

        const schoolVal = document.getElementById('schoolFilter')?.value || '';
        const rows = activeTable.querySelectorAll('tbody tr.report-row');

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rowSchool = row.dataset.school || '';

            const matchesSchool = !schoolVal || rowSchool === schoolVal;
            const matchesQuery = !query || rowText.includes(query);

            if (matchesSchool && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterActiveReport() {
        searchActiveReport();
    }

    function resetReportFilters() {
        const schoolSelect = document.getElementById('schoolFilter');
        if (schoolSelect) schoolSelect.value = '';
        const searchInput = document.getElementById('liveSearchInput');
        if (searchInput) searchInput.value = '';
        filterActiveReport();
    }

    function exportTableToCSV() {
        const activeTable = document.getElementById('table-' + currentReportKey);
        if (!activeTable) return;

        let csv = [];
        const rows = activeTable.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cols = row.querySelectorAll('th, td');
                const rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
                    rowData.push('"' + text + '"');
                });
                csv.push(rowData.join(','));
            }
        });

        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${currentReportKey}_report_${new Date().toISOString().slice(0,10)}.csv`;
        link.click();
    }

    // Chart Interactive Telemetry & Tooltips
    const monthlyChartData = [
        { month: 'March 2026', apps: 95, adm: 20, vel: '+12.0%', rev: 'KES 95,000' },
        { month: 'April 2026', apps: 140, adm: 45, vel: '+47.3%', rev: 'KES 140,000' },
        { month: 'May 2026', apps: 210, adm: 88, vel: '+50.0%', rev: 'KES 210,000' },
        { month: 'June 2026', apps: 285, adm: 165, vel: '+35.7%', rev: 'KES 285,000' },
        { month: 'July 2026', apps: 320, adm: 210, vel: '+12.3%', rev: 'KES 320,000' },
        { month: 'August 2026', apps: 234, adm: 184, vel: '-26.8%', rev: 'KES 234,000' }
    ];

    let showAppsSeries = true;
    let showAdmSeries = true;

    function showChartTooltip(index, x, yApps, yAdm) {
        const data = monthlyChartData[index];
        if (!data) return;

        const tooltip = document.getElementById('chartTooltip');
        const guideLine = document.getElementById('cursorGuideLine');
        const chartWrapper = document.getElementById('chartWrapper');
        if (!tooltip || !chartWrapper) return;

        const velClass = data.vel.startsWith('+') ? 'text-emerald-400' : 'text-amber-400';
        document.getElementById('tooltipMonth').innerHTML = `<span>${data.month}</span><span class="${velClass} font-mono">${data.vel}</span>`;
        document.getElementById('tooltipApps').textContent = data.apps.toLocaleString();
        document.getElementById('tooltipAdm').textContent = data.adm.toLocaleString();
        document.getElementById('tooltipRev').textContent = data.rev;

        const percentX = (x / 650) * 100;
        tooltip.style.top = `10px`;
        if (percentX > 50) {
            tooltip.style.left = 'auto';
            tooltip.style.right = `calc(${100 - percentX}% + 16px)`;
            tooltip.style.transform = 'none';
        } else {
            tooltip.style.right = 'auto';
            tooltip.style.left = `calc(${percentX}% + 16px)`;
            tooltip.style.transform = 'none';
        }
        tooltip.classList.remove('hidden');

        if (guideLine) {
            guideLine.setAttribute('x1', x);
            guideLine.setAttribute('x2', x);
            guideLine.setAttribute('opacity', '0.7');
        }
    }

    function hideChartTooltip() {
        const tooltip = document.getElementById('chartTooltip');
        const guideLine = document.getElementById('cursorGuideLine');
        if (tooltip) tooltip.classList.add('hidden');
        if (guideLine) guideLine.setAttribute('opacity', '0');
    }

    function toggleChartSeries(type) {
        if (type === 'apps') {
            showAppsSeries = !showAppsSeries;
            const grp = document.getElementById('seriesAppsGroup');
            const pts = document.getElementById('appPointsGroup');
            const btn = document.getElementById('toggleSeriesApps');
            if (grp) grp.style.opacity = showAppsSeries ? '1' : '0.1';
            if (pts) pts.style.opacity = showAppsSeries ? '1' : '0.1';
            if (btn) btn.style.opacity = showAppsSeries ? '1' : '0.4';
        } else if (type === 'adm') {
            showAdmSeries = !showAdmSeries;
            const grp = document.getElementById('seriesAdmGroup');
            const pts = document.getElementById('admPointsGroup');
            const btn = document.getElementById('toggleSeriesAdm');
            if (grp) grp.style.opacity = showAdmSeries ? '1' : '0.1';
            if (pts) pts.style.opacity = showAdmSeries ? '1' : '0.1';
            if (btn) btn.style.opacity = showAdmSeries ? '1' : '0.4';
        }
    }
</script>
@endsection
