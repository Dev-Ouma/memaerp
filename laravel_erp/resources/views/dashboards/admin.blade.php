@php
    $m = $metrics;
    $apps = $m['applications']; $inProg = $m['inProgress']; $interested = $m['interestedApplicants'];
    $reverify = $m['reverify']; $admitted = $m['admitted']; $inRev = $m['inReview'];
    $l2Rejected = $m['l2Rejected']; $offerRej = $m['offerRejected']; $enrolled = $m['enrolled'];
    $enrolledInRev = $m['enrolledInReview']; $accepted = $m['accepted']; $initiated = $m['initiated'];
    $graduated = $m['graduated']; $alumni = $m['alumniCount']; $pendingApps = $m['pendingAdmissions'];
    $dropOff = $m['dropOffRate']; $sources = $m['admissionsBySource']; $trends = $m['applicationTrends'];
    $programmes = $m['programmePopularity']; $schools = $m['schoolDistribution']; $placement = $m['placement'];
    $inclusivity = $m['inclusivity']; $gender = $m['gender']; $counties = $m['counties']; $exec = $executive;
    $trendValues = collect($trends)->pluck('value');
    $latestTrend = collect($trends)->last();
    $peakTrend = collect($trends)->sortByDesc('value')->first();
    $monthlyAverage = $trendValues->count() > 0 ? round($trendValues->avg()) : 0;
@endphp

<div class="ouk-dashboard-container font-quicksand">
    <div class="sr-only">College-wide operations, people and academic performance.</div>
    
    {{-- TOP FILTER CONTROLS BAR (END-TO-END CONNECTED) --}}
    @php
        $f = $filters ?? [
            'academic_year' => request('academic_year', ''),
            'semester' => request('semester', ''),
            'cohort' => request('cohort', ''),
            'programme' => request('programme', ''),
            'level' => request('level', ''),
            'options' => [
                'academic_years' => ['2026/2027', '2025/2026', '2024/2025', '2023/2024'],
                'semesters' => ['Semester 1', 'Semester 2', 'Trimester 1', 'Trimester 2', 'Trimester 3'],
                'cohorts' => ['2026/2027 - September Intake', '2026/2027 - January Intake', '2026/2027 - May Intake'],
                'programmes' => \App\Models\Course::query()->orderBy('name')->get(['id', 'code', 'name']),
                'levels' => ['Undergraduate' => 'Undergraduate (Degree)', 'Postgraduate' => 'Postgraduate (Masters / PhD)', 'Diploma' => 'Diploma Programmes', 'Certificate' => 'Certificate Courses', 'Short Course' => 'Executive & Short Courses'],
            ],
            'active' => array_filter(request()->only(['academic_year', 'semester', 'cohort', 'programme', 'level'])),
            'active_count' => count(array_filter(request()->only(['academic_year', 'semester', 'cohort', 'programme', 'level']))),
            'has_active' => count(array_filter(request()->only(['academic_year', 'semester', 'cohort', 'programme', 'level']))) > 0,
        ];
    @endphp

    <div class="mb-6 bg-white p-4 sm:p-5 rounded-xl border border-slate-200/90 shadow-2xs">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 mb-3.5 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="w-8 h-8 rounded-lg bg-[#0A3E50]/10 flex items-center justify-center text-[#0A3E50]">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-900 tracking-tight">Institutional Operations & Telemetry Filters</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live PostgreSQL Data
                        </span>
                        @if($f['has_active'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200">
                                <i data-lucide="filter" class="w-3 h-3 text-amber-600"></i> {{ $f['active_count'] }} Active {{ \Illuminate\Support\Str::plural('Filter', $f['active_count']) }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Filter real-time student admissions lifecycle, enrolment registers, and institutional performance metrics.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap self-end lg:self-auto">
                <button type="button" onclick="openDashboardExportModal()" class="px-3 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-[#E67E22]"></i> Export Report / Data
                </button>
                <a href="{{ route('admissions.reports') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all border border-slate-300/80 flex items-center gap-1.5">
                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Detailed Reports
                </a>
            </div>
        </div>

        {{-- Interactive Filter Form --}}
        <form method="GET" action="{{ route('dashboard') }}" id="dashboardFilterForm" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
                {{-- 1. Academic Year --}}
                <div>
                    <label for="filter_academic_year" class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3 text-[#0A3E50]"></i> Academic Year
                    </label>
                    <select name="academic_year" id="filter_academic_year" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] transition-all">
                        <option value="">All Academic Years</option>
                        @foreach($f['options']['academic_years'] as $yr)
                            <option value="{{ $yr }}" {{ $f['academic_year'] === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Semester / Term --}}
                <div>
                    <label for="filter_semester" class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-[#0A3E50]"></i> Semester / Term
                    </label>
                    <select name="semester" id="filter_semester" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] transition-all">
                        <option value="">All Semesters</option>
                        @foreach($f['options']['semesters'] as $sem)
                            <option value="{{ $sem }}" {{ $f['semester'] === (string)$sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Cohort / Intake --}}
                <div>
                    <label for="filter_cohort" class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center gap-1">
                        <i data-lucide="layers" class="w-3 h-3 text-[#0A3E50]"></i> Cohort / Intake
                    </label>
                    <select name="cohort" id="filter_cohort" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] transition-all">
                        <option value="">All Cohorts</option>
                        @foreach($f['options']['cohorts'] as $coh)
                            <option value="{{ $coh }}" {{ $f['cohort'] === (string)$coh ? 'selected' : '' }}>{{ $coh }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Programme / Course --}}
                <div>
                    <label for="filter_programme" class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center gap-1">
                        <i data-lucide="graduation-cap" class="w-3 h-3 text-[#0A3E50]"></i> Programme
                    </label>
                    <select name="programme" id="filter_programme" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] transition-all">
                        <option value="">All Programmes</option>
                        @foreach($f['options']['programmes'] as $prog)
                            @php
                                $val = is_object($prog) ? ($prog->code ?? $prog->id) : (string)$prog;
                                $label = is_object($prog) ? ($prog->code ? "{$prog->name} ({$prog->code})" : $prog->name) : (string)$prog;
                            @endphp
                            <option value="{{ $val }}" {{ $f['programme'] === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 5. Level --}}
                <div>
                    <label for="filter_level" class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center gap-1">
                        <i data-lucide="award" class="w-3 h-3 text-[#0A3E50]"></i> Study Level
                    </label>
                    <select name="level" id="filter_level" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#0A3E50] focus:ring-1 focus:ring-[#0A3E50] transition-all">
                        <option value="">All Levels</option>
                        @foreach($f['options']['levels'] as $lvlKey => $lvlLabel)
                            <option value="{{ $lvlKey }}" {{ strtolower($f['level']) === strtolower((string)$lvlKey) ? 'selected' : '' }}>{{ $lvlLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Filter Actions & Active Filter Badges --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex items-center gap-1.5 flex-wrap text-xs">
                    @if($f['has_active'])
                        <span class="text-[11px] font-bold text-slate-500 mr-1">Active:</span>
                        @foreach($f['active'] as $key => $val)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold text-[#0A3E50] bg-[#0A3E50]/8 border border-[#0A3E50]/20">
                                <span class="capitalize text-slate-600">{{ str_replace('_', ' ', $key) }}:</span>
                                <strong>{{ $val }}</strong>
                                <a href="{{ route('dashboard', request()->except($key)) }}" class="text-slate-400 hover:text-rose-600 ml-0.5" title="Remove filter">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </a>
                            </span>
                        @endforeach
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors ml-1">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Clear All Filters
                        </a>
                    @else
                        <span class="text-[11px] text-slate-400 italic">Showing institutional-wide metrics across all programmes, cohorts, and academic years.</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if($f['has_active'])
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all border border-slate-300 flex items-center gap-1">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset
                        </a>
                    @endif
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-[#E67E22]"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- SECTION 1: APPLICATION OVERVIEW (KPI CARDS) --}}
    <section class="ouk-section mb-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="ouk-section-title mb-0">Application Overview</h2>
            <span class="text-xs text-slate-500">Click any card or metric to drill down into records</span>
        </div>
        <div class="ouk-kpi-grid">
            
            {{-- Card 1: Applications --}}
            <div class="ouk-kpi-card hover:border-[#0A3E50] transition-all cursor-pointer group" onclick="openDataViewModal('applications')">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label group-hover:text-[#0A3E50]">Applications</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($apps) }}</span>
                        <span onclick="openDataViewModal('in_progress'); event.stopPropagation();" class="kpi-sub-pill hover:bg-teal-100 transition-colors cursor-pointer" title="View in-progress applications">
                            In Progress: {{ number_format($inProg) }} <i data-lucide="line-chart" class="w-3.5 h-3.5 inline text-teal-600"></i>
                        </span>
                    </div>
                    <div class="kpi-subtitle">Current applied status</div>
                    <div class="kpi-badge-list">
                        <span onclick="openDataViewModal('interested'); event.stopPropagation();" class="kpi-badge kpi-badge-blue hover:bg-blue-100 transition-colors cursor-pointer" title="View all applicants">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Interested Applicants: {{ number_format($interested) }}</span>
                        </span>
                        <span onclick="openDataViewModal('reverify'); event.stopPropagation();" class="kpi-badge kpi-badge-blue hover:bg-blue-100 transition-colors cursor-pointer" title="Documents requiring re-verification">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            <span>Reverify: {{ $reverify }}</span>
                        </span>
                    </div>
                </div>
                <div class="kpi-footer flex justify-between items-center">
                    <span></span>
                    <button type="button" onclick="openDataViewModal('applications'); event.stopPropagation();" class="kpi-eye-btn" title="View details" aria-label="View Applications details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Card 2: Admitted --}}
            <div class="ouk-kpi-card hover:border-[#0A3E50] transition-all cursor-pointer group" onclick="openDataViewModal('admissions')">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label group-hover:text-[#0A3E50]">Admitted</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($admitted) }}</span>
                        <span onclick="openDataViewModal('in_progress'); event.stopPropagation();" class="kpi-sub-pill hover:bg-slate-200 transition-colors cursor-pointer">
                            In Review: {{ number_format($inRev) }}
                        </span>
                    </div>
                    <div class="kpi-subtitle">Current admitted student status</div>
                    <div class="kpi-badge-list">
                        <span onclick="openDataViewModal('l2_rejected'); event.stopPropagation();" class="kpi-badge kpi-badge-red hover:bg-rose-100 transition-colors cursor-pointer">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            <span>L2 Rejected: {{ number_format($l2Rejected) }}</span>
                        </span>
                        <span onclick="openDataViewModal('offer_rejected'); event.stopPropagation();" class="kpi-badge kpi-badge-red hover:bg-rose-100 transition-colors cursor-pointer">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>Offer Rejected: {{ number_format($offerRej) }}</span>
                        </span>
                    </div>
                </div>
                <div class="kpi-footer flex justify-between items-center">
                    <span></span>
                    <button type="button" onclick="openDataViewModal('admissions'); event.stopPropagation();" class="kpi-eye-btn" title="View details" aria-label="View Admitted details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Card 3: Enrolled --}}
            <div class="ouk-kpi-card hover:border-[#0A3E50] transition-all cursor-pointer group" onclick="openDataViewModal('enrolments')">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label group-hover:text-[#0A3E50]">Enrolled</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($enrolled) }}</span>
                        <span onclick="openDataViewModal('in_progress'); event.stopPropagation();" class="kpi-sub-pill hover:bg-slate-200 transition-colors cursor-pointer">
                            In Review: {{ number_format($enrolledInRev) }}
                        </span>
                    </div>
                    <div class="kpi-subtitle">Current registered status</div>
                    <div class="kpi-badge-list">
                        <span onclick="openDataViewModal('accepted'); event.stopPropagation();" class="kpi-badge kpi-badge-green hover:bg-emerald-100 transition-colors cursor-pointer">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                            <span>Accepted: {{ number_format($accepted) }}</span>
                        </span>
                        <span onclick="openDataViewModal('initiated'); event.stopPropagation();" class="kpi-badge kpi-badge-green hover:bg-emerald-100 transition-colors cursor-pointer">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Initiated: {{ number_format($initiated) }}</span>
                        </span>
                    </div>
                </div>
                <div class="kpi-footer flex justify-between items-center">
                    <span></span>
                    <button type="button" onclick="openDataViewModal('enrolments'); event.stopPropagation();" class="kpi-eye-btn" title="View details" aria-label="View Enrolled details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Card 4: Graduated --}}
            <div class="ouk-kpi-card hover:border-[#0A3E50] transition-all cursor-pointer group" onclick="openDataViewModal('graduated')">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label group-hover:text-[#0A3E50]">Graduated</span>
                    <span class="kpi-alumni-tag">Alumni: {{ $alumni }}</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($graduated) }}</span>
                        <span onclick="openDataViewModal('programmes'); event.stopPropagation();" class="kpi-sub-pill hover:bg-slate-200 transition-colors cursor-pointer">
                            Programmes: {{ $m['programmesCount'] ?? 1 }}
                        </span>
                    </div>
                    <div class="kpi-subtitle">Current graduation list status</div>
                </div>
                <div class="kpi-footer flex justify-between items-center">
                    <span></span>
                    <button type="button" onclick="openDataViewModal('graduated'); event.stopPropagation();" class="kpi-eye-btn" title="View details" aria-label="View Graduated details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

        </div>
    </section>

    {{-- SECTION 2: ADMISSIONS AND REGISTRATION --}}
    <section class="ouk-section mb-6">
        <h2 class="ouk-section-title">Admissions and Registration</h2>
        <div class="ouk-adm-reg-grid">
            
            {{-- Left Summary Stats Column --}}
            <div class="ouk-panel ouk-stats-summary-panel">
                <a href="{{ route('admissions.workspace.shortlists') }}" class="stat-block hover:bg-slate-50 transition-colors block rounded-lg p-2.5 -m-2.5 mb-1 group" title="View shortlisted/admitted candidates">
                    <div class="stat-title-caps group-hover:text-[#0A3E50] flex justify-between items-center">
                        <span>ADMITTED</span>
                        <span class="text-[10px] text-slate-400 font-normal">View List &rarr;</span>
                    </div>
                    <div class="stat-val-large">{{ number_format($admitted) }}</div>
                </a>

                <a href="{{ route('admissions.workspace.offers') }}" class="stat-block hover:bg-slate-50 transition-colors block rounded-lg p-2.5 -m-2.5 mb-1 group" title="View pending admission offers">
                    <div class="stat-title-regular group-hover:text-[#0A3E50] flex justify-between items-center">
                        <span>Pending Admissions</span>
                        <span class="text-[10px] text-slate-400 font-normal">Offers &rarr;</span>
                    </div>
                    <div class="stat-val-medium">{{ number_format($pendingApps) }}</div>
                    <div class="stat-note">Students offered admission but not confirmed</div>
                </a>

                <a href="{{ route('admissions.reports') }}" class="stat-block hover:bg-slate-50 transition-colors block rounded-lg p-2.5 -m-2.5 mb-1 group" title="View conversion and drop-off analytics">
                    <div class="stat-title-caps group-hover:text-[#0A3E50] flex justify-between items-center">
                        <span>DROP-OFF RATE</span>
                        <span class="text-[10px] text-slate-400 font-normal">Analytics &rarr;</span>
                    </div>
                    <div class="stat-rate-row">
                        <span class="stat-rate-val text-emerald-600">{{ $dropOff }}</span>
                        <span class="stat-accepted-tag">Accepted: {{ number_format($accepted) }}</span>
                    </div>
                    <div class="stat-note">Confirmed but didn't register</div>
                </a>

                <a href="{{ route('admissions.reports.applications') }}" class="stat-block hover:bg-slate-50 transition-colors block rounded-lg p-2.5 -m-2.5 group" title="View admissions by intake source">
                    <div class="stat-title-caps group-hover:text-[#0A3E50] flex justify-between items-center">
                        <span>ADMISSIONS BY SOURCE</span>
                        <span class="text-[10px] text-slate-400 font-normal">Details &rarr;</span>
                    </div>
                    <div class="source-list">
                        <?php foreach($sources as $source => $count): ?>
                            <div class="source-item">
                                <span class="source-name">{{ $source }}:</span>
                                <span class="source-count font-bold">{{ number_format($count) }}</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </a>
            </div>

            {{-- Right Chart: Application Trends Over Time --}}
            <div class="ouk-panel ouk-chart-panel">
                <div class="panel-header-row">
                    <div>
                        <h3 class="panel-chart-title">Applications</h3>
                        <p class="panel-chart-subtitle">Application trends over time</p>
                    </div>
                    <div class="panel-controls">
                        <select class="ouk-select" aria-label="Application trends timeframe">
                            <option>Monthly</option>
                            <option>Quarterly</option>
                            <option>Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="chart-wrapper spline-chart-wrapper">
                    <?php
                        $rawMax = max(array_column($trends, 'value') ?: [0]);
                        if ($rawMax <= 10) {
                            $maxY = 10;
                            $yTicks = [0, 2, 4, 6, 8, 10];
                        } elseif ($rawMax <= 25) {
                            $maxY = 25;
                            $yTicks = [0, 5, 10, 15, 20, 25];
                        } elseif ($rawMax <= 50) {
                            $maxY = 50;
                            $yTicks = [0, 10, 20, 30, 40, 50];
                        } elseif ($rawMax <= 100) {
                            $maxY = 100;
                            $yTicks = [0, 20, 40, 60, 80, 100];
                        } elseif ($rawMax <= 150) {
                            $maxY = 150;
                            $yTicks = [0, 30, 60, 90, 120, 150];
                        } elseif ($rawMax <= 250) {
                            $maxY = 250;
                            $yTicks = [0, 50, 100, 150, 200, 250];
                        } elseif ($rawMax <= 500) {
                            $maxY = 500;
                            $yTicks = [0, 100, 200, 300, 400, 500];
                        } else {
                            $step = (int) max(50, ceil(($rawMax / 5) / 50) * 50);
                            $maxY = $step * 5;
                            $yTicks = range(0, $maxY, $step);
                        }

                        $w = 780;
                        $h = 240;
                        $padX = 40;
                        $padY = 20;
                        $bottomY = $h - 30;
                        $chartW = $w - $padX - 20;
                        $chartH = $bottomY - $padY;
                        
                        $countTrends = count($trends);
                        $points = [];
                        foreach ($trends as $idx => $t) {
                            $x = $padX + ($idx * ($chartW / max(1, $countTrends - 1)));
                            $y = $bottomY - (($t['value'] / max(1, $maxY)) * $chartH);
                            $points[] = ['x' => $x, 'y' => $y, 'month' => $t['month'], 'val' => $t['value']];
                        }
                        
                        // Construct smooth cubic bezier path
                        $pathD = "M " . $points[0]['x'] . " " . $points[0]['y'];
                        for ($i = 0; $i < count($points) - 1; $i++) {
                            $p0 = $points[max(0, $i - 1)];
                            $p1 = $points[$i];
                            $p2 = $points[$i + 1];
                            $p3 = $points[min(count($points) - 1, $i + 2)];
                            
                            $cp1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                            $cp1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                            $cp2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                            $cp2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;
                            
                            $pathD .= " C $cp1x $cp1y, $cp2x $cp2y, " . $p2['x'] . " " . $p2['y'];
                        }
                        
                        $areaD = $pathD . " L " . $points[count($points)-1]['x'] . " $bottomY L " . $points[0]['x'] . " $bottomY Z";
                    ?>

                    <svg class="w-full h-auto" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Application trends over time chart">
                        <defs>
                            <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0A3E50" stop-opacity="0.35" />
                                <stop offset="65%" stop-color="#007A8C" stop-opacity="0.12" />
                                <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        {{-- Y-Axis Gridlines & Labels --}}
                        <?php foreach($yTicks as $yVal): ?>
                            <?php $yPos = $bottomY - (($yVal / max(1, $maxY)) * $chartH); ?>
                            <line x1="{{ $padX }}" y1="{{ $yPos }}" x2="{{ $w - 20 }}" y2="{{ $yPos }}" stroke="#E2E8F0" stroke-width="1" />
                            <text x="{{ $padX - 8 }}" y="{{ $yPos + 3 }}" text-anchor="end" font-size="10" fill="#64748B">{{ $yVal }}</text>
                        <?php endforeach; ?>

                        {{-- Area Gradient Fill --}}
                        <path d="{{ $areaD }}" fill="url(#trendGradient)" />

                        {{-- Smooth Spline Line --}}
                        <path d="{{ $pathD }}" fill="none" stroke="#007A8C" stroke-width="2.5" stroke-linecap="round" />

                        {{-- Points and Month Labels --}}
                        <?php foreach($points as $pt): ?>
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#FFFFFF" stroke="#007A8C" stroke-width="2" class="cursor-pointer hover:r-6 transition-all duration-150">
                                <title>{{ $pt['month'] }}: {{ number_format($pt['val']) }} applications</title>
                            </circle>
                            <text x="{{ $pt['x'] }}" y="{{ $h - 10 }}" text-anchor="middle" font-size="10.5" fill="#64748B">{{ $pt['month'] }}</text>
                        <?php endforeach; ?>
                    </svg>
                </div>
            </div>

        </div>
    </section>

    {{-- SECTION 3: PROGRAMMES --}}
    <section class="ouk-section mb-6">
        <div class="panel-header-row mb-3 flex-wrap">
            <h2 class="ouk-section-title mb-0">Programmes</h2>
            <div class="flex items-center gap-3">
                <select class="ouk-select" aria-label="Programmes timeframe">
                    <option>Monthly</option>
                    <option>Quarterly</option>
                    <option>Yearly</option>
                </select>
                <button type="button" class="ouk-btn-export">
                    <span>Export</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        {{-- Programmes Sub-stats Bar --}}
        <div class="programmes-stat-strip mb-4">
            <div class="strip-item">
                <div class="strip-headline">
                    <span class="font-bold text-gray-900">32</span> <span class="text-sm font-semibold text-gray-700">Active</span> <span class="text-emerald-600 text-xs font-bold">▲</span>
                </div>
                <div class="strip-sub">Total: 72</div>
            </div>

            <div class="strip-item">
                <div class="strip-headline">
                    <a href="#" class="text-blue-600 hover:underline font-bold text-sm">79 Professional Development Courses</a>
                </div>
                <div class="strip-sub">Total: <span class="font-semibold text-gray-800">34360</span> &nbsp; Applied: <span class="font-semibold text-gray-800">19958</span> &nbsp; In Review: <span class="font-semibold text-gray-800">14402</span></div>
            </div>

            <div class="strip-item">
                <div class="strip-headline">
                    <span class="font-bold text-gray-900">9</span> <span class="text-sm font-semibold text-gray-700">Active Cohorts</span>
                </div>
                <div class="strip-sub">Total: 9</div>
            </div>
        </div>

        {{-- Programmes Bar Chart Panel --}}
        <div class="ouk-panel">
            <div class="flex justify-between items-center mb-3">
                <div class="font-bold text-xs uppercase tracking-wider text-gray-800">Top 10 Programme Popularity</div>
                <a href="#" class="text-xs text-orange-600 hover:underline font-semibold">More</a>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="w-3.5 h-3.5 inline-block bg-[#007A8C] rounded-sm"></span>
                <span class="text-xs text-gray-600 font-medium">Programmes</span>
            </div>

            {{-- Bar Chart SVG --}}
            <?php
                $pRawMax = max(array_column($programmes, 'count') ?: [0]);
                if ($pRawMax <= 10) {
                    $progMaxY = 10;
                    $pYTicks = [0, 2, 4, 6, 8, 10];
                } elseif ($pRawMax <= 25) {
                    $progMaxY = 25;
                    $pYTicks = [0, 5, 10, 15, 20, 25];
                } elseif ($pRawMax <= 50) {
                    $progMaxY = 50;
                    $pYTicks = [0, 10, 20, 30, 40, 50];
                } elseif ($pRawMax <= 100) {
                    $progMaxY = 100;
                    $pYTicks = [0, 20, 40, 60, 80, 100];
                } else {
                    $step = (int) max(10, ceil(($pRawMax / 5) / 10) * 10);
                    $progMaxY = $step * 5;
                    $pYTicks = range(0, $progMaxY, $step);
                }

                $pW = 920;
                $pH = 300;
                $pPadX = 48;
                $pPadY = 20;
                $pBottomY = $pH - 35;
                $pChartW = $pW - $pPadX - 20;
                $pChartH = $pBottomY - $pPadY;
                $barSlotW = $pChartW / max(1, count($programmes));
                $barWidth = min(58, $barSlotW * 0.72);
            ?>

            <div class="chart-wrapper">
                <svg class="w-full h-auto" viewBox="0 0 {{ $pW }} {{ $pH }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Top 10 Programme Popularity bar chart">
                    {{-- Grid lines & Y labels --}}
                    <?php foreach($pYTicks as $yVal): ?>
                        <?php $yPos = $pBottomY - (($yVal / max(1, $progMaxY)) * $pChartH); ?>
                        <line x1="{{ $pPadX }}" y1="{{ $yPos }}" x2="{{ $pW - 20 }}" y2="{{ $yPos }}" stroke="#E2E8F0" stroke-width="1" />
                        <text x="{{ $pPadX - 8 }}" y="{{ $yPos + 3 }}" text-anchor="end" font-size="9.5" fill="#64748B">{{ $yVal }}</text>
                    <?php endforeach; ?>

                    {{-- Vertical Column Separators --}}
                    <?php foreach($programmes as $i => $prog): ?>
                        <?php $colX = $pPadX + ($i * $barSlotW); ?>
                        <line x1="{{ $colX }}" y1="{{ $pPadY }}" x2="{{ $colX }}" y2="{{ $pBottomY }}" stroke="#F1F5F9" stroke-width="1" />
                    <?php endforeach; ?>
                    <line x1="{{ $pPadX + (count($programmes) * $barSlotW) }}" y1="{{ $pPadY }}" x2="{{ $pPadX + (count($programmes) * $barSlotW) }}" y2="{{ $pBottomY }}" stroke="#F1F5F9" stroke-width="1" />

                    {{-- Bars --}}
                    <?php foreach($programmes as $i => $prog): ?>
                        <?php
                            $slotCenterX = $pPadX + ($i * $barSlotW) + ($barSlotW / 2);
                            $bX = $slotCenterX - ($barWidth / 2);
                            $bHeight = ($prog['count'] / $progMaxY) * $pChartH;
                            $bY = $pBottomY - $bHeight;
                        ?>
                        <rect x="{{ $bX }}" y="{{ $bY }}" width="{{ $barWidth }}" height="{{ $bHeight }}" fill="#007A8C" rx="1" class="transition-all duration-200 hover:opacity-85 cursor-pointer">
                            <title>{{ $prog['code'] }}: {{ number_format($prog['count']) }} students</title>
                        </rect>
                        <text x="{{ $slotCenterX }}" y="{{ $pH - 12 }}" text-anchor="middle" font-size="9.5" fill="#475569" font-weight="600">{{ $prog['code'] }}</text>
                    <?php endforeach; ?>
                </svg>
            </div>
        </div>
    </section>

    {{-- SECTION 4: SCHOOLS AND DEPARTMENTS --}}
    <section class="ouk-section mb-6">
        <div class="ouk-panel">
            <div class="panel-header-row mb-1 flex-wrap">
                <div>
                    <h2 class="panel-chart-title text-base font-bold text-gray-900">Schools and Departments</h2>
                    <p class="panel-chart-subtitle text-xs text-gray-500">graphical presentation of student enrollment status across various schools, displaying the numbers of students who have registered, applied and been admitted</p>
                </div>
                <div class="flex items-center gap-3">
                    <select class="ouk-select" aria-label="Schools timeframe">
                        <option>Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                    </select>
                    <button type="button" class="ouk-btn-export">
                        <span>Export</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <div class="schools-grid mt-4">
                {{-- Left: Stacked / Status Comparison Chart --}}
                <div class="schools-chart-col">
                    {{-- Legend --}}
                    <div class="flex items-center gap-4 mb-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm bg-[#E67E22] inline-block"></span>
                            <span class="text-gray-700">Admitted</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm bg-[#007A8C] inline-block"></span>
                            <span class="text-gray-700">Registered</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm bg-[#94A3B8] inline-block"></span>
                            <span class="text-gray-700">Pending Application</span>
                        </div>
                    </div>

                    {{-- Percentage Stacked Chart SVG --}}
                    <?php
                        $schW = 600;
                        $schH = 260;
                        $schPadX = 35;
                        $schPadY = 15;
                        $schBottomY = $schH - 25;
                        $schChartW = $schW - $schPadX - 15;
                        $schChartH = $schBottomY - $schPadY;
                        $schSlotW = $schChartW / max(1, count($schools));
                        $schBarW = min(50, $schSlotW * 0.45);
                    ?>

                    <div class="chart-wrapper">
                        <svg class="w-full h-auto" viewBox="0 0 {{ $schW }} {{ $schH }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Schools student status chart">
                            {{-- Y-Axis Grid (0% to 100%) --}}
                            <?php foreach([0, 20, 40, 60, 80, 100] as $pct): ?>
                                <?php $yPos = $schBottomY - (($pct / 100) * $schChartH); ?>
                                <line x1="{{ $schPadX }}" y1="{{ $yPos }}" x2="{{ $schW - 15 }}" y2="{{ $yPos }}" stroke="#E2E8F0" stroke-width="1" />
                                <text x="{{ $schPadX - 6 }}" y="{{ $yPos + 3 }}" text-anchor="end" font-size="9" fill="#64748B">{{ $pct }}%</text>
                            <?php endforeach; ?>

                            {{-- Stacked Bars for each School --}}
                            <?php foreach($schools as $i => $school): ?>
                                <?php
                                    $slotCenterX = $schPadX + ($i * $schSlotW) + ($schSlotW / 2);
                                    $bX = $slotCenterX - ($schBarW / 2);
                                    
                                    $hAdmitted = ($school['admitted'] / 100) * $schChartH;
                                    $hReg = ($school['registered'] / 100) * $schChartH;
                                    $hPending = ($school['pending'] / 100) * $schChartH;
                                    
                                    $yAdmitted = $schBottomY - $hAdmitted;
                                    $yReg = $yAdmitted - $hReg;
                                    $yPending = $yReg - $hPending;
                                    
                                    $shortName = match($i) {
                                        0 => 'Business & Econ',
                                        1 => 'Sci & Tech',
                                        2 => 'Education',
                                        default => 'School ' . ($i+1)
                                    };
                                ?>

                                {{-- Pending (Grey) --}}
                                <rect x="{{ $bX }}" y="{{ $yPending }}" width="{{ $schBarW }}" height="{{ $hPending }}" fill="#94A3B8" class="hover:opacity-85">
                                    <title>{{ $school['name'] }} - Pending: {{ $school['pending'] }}%</title>
                                </rect>

                                {{-- Registered (Teal) --}}
                                <rect x="{{ $bX }}" y="{{ $yReg }}" width="{{ $schBarW }}" height="{{ $hReg }}" fill="#007A8C" class="hover:opacity-85">
                                    <title>{{ $school['name'] }} - Registered: {{ $school['registered'] }}%</title>
                                </rect>

                                {{-- Admitted (Orange) --}}
                                <rect x="{{ $bX }}" y="{{ $yAdmitted }}" width="{{ $schBarW }}" height="{{ $hAdmitted }}" fill="#E67E22" class="hover:opacity-85">
                                    <title>{{ $school['name'] }} - Admitted: {{ $school['admitted'] }}%</title>
                                </rect>

                                <text x="{{ $slotCenterX }}" y="{{ $schH - 8 }}" text-anchor="middle" font-size="9.5" fill="#475569" font-weight="600">{{ $shortName }}</text>
                            <?php endforeach; ?>
                        </svg>
                    </div>
                </div>

                {{-- Right: Schools Sidebar Stats --}}
                <div class="schools-info-col border-l border-gray-100 pl-4">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <div class="text-xs font-bold text-gray-800">Schools</div>
                            <div class="text-xs font-semibold text-gray-700">3 Active <span class="text-emerald-600 text-xs">▲</span> &nbsp; <span class="text-gray-500 font-normal">Total: 35</span></div>
                        </div>
                        <a href="#" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-semibold">Reports <i data-lucide="external-link" class="w-3 h-3"></i></a>
                    </div>

                    <div class="mb-4">
                        <div class="text-xs font-bold text-gray-800">Students</div>
                        <div class="text-[11px] text-gray-500 mb-2">Number of students per school</div>

                        <div class="space-y-2">
                            <?php foreach($schools as $sch): ?>
                                <div class="school-stat-row">
                                    <div class="font-bold text-gray-900 text-sm">{{ number_format($sch['count']) }}</div>
                                    <div class="text-xs text-gray-600">{{ $sch['name'] }}</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="#" class="text-xs text-orange-600 hover:underline font-semibold flex items-center gap-1">School-wise Admission Trends <i data-lucide="arrow-up-right" class="w-3 h-3"></i></a>
                    </div>

                    <div class="top-perf-box p-2.5 bg-orange-50/60 rounded-md border border-orange-100">
                        <div class="text-[11px] uppercase font-bold text-gray-600 tracking-wide">Top Performing Schools</div>
                        <div class="text-xs font-bold text-orange-600 mt-0.5">School of Business and Economics</div>
                        <div class="text-[11px] text-gray-500">By highest enrollment</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 5: DEMOGRAPHICS (PLACEMENT, INCLUSIVITY, GENDER) --}}
    <section class="ouk-section mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            {{-- Placement Donut / Pie Chart --}}
            <div class="ouk-panel flex flex-col justify-between">
                <div class="font-bold text-xs uppercase tracking-wider text-gray-800 mb-2">Placement</div>
                <div class="flex items-center justify-center my-auto py-2">
                    {{-- SVG Donut Chart for Placement --}}
                    <svg width="170" height="170" viewBox="0 0 42 42" class="donut-chart" role="img" aria-label="Placement distribution">
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#E2E8F0" stroke-width="8"></circle>
                        {{-- PSSP (Teal ~68%) --}}
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#007A8C" stroke-width="8" stroke-dasharray="{{ $placement['pssp'] }} {{ 100 - $placement['pssp'] }}" stroke-dashoffset="25"></circle>
                        {{-- KUCCPS (Peach/Coral ~32%) --}}
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#F6BDAC" stroke-width="8" stroke-dasharray="{{ $placement['kuccps'] }} {{ 100 - $placement['kuccps'] }}" stroke-dashoffset="{{ 25 - $placement['pssp'] }}"></circle>
                    </svg>
                </div>
                <div class="flex justify-center items-center gap-4 mt-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#F6BDAC] inline-block"></span>
                        <span class="text-gray-700">KUCCPS ({{ $placement['kuccps'] }}%)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#007A8C] inline-block"></span>
                        <span class="text-gray-700">PSSP ({{ $placement['pssp'] }}%)</span>
                    </div>
                </div>
            </div>

            {{-- Inclusivity Progress Bars --}}
            <div class="ouk-panel flex flex-col justify-between">
                <div>
                    <div class="font-bold text-xs uppercase tracking-wider text-gray-800 mb-1">Inclusivity</div>
                    <p class="text-[11px] text-gray-500 mb-4">Overall analysis of students with disability and youths against number of applications</p>

                    <div class="space-y-4 my-auto">
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Students with disability:</span>
                                <span class="text-emerald-700 font-bold">{{ $inclusivity['disability'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-emerald-600 h-2.5 rounded-full" style="width: {{ $inclusivity['disability'] }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Youth:</span>
                                <span class="text-blue-700 font-bold">{{ $inclusivity['youth'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $inclusivity['youth'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gender Distribution Donut / Pie Chart --}}
            <div class="ouk-panel flex flex-col justify-between">
                <div class="font-bold text-xs uppercase tracking-wider text-gray-800 mb-2">Gender Distribution</div>
                <div class="flex items-center justify-center my-auto py-2">
                    {{-- SVG Donut Chart for Gender --}}
                    <svg width="170" height="170" viewBox="0 0 42 42" class="donut-chart" role="img" aria-label="Gender distribution">
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#E2E8F0" stroke-width="8"></circle>
                        {{-- Female (Lavender ~72%) --}}
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#E8D5F5" stroke-width="8" stroke-dasharray="{{ $gender['female'] }} {{ 100 - $gender['female'] }}" stroke-dashoffset="25"></circle>
                        {{-- Male (Light Blue ~28%) --}}
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#BAE6FD" stroke-width="8" stroke-dasharray="{{ $gender['male'] }} {{ 100 - $gender['male'] }}" stroke-dashoffset="{{ 25 - $gender['female'] }}"></circle>
                    </svg>
                </div>
                <div class="flex justify-center items-center gap-4 mt-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#E8D5F5] inline-block"></span>
                        <span class="text-gray-700">Female ({{ $gender['female'] }}%)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#BAE6FD] inline-block"></span>
                        <span class="text-gray-700">Male ({{ $gender['male'] }}%)</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- SECTION 6: GEOGRAPHICAL DISTRIBUTION --}}
    <section class="ouk-section mb-6">
        <div class="ouk-panel">
            <div class="panel-header-row mb-2 flex-wrap">
                <div>
                    <h2 class="panel-chart-title text-base font-bold text-gray-900">Geographical Distribution</h2>
                    <p class="panel-chart-subtitle text-xs text-gray-500">Number of students distributed per the top 10 County</p>
                </div>
                <div class="flex items-center gap-3">
                    <select class="ouk-select" aria-label="Geographical timeframe">
                        <option>Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                    </select>
                </div>
            </div>

            {{-- Tabs: Local Students vs International Students --}}
            <div class="flex items-center gap-2 border-b border-gray-100 pb-3 mb-3">
                <button type="button" class="geo-tab-btn active">Local Students</button>
                <button type="button" class="geo-tab-btn">International Students</button>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="w-3.5 h-3.5 inline-block bg-[#007A8C] rounded-sm"></span>
                <span class="text-xs text-gray-600 font-medium">County</span>
            </div>

            {{-- County Bar Chart SVG --}}
            <?php
                $gRawMax = max(array_column($counties, 'count') ?: [0]);
                if ($gRawMax <= 10) {
                    $geoMaxY = 10;
                    $gYTicks = [0, 2, 4, 6, 8, 10];
                } elseif ($gRawMax <= 25) {
                    $geoMaxY = 25;
                    $gYTicks = [0, 5, 10, 15, 20, 25];
                } elseif ($gRawMax <= 50) {
                    $geoMaxY = 50;
                    $gYTicks = [0, 10, 20, 30, 40, 50];
                } elseif ($gRawMax <= 100) {
                    $geoMaxY = 100;
                    $gYTicks = [0, 20, 40, 60, 80, 100];
                } else {
                    $step = (int) max(10, ceil(($gRawMax / 5) / 10) * 10);
                    $geoMaxY = $step * 5;
                    $gYTicks = range(0, $geoMaxY, $step);
                }

                $gW = 920;
                $gH = 260;
                $gPadX = 48;
                $gPadY = 15;
                $gBottomY = $gH - 35;
                $gChartW = $gW - $gPadX - 20;
                $gChartH = $gBottomY - $gPadY;
                $geoSlotW = $gChartW / max(1, count($counties));
                $geoBarW = min(54, $geoSlotW * 0.65);
            ?>

            <div class="chart-wrapper">
                <svg class="w-full h-auto" viewBox="0 0 {{ $gW }} {{ $gH }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Top 10 Counties geographical distribution chart">
                    {{-- Grid lines & Y labels --}}
                    <?php foreach($gYTicks as $yVal): ?>
                        <?php $yPos = $gBottomY - (($yVal / max(1, $geoMaxY)) * $gChartH); ?>
                        <line x1="{{ $gPadX }}" y1="{{ $yPos }}" x2="{{ $gW - 20 }}" y2="{{ $yPos }}" stroke="#E2E8F0" stroke-width="1" />
                        <text x="{{ $gPadX - 8 }}" y="{{ $yPos + 3 }}" text-anchor="end" font-size="9" fill="#64748B">{{ $yVal }}</text>
                    <?php endforeach; ?>

                    {{-- Vertical Column Separators --}}
                    <?php foreach($counties as $i => $c): ?>
                        <?php $colX = $gPadX + ($i * $geoSlotW); ?>
                        <line x1="{{ $colX }}" y1="{{ $gPadY }}" x2="{{ $colX }}" y2="{{ $gBottomY }}" stroke="#F8FAFC" stroke-width="1" />
                    <?php endforeach; ?>
                    <line x1="{{ $gPadX + (count($counties) * $geoSlotW) }}" y1="{{ $gPadY }}" x2="{{ $gPadX + (count($counties) * $geoSlotW) }}" y2="{{ $gBottomY }}" stroke="#F8FAFC" stroke-width="1" />

                    {{-- Bars --}}
                    <?php foreach($counties as $i => $c): ?>
                        <?php
                            $slotCenterX = $gPadX + ($i * $geoSlotW) + ($geoSlotW / 2);
                            $bX = $slotCenterX - ($geoBarW / 2);
                            $bHeight = ($c['count'] / $geoMaxY) * $gChartH;
                            $bY = $gBottomY - $bHeight;
                        ?>
                        <rect x="{{ $bX }}" y="{{ $bY }}" width="{{ $geoBarW }}" height="{{ $bHeight }}" fill="#007A8C" rx="1" class="transition-all duration-200 hover:opacity-85 cursor-pointer">
                            <title>{{ $c['name'] }}: {{ number_format($c['count']) }} students</title>
                        </rect>
                        <text x="{{ $slotCenterX }}" y="{{ $gH - 12 }}" text-anchor="middle" font-size="9.5" fill="#475569" font-weight="600">{{ $c['name'] }}</text>
                    <?php endforeach; ?>
                </svg>
            </div>
        </div>
    </section>

    {{-- SECTION 7: COLLEGE POPULATION --}}
    <section class="ouk-section mb-6" id="population-section">
        <h2 class="ouk-section-title">College Population</h2>
        <div class="ouk-kpi-grid">
            
            {{-- Total Unique Students --}}
            <div class="ouk-kpi-card">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label">Total Unique Students</span>
                    <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-100">Database total</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($stats['students']) }}</span>
                        <span class="kpi-sub-pill">Enrolled Active <i data-lucide="user-check" class="w-3.5 h-3.5 inline text-teal-600"></i></span>
                    </div>
                    <div class="kpi-subtitle">Active registered learners across all faculties</div>
                    <div class="kpi-badge-list">
                        <div class="kpi-badge kpi-badge-blue">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Registered students: {{ number_format($stats['students']) }}</span>
                        </div>
                        <div class="kpi-badge kpi-badge-green">
                            <i data-lucide="award" class="w-3.5 h-3.5"></i>
                            <span>Admission pipeline: {{ number_format($pendingApps) }}</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-footer">
                    <a href="{{ route('students.index') }}" class="kpi-eye-btn" title="View Students" aria-label="View Students directory">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- Instructors & Faculty --}}
            <div class="ouk-kpi-card">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label">Instructors & Faculty</span>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ $exec['academicHealth']['studentFacultyRatio'] }} Ratio</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($stats['staff']) }}</span>
                        <span class="kpi-sub-pill">Active staff <i data-lucide="graduation-cap" class="w-3.5 h-3.5 inline text-emerald-600"></i></span>
                    </div>
                    <div class="kpi-subtitle">Teaching faculty and academic staff</div>
                    <div class="kpi-badge-list">
                        <div class="kpi-badge kpi-badge-green">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                            <span>Active staff: {{ number_format($stats['staff']) }}</span>
                        </div>
                        <div class="kpi-badge kpi-badge-blue">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span>Student ratio: {{ $exec['academicHealth']['studentFacultyRatio'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-footer">
                    <a href="#instructors" class="kpi-eye-btn" title="View Staff" aria-label="View Staff directory">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- Active Courses & Programmes --}}
            <div class="ouk-kpi-card">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label">Courses & Programmes</span>
                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">{{ $exec['academicHealth']['accreditedCount'] }} tracked</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ number_format($stats['courses']) }}</span>
                        <span class="kpi-sub-pill">{{ number_format($stats['courses']) }} active <i data-lucide="layers" class="w-3.5 h-3.5 inline text-blue-600"></i></span>
                    </div>
                    <div class="kpi-subtitle">Degree, Diploma and Professional courses</div>
                    <div class="kpi-badge-list">
                        <div class="kpi-badge kpi-badge-blue">
                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                            <span>Modules: {{ number_format($stats['subjects']) }}</span>
                        </div>
                        <div class="kpi-badge kpi-badge-green">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Academic coverage: {{ $exec['academicHealth']['complianceRate'] }}%</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-footer">
                    <a href="{{ route('courses.index') }}" class="kpi-eye-btn" title="View Courses" aria-label="View Courses directory">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- Attendance & Operations Rate --}}
            <div class="ouk-kpi-card">
                <div class="kpi-header flex justify-between items-center">
                    <span class="kpi-label">Operations & Attendance</span>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Optimal</span>
                </div>
                <div class="kpi-body">
                    <div class="kpi-metric-row">
                        <span class="kpi-big-number">{{ $attendanceRate }}%</span>
                        <span class="kpi-sub-pill">Live Telemetry <i data-lucide="activity" class="w-3.5 h-3.5 inline text-teal-600"></i></span>
                    </div>
                    <div class="kpi-subtitle">Lecture attendance and LMS session engagement</div>
                    <div class="kpi-badge-list">
                        <div class="kpi-badge kpi-badge-green">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            <span>Recorded attendance: {{ $attendanceRate }}%</span>
                        </div>
                        <div class="kpi-badge kpi-badge-blue">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                            <span>Audit readiness: {{ $exec['governance']['auditReadiness'] }}%</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-footer">
                    <button type="button" class="kpi-eye-btn" title="View Details" aria-label="View Attendance details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

        </div>
    </section>

    {{-- SECTION 8: EXECUTIVE DECISION INTELLIGENCE HUB (VC / DVC COMMAND) --}}
    <section class="ouk-section mb-6" id="executive-hub-section">
        <div class="ouk-panel" style="border-left: 4px solid var(--primary); background: linear-gradient(to right, #ffffff, #fbfdfd);">
            <div class="panel-header-row mb-3 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-md bg-teal-50 text-teal-800">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h2 class="panel-chart-title text-sm font-bold text-gray-900">Executive Decision Intelligence Hub</h2>
                        <p class="panel-chart-subtitle text-xs text-gray-500">Telemetry & quick decision triggers for VC, DVCs and Senate leadership</p>
                    </div>
                </div>
                
                {{-- Executive Filter Lens --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <select class="ouk-select text-xs font-semibold" aria-label="Executive role view">
                        <option>👑 Vice Chancellor Overview</option>
                        <option>🎓 DVC Academic Affairs</option>
                        <option>💰 DVC Finance & Planning</option>
                        <option>🔬 DVC Research & Innovation</option>
                    </select>
                    <select class="ouk-select text-xs font-semibold" aria-label="Intake session">
                        <option>September 2026 (Live Intake)</option>
                        <option>May 2026</option>
                        <option>January 2026</option>
                    </select>
                    <button type="button" class="btn btn-secondary text-xs py-1.5 px-3" data-modal-open="stakeholder-login-modal">
                        <i data-lucide="scan-face" class="w-3.5 h-3.5 text-teal-700"></i>Log in as stakeholder
                    </button>
                </div>
            </div>

            {{-- 4 Executive Decision Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                {{-- Card 1: Fiscal Inflow & Tuition --}}
                <div class="p-3 bg-white rounded-lg border border-slate-200 shadow-sm hover:border-teal-600 transition-colors">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tuition & Revenue</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $exec['financials']['yoy'] }} YoY</span>
                    </div>
                    <div class="text-lg font-bold text-slate-900 leading-tight">{{ $exec['financials']['collected'] }}</div>
                    <div class="flex justify-between text-[11px] text-slate-600 mt-1 mb-1.5">
                        <span>Target: {{ $exec['financials']['target'] }}</span>
                        <span class="font-bold text-teal-800">{{ $exec['financials']['rate'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $exec['financials']['rate'] }}%"></div>
                    </div>
                    <div class="text-[10.5px] text-slate-500 mt-2 flex items-center justify-between">
                        <span>Uncollected: {{ $exec['financials']['outstanding'] }}</span>
                        <a href="#broadcast" class="text-orange-600 font-bold hover:underline">Notify Deans →</a>
                    </div>
                </div>

                {{-- Card 2: Academic Capacity & Accreditation --}}
                <div class="p-3 bg-white rounded-lg border border-slate-200 shadow-sm hover:border-teal-600 transition-colors">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Programme Quality</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">Database coverage</span>
                    </div>
                    <div class="text-lg font-bold text-slate-900 leading-tight">{{ $exec['academicHealth']['complianceRate'] }}% Compliant</div>
                    <div class="flex justify-between text-[11px] text-slate-600 mt-1 mb-1.5">
                        <span>Faculty Ratio: {{ $exec['academicHealth']['studentFacultyRatio'] }}</span>
                        <span class="font-bold text-slate-800">{{ $exec['academicHealth']['accreditedCount'] }}/{{ $exec['academicHealth']['totalProgrammes'] }} Prog</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-teal-700 h-1.5 rounded-full" style="width: {{ $exec['academicHealth']['complianceRate'] }}%"></div>
                    </div>
                    <div class="text-[10.5px] text-slate-500 mt-2 flex items-center justify-between">
                        <span>Tracked modules: {{ number_format($stats['subjects']) }}</span>
                        <a href="{{ route('courses.index') }}" class="text-orange-600 font-bold hover:underline">Review caps →</a>
                    </div>
                </div>

                {{-- Card 3: Student Retention & Risk Telemetry --}}
                <div class="p-3 bg-white rounded-lg border border-slate-200 shadow-sm hover:border-teal-600 transition-colors">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Retention Telemetry</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $exec['retention']['dropOffDelta'] }} Drop</span>
                    </div>
                    <div class="text-lg font-bold text-slate-900 leading-tight">{{ $exec['retention']['rate'] }}% Term Retention</div>
                    <div class="flex justify-between text-[11px] text-slate-600 mt-1 mb-1.5">
                        <span>Active Advisories: {{ $exec['retention']['interventionsActive'] }}</span>
                        <span class="font-bold text-amber-700">{{ $exec['retention']['atRiskCount'] }} Flags</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $exec['retention']['rate'] }}%"></div>
                    </div>
                    <div class="text-[10.5px] text-slate-500 mt-2 flex items-center justify-between">
                        <span>Risk alerts resolved</span>
                        <a href="{{ route('students.index') }}" class="text-orange-600 font-bold hover:underline">Action queue →</a>
                    </div>
                </div>

                {{-- Card 4: Research & Innovation Pipeline --}}
                <div class="p-3 bg-white rounded-lg border border-slate-200 shadow-sm hover:border-teal-600 transition-colors">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Research & Enterprise</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-100">Persisted records</span>
                    </div>
                    <div class="text-lg font-bold text-slate-900 leading-tight">{{ $exec['research']['grantsTotal'] }}</div>
                    <div class="flex justify-between text-[11px] text-slate-600 mt-1 mb-1.5">
                        <span>Active Grants: {{ $exec['research']['activeProjects'] }}</span>
                        <span class="font-bold text-purple-800">{{ $exec['research']['publicationsYtd'] }} Papers</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $exec['research']['activeProjects'] > 0 ? 100 : 0 }}%"></div>
                    </div>
                    <div class="text-[10.5px] text-slate-500 mt-2 flex items-center justify-between">
                        <span>{{ $exec['research']['innovationPipeline'] }} innovations in pipeline</span>
                        <a href="#grants" class="text-orange-600 font-bold hover:underline">View pipeline →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 9: OPERATIONS COMMAND CENTER --}}
    <section class="ouk-section mb-6" id="operations-command-center">
        <div class="panel-header-row">
            <div>
                <h2 class="panel-chart-title">Operations Command Center</h2>
                <p class="panel-chart-subtitle">Priorities, system signals and the latest activity across MEMA ERP.</p>
            </div>
            <span class="kpi-badge kpi-badge-green"><i data-lucide="activity" class="w-3.5 h-3.5"></i>Live workspace</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="ouk-panel lg:col-span-2">
                <div class="panel-header-row mb-3">
                    <div>
                        <h3 class="panel-chart-title">Action Center</h3>
                        <p class="panel-chart-subtitle">Items that need attention today.</p>
                    </div>
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-orange-500"></i>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('admissions.index') }}" class="p-3 rounded-lg border border-orange-200 bg-orange-50 flex items-center justify-between gap-3 text-inherit no-underline hover:border-orange-400 transition-colors">
                        <span class="flex items-center gap-3"><i data-lucide="file-clock" class="w-5 h-5 text-orange-600"></i><span><strong class="block text-sm text-slate-900">Pending admissions</strong><small class="text-slate-500">Review applications in queue</small></span></span>
                        <strong class="text-lg text-orange-700">{{ number_format($pendingApps) }}</strong>
                    </a>
                    <a href="{{ route('students.index') }}" class="p-3 rounded-lg border border-amber-200 bg-amber-50 flex items-center justify-between gap-3 text-inherit no-underline hover:border-amber-400 transition-colors">
                        <span class="flex items-center gap-3"><i data-lucide="user-round-search" class="w-5 h-5 text-amber-700"></i><span><strong class="block text-sm text-slate-900">Retention watch</strong><small class="text-slate-500">Students flagged for follow-up</small></span></span>
                        <strong class="text-lg text-amber-700">{{ $exec['retention']['atRiskCount'] }}</strong>
                    </a>
                    <a href="{{ route('admissions.reports') }}" class="p-3 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between gap-3 text-inherit no-underline hover:border-teal-400 transition-colors">
                        <span class="flex items-center gap-3"><i data-lucide="file-check-2" class="w-5 h-5 text-teal-700"></i><span><strong class="block text-sm text-slate-900">Senate approvals</strong><small class="text-slate-500">Decisions awaiting review</small></span></span>
                        <strong class="text-lg text-teal-700">{{ $exec['governance']['pendingSenateApprovals'] }}</strong>
                    </a>
                    <a href="{{ route('courses.index') }}" class="p-3 rounded-lg border border-emerald-200 bg-emerald-50 flex items-center justify-between gap-3 text-inherit no-underline hover:border-emerald-400 transition-colors">
                        <span class="flex items-center gap-3"><i data-lucide="graduation-cap" class="w-5 h-5 text-emerald-700"></i><span><strong class="block text-sm text-slate-900">Programme quality</strong><small class="text-slate-500">Accreditation readiness</small></span></span>
                        <strong class="text-lg text-emerald-700">{{ $exec['academicHealth']['complianceRate'] }}%</strong>
                    </a>
                </div>
            </div>

            <div class="ouk-panel">
                <div class="panel-header-row mb-3">
                    <div>
                        <h3 class="panel-chart-title">Financial Snapshot</h3>
                        <p class="panel-chart-subtitle">Current collection position.</p>
                    </div>
                    <i data-lucide="wallet-cards" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ $exec['financials']['collected'] }}</div>
                <div class="flex justify-between mt-2 text-xs text-slate-500"><span>Target {{ $exec['financials']['target'] }}</span><strong class="text-emerald-700">{{ $exec['financials']['rate'] }}%</strong></div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden"><div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $exec['financials']['rate'] }}%"></div></div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs"><span class="text-slate-500">Outstanding</span><strong class="text-orange-700">{{ $exec['financials']['outstanding'] }}</strong></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="ouk-panel">
                <div class="panel-header-row mb-3"><h3 class="panel-chart-title">LMS Health</h3><i data-lucide="laptop-minimal-check" class="w-5 h-5 text-teal-700"></i></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span><strong class="text-sm text-slate-900">Integration monitoring</strong></div>
                <p class="panel-chart-subtitle mt-2">Sync metrics will appear when Moodle services are connected.</p>
                <a href="#lms" class="inline-flex items-center gap-1 mt-3 text-xs font-bold text-orange-600 no-underline">Open LMS workspace <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i></a>
            </div>
            <div class="ouk-panel">
                <div class="panel-header-row mb-3"><h3 class="panel-chart-title">System Health</h3><i data-lucide="server-cog" class="w-5 h-5 text-teal-700"></i></div>
                <div class="grid grid-cols-2 gap-3 text-sm"><div><span class="block text-xs text-slate-500">Queue</span><strong class="text-slate-700">Review status</strong></div><div><span class="block text-xs text-slate-500">Database</span><strong class="text-emerald-700">Connected</strong></div><div><span class="block text-xs text-slate-500">Audit</span><strong class="text-emerald-700">{{ $exec['governance']['auditReadiness'] }}%</strong></div><div><span class="block text-xs text-slate-500">Backups</span><strong class="text-slate-700">Review status</strong></div></div>
                <a href="{{ route('admin.setups.index') }}" class="inline-flex items-center gap-1 mt-3 text-xs font-bold text-orange-600 no-underline">Open admin setups <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i></a>
            </div>
            <div class="ouk-panel">
                <div class="panel-header-row mb-3"><h3 class="panel-chart-title">Quick Actions</h3><i data-lucide="zap" class="w-5 h-5 text-orange-500"></i></div>
                <div class="grid grid-cols-2 gap-2"><a href="{{ route('students.index') }}" class="btn btn-secondary justify-center text-xs py-2 no-underline"><i data-lucide="user-plus" class="w-3.5 h-3.5"></i>Add student</a><a href="{{ route('courses.index') }}" class="btn btn-secondary justify-center text-xs py-2 no-underline"><i data-lucide="book-plus" class="w-3.5 h-3.5"></i>New course</a><a href="{{ route('admissions.index') }}" class="btn btn-secondary justify-center text-xs py-2 no-underline"><i data-lucide="inbox" class="w-3.5 h-3.5"></i>Applications</a><a href="{{ route('admissions.reports') }}" class="btn btn-secondary justify-center text-xs py-2 no-underline"><i data-lucide="download" class="w-3.5 h-3.5"></i>Reports</a></div>
            </div>
        </div>

        <div class="ouk-panel mt-4">
            <div class="panel-header-row mb-2"><div><h3 class="panel-chart-title">Recent Activity</h3><p class="panel-chart-subtitle">Latest records entering the system.</p></div><i data-lucide="history" class="w-5 h-5 text-slate-500"></i></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8">
                @forelse(($students ?? collect())->take(3) as $student)
                    <div class="flex items-center justify-between gap-3 py-3 border-b border-slate-100"><span class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-teal-50 text-teal-800 grid place-items-center text-xs font-bold">{{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}</span><span><strong class="block text-sm text-slate-800">{{ $student->user?->name ?? 'Student record' }}</strong><small class="text-xs text-slate-500">New student record</small></span></span><i data-lucide="user-round-plus" class="w-4 h-4 text-emerald-600"></i></div>
                @empty
                    <p class="empty">No recent student activity.</p>
                @endforelse
                @forelse(($results ?? collect())->take(3) as $result)
                    <div class="flex items-center justify-between gap-3 py-3 border-b border-slate-100"><span class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-orange-50 text-orange-700 grid place-items-center text-xs font-bold">{{ strtoupper(substr($result->subject?->name ?? 'R', 0, 1)) }}</span><span><strong class="block text-sm text-slate-800">{{ $result->subject?->name ?? 'Assessment result' }}</strong><small class="text-xs text-slate-500">Recent result recorded</small></span></span><i data-lucide="file-check-2" class="w-4 h-4 text-orange-600"></i></div>
                @empty
                    <p class="empty">No recent result activity.</p>
                @endforelse
            </div>
        </div>
    </section>

    @php
        $trendCount = max(1, count($trends));
        $trendMax = max(1, collect($trends)->max('value'));
        $trendCoordinates = collect($trends)->values()->map(function ($trend, $index) use ($trendCount, $trendMax) {
            return [
                'x' => $trendCount > 1 ? 28 + (($index / ($trendCount - 1)) * 544) : 300,
                'y' => 142 - (($trend['value'] / $trendMax) * 112),
            ];
        })->all();
        $trendPoints = collect($trendCoordinates)->map(fn ($point) => round($point['x']).','.round($point['y']))->implode(' ');
        $trendPath = collect($trendCoordinates)->reduce(function ($path, $point, $index) use ($trendCoordinates) {
            if ($index === 0) {
                return 'M '.round($point['x'], 1).' '.round($point['y'], 1);
            }

            $previous = $trendCoordinates[$index - 1];
            $previousPrevious = $trendCoordinates[$index - 2] ?? $previous;
            $next = $trendCoordinates[$index + 1] ?? $point;
            $controlOneX = $previous['x'] + (($point['x'] - $previousPrevious['x']) / 6);
            $controlOneY = $previous['y'] + (($point['y'] - $previousPrevious['y']) / 6);
            $controlTwoX = $point['x'] - (($next['x'] - $previous['x']) / 6);
            $controlTwoY = $point['y'] - (($next['y'] - $previous['y']) / 6);

            return $path.' C '.round($controlOneX, 1).' '.round($controlOneY, 1).', '.round($controlTwoX, 1).' '.round($controlTwoY, 1).', '.round($point['x'], 1).' '.round($point['y'], 1);
        }, '');
    @endphp
    <section class="ouk-section mb-6" id="dashboard-visual-insights">
        <div class="panel-header-row">
            <div>
                <h2 class="panel-chart-title">Visual Insights</h2>
                <p class="panel-chart-subtitle">See movement, conversion and institutional pressure at a glance.</p>
            </div>
            <a href="{{ route('admissions.analytics') }}" class="btn btn-secondary text-xs py-1.5 px-3 no-underline"><i data-lucide="chart-no-axes-combined" class="w-3.5 h-3.5"></i>Open analytics</a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 items-start gap-4">
            <div class="ouk-panel self-start h-fit">
                <div class="panel-header-row mb-2">
                    <div><h3 class="panel-chart-title">Admissions Funnel</h3><p class="panel-chart-subtitle">Conversion from interest to confirmed enrollment.</p></div>
                    <i data-lucide="filter" class="w-5 h-5 text-orange-500"></i>
                </div>
                <div class="py-2" aria-label="Admissions conversion funnel">
                    @php($funnelStages = [['label' => 'Applications', 'value' => $apps, 'width' => 100, 'color' => '#0a3e50'], ['label' => 'Admitted', 'value' => $admitted, 'width' => 82, 'color' => '#08758b'], ['label' => 'Accepted', 'value' => $accepted, 'width' => 64, 'color' => '#1e8449'], ['label' => 'Enrolled', 'value' => $enrolled, 'width' => 46, 'color' => '#d2a24a']])
                    @foreach ($funnelStages as $funnelStage)
                        <div class="mx-auto" style="width:{{ $funnelStage['width'] }}%;height:53px;margin-top:{{ $loop->first ? 0 : '-1px' }};position:relative;z-index:{{ 5 - $loop->index }};filter:drop-shadow(0 7px 5px rgb(15 23 42 / 12%))">
                            <div aria-hidden="true" style="position:absolute;inset:8px 0 0;background:{{ $funnelStage['color'] }};filter:brightness(.62);clip-path:polygon(0 0,100% 0,{{ $loop->last ? 82 : 92 }}% 100%,{{ $loop->last ? 18 : 8 }}% 100%)"></div>
                            <div class="flex h-11 items-center justify-center text-center text-white" style="position:absolute;inset:0 0 9px;background:linear-gradient(135deg,rgb(255 255 255 / 24%),transparent 34%),linear-gradient(315deg,rgb(0 0 0 / 18%),transparent 42%),{{ $funnelStage['color'] }};clip-path:polygon(0 0,100% 0,{{ $loop->last ? 82 : 92 }}% 100%,{{ $loop->last ? 18 : 8 }}% 100%)">
                                <span class="text-xs font-bold tracking-wide">{{ $funnelStage['label'] }} <span class="font-medium opacity-80">{{ number_format($funnelStage['value']) }}</span></span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs"><span class="text-slate-500">Application to enrollment conversion</span><strong class="text-teal-800">{{ $apps > 0 ? number_format(($enrolled / $apps) * 100, 1) : '0.0' }}%</strong></div>
            </div>

            {{-- Right Panel: Upgraded Application Trend --}}
            <div class="ouk-panel">
                <div class="panel-header-row mb-3 flex-wrap gap-2">
                    <div>
                        <h3 class="panel-chart-title">Application Trend</h3>
                        <p class="panel-chart-subtitle">Monthly application volume & live intake trajectory.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="kpi-badge kpi-badge-blue">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>{{ number_format($apps) }} total
                        </span>
                        <select class="ouk-select text-xs font-semibold" aria-label="Trend Timeframe">
                            <option>2026 Academic Cycle</option>
                            <option>Live Intake (Sep 2026)</option>
                            <option>Previous Cycle (2025)</option>
                        </select>
                    </div>
                </div>

                {{-- Trend Summary Mini-Bar --}}
                <div class="grid grid-cols-4 gap-2 mb-3 p-2.5 bg-slate-50/80 rounded-lg border border-slate-100 text-center">
                    <div>
                        <div class="text-[10.5px] font-medium text-slate-500">Latest month</div>
                        <div class="text-xs font-bold text-slate-800">{{ number_format($latestTrend['value'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="text-[10.5px] font-medium text-slate-500">Year total</div>
                        <div class="text-xs font-bold text-teal-700">{{ number_format($trendValues->sum()) }}</div>
                    </div>
                    <div>
                        <div class="text-[10.5px] font-medium text-slate-500">Cycle Peak</div>
                        <div class="text-xs font-bold text-orange-600">{{ number_format($peakTrend['value'] ?? 0) }} <span class="text-[9px] text-slate-400">({{ $peakTrend['month'] ?? '—' }})</span></div>
                    </div>
                    <div>
                        <div class="text-[10.5px] font-medium text-slate-500">Monthly Avg</div>
                        <div class="text-xs font-bold text-slate-700">{{ number_format($monthlyAverage) }} / mo</div>
                    </div>
                </div>

                <?php
                    $enrichedTrends = collect($trends)->map(fn ($item) => [
                        'month' => $item['month'],
                        'val' => $item['value'],
                        'type' => 'actual',
                        'is_peak' => ($item['month'] === ($peakTrend['month'] ?? null)) && ($item['value'] > 0),
                    ])->all();

                    $tW = 680;
                    $tH = 250;
                    $tPadL = 38;
                    $tPadR = 24;
                    $tPadT = 30;
                    $tPadB = 32;
                    $rawTMax = max(array_column($enrichedTrends, 'val') ?: [0]);
                    if ($rawTMax <= 10) {
                        $tMaxY = 10;
                        $tYTicks = [0, 2, 4, 6, 8, 10];
                    } elseif ($rawTMax <= 25) {
                        $tMaxY = 25;
                        $tYTicks = [0, 5, 10, 15, 20, 25];
                    } elseif ($rawTMax <= 50) {
                        $tMaxY = 50;
                        $tYTicks = [0, 10, 20, 30, 40, 50];
                    } elseif ($rawTMax <= 100) {
                        $tMaxY = 100;
                        $tYTicks = [0, 20, 40, 60, 80, 100];
                    } elseif ($rawTMax <= 150) {
                        $tMaxY = 150;
                        $tYTicks = [0, 30, 60, 90, 120, 150];
                    } elseif ($rawTMax <= 250) {
                        $tMaxY = 250;
                        $tYTicks = [0, 50, 100, 150, 200, 250];
                    } elseif ($rawTMax <= 500) {
                        $tMaxY = 500;
                        $tYTicks = [0, 100, 200, 300, 400, 500];
                    } else {
                        $step = (int) max(50, ceil(($rawTMax / 5) / 50) * 50);
                        $tMaxY = $step * 5;
                        $tYTicks = range(0, $tMaxY, $step);
                    }

                    $tPlotW = $tW - $tPadL - $tPadR;
                    $tPlotH = $tH - $tPadT - $tPadB;
                    $tCount = count($enrichedTrends);

                    $tPoints = [];
                    foreach ($enrichedTrends as $idx => $item) {
                        $pX = $tPadL + ($idx * ($tPlotW / max(1, $tCount - 1)));
                        $pY = ($tH - $tPadB) - (($item['val'] / max(1, $tMaxY)) * $tPlotH);
                        $tPoints[] = array_merge($item, ['x' => $pX, 'y' => $pY]);
                    }

                    // Build smooth cubic bezier
                    $splinePath = "M " . round($tPoints[0]['x'], 1) . " " . round($tPoints[0]['y'], 1);
                    for ($i = 0; $i < $tCount - 1; $i++) {
                        $p0 = $tPoints[max(0, $i - 1)];
                        $p1 = $tPoints[$i];
                        $p2 = $tPoints[$i + 1];
                        $p3 = $tPoints[min($tCount - 1, $i + 2)];

                        $cp1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                        $cp1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                        $cp2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                        $cp2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;

                        $splinePath .= " C " . round($cp1x, 1) . " " . round($cp1y, 1) . ", " . round($cp2x, 1) . " " . round($cp2y, 1) . ", " . round($p2['x'], 1) . " " . round($p2['y'], 1);
                    }

                    $areaPath = $splinePath . " L " . round($tPoints[$tCount - 1]['x'], 1) . " " . ($tH - $tPadB) . " L " . round($tPoints[0]['x'], 1) . " " . ($tH - $tPadB) . " Z";
                ?>

                <div class="relative overflow-hidden rounded-lg border border-slate-200/80 bg-white p-2.5 shadow-2xs">
                    <svg viewBox="0 0 {{ $tW }} {{ $tH }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Monthly application trend chart" class="w-full h-auto">
                        <defs>
                            <linearGradient id="appTrendGradNew" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#007A8C" stop-opacity="0.32" />
                                <stop offset="45%" stop-color="#007A8C" stop-opacity="0.14" />
                                <stop offset="100%" stop-color="#007A8C" stop-opacity="0.01" />
                            </linearGradient>

                            <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#007A8C" flood-opacity="0.25" />
                            </filter>
                        </defs>

                        {{-- Dotted horizontal gridlines and Y labels --}}
                        <?php foreach ($tYTicks as $gVal): ?>
                            <?php $gY = ($tH - $tPadB) - (($gVal / max(1, $tMaxY)) * $tPlotH); ?>
                            <line x1="{{ $tPadL }}" y1="{{ $gY }}" x2="{{ $tW - $tPadR }}" y2="{{ $gY }}" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 4" />
                            <text x="{{ $tPadL - 8 }}" y="{{ $gY + 3.5 }}" text-anchor="end" font-size="9.5" fill="#94A3B8" font-family="Quicksand, sans-serif" font-weight="600">
                                {{ $gVal >= 1000 ? ($gVal / 1000) . 'k' : $gVal }}
                            </text>
                        <?php endforeach; ?>

                        {{-- Vertical subtle column guides --}}
                        <?php foreach ($tPoints as $pt): ?>
                            <line x1="{{ $pt['x'] }}" y1="{{ $tPadT }}" x2="{{ $pt['x'] }}" y2="{{ $tH - $tPadB }}" stroke="#F8FAFC" stroke-width="1" />
                        <?php endforeach; ?>

                        {{-- Area fill --}}
                        <path d="{{ $areaPath }}" fill="url(#appTrendGradNew)" />

                        {{-- Spline Line with glow --}}
                        <path d="{{ $splinePath }}" fill="none" stroke="#007A8C" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" filter="url(#softGlow)" />

                        {{-- Data points and Month Labels --}}
                        <?php foreach ($tPoints as $pt): ?>
                            <?php
                                $isPeak = !empty($pt['is_peak']);
                                $isLive = ($pt['type'] ?? '') === 'live';
                                $isProj = ($pt['type'] ?? '') === 'projection';
                                $dotColor = $isPeak ? '#ea580c' : ($isLive ? '#059669' : ($isProj ? '#0284c7' : '#007A8C'));
                            ?>
                            
                            {{-- Peak Callout Bubble --}}
                            <?php if ($isPeak): ?>
                                <g transform="translate({{ $pt['x'] }}, {{ $pt['y'] - 18 }})">
                                    <rect x="-34" y="-14" width="68" height="18" rx="9" fill="#EA580C" filter="drop-shadow(0 2px 4px rgba(234,88,12,0.3))" />
                                <text x="0" y="-2" text-anchor="middle" font-size="9" font-weight="700" fill="#FFFFFF" font-family="Quicksand, sans-serif">Peak {{ number_format($pt['val']) }}</text>
                                </g>
                            <?php endif; ?>

                            {{-- Live Intake Indicator --}}
                            <?php if ($isLive): ?>
                                <g transform="translate({{ $pt['x'] }}, {{ $pt['y'] - 16 }})">
                                    <rect x="-28" y="-13" width="56" height="16" rx="8" fill="#059669" filter="drop-shadow(0 2px 3px rgba(5,150,105,0.25))" />
                                    <text x="0" y="-2" text-anchor="middle" font-size="8.5" font-weight="700" fill="#FFFFFF" font-family="Quicksand, sans-serif">Live {{ number_format($pt['val']) }}</text>
                                </g>
                            <?php endif; ?>

                            {{-- Outer halo on hover --}}
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="7" fill="{{ $dotColor }}" opacity="0.18" class="transition-all duration-150" />
                            
                            {{-- Inner Core Dot --}}
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#FFFFFF" stroke="{{ $dotColor }}" stroke-width="2.5" class="cursor-pointer transition-all duration-150 hover:scale-125">
                                <title>{{ $pt['month'] }}: {{ number_format($pt['val']) }} applications ({{ $pt['type'] }})</title>
                            </circle>

                            {{-- Month label --}}
                            <text x="{{ $pt['x'] }}" y="{{ $tH - 12 }}" text-anchor="middle" font-size="10" font-weight="{{ ($isPeak || $isLive) ? '700' : '600' }}" fill="{{ $isPeak ? '#ea580c' : ($isLive ? '#059669' : '#64748B') }}" font-family="Quicksand, sans-serif">
                                {{ $pt['month'] }}
                            </text>
                        <?php endforeach; ?>
                    </svg>

                    {{-- Footer Legend --}}
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-100 mt-1">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#007A8C]"></span>Actuals</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#059669]"></span>Sep Live Intake</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#0284c7]"></span>Q4 Projection</span>
                        </div>
                        <span class="text-teal-800 font-bold">{{ number_format($trendValues->sum()) }} YTD cycle</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ouk-panel mt-4">
            <div class="panel-header-row mb-3">
                <div><h3 class="panel-chart-title">Executive Signal Heatmap</h3><p class="panel-chart-subtitle">Relative health across the institution’s decision areas.</p></div>
                <div class="flex items-center gap-2 text-[10px] text-slate-500"><span>Lower</span><span class="h-2.5 w-8 rounded-sm bg-orange-500"></span><span class="h-2.5 w-8 rounded-sm bg-amber-300"></span><span class="h-2.5 w-8 rounded-sm bg-emerald-500"></span><span>Higher</span></div>
            </div>
            <div class="overflow-x-auto">
                <?php
                    $signalRows = $signalRows;
                ?>
                <div class="min-w-[640px] grid grid-cols-[1.55fr_repeat(5,1fr)] gap-1.5 text-xs">
                    <div class="p-2 text-slate-400"></div>
                    <?php foreach (['Current', 'Target', 'Momentum', 'Stability', 'Coverage'] as $heatmapHeading): ?>
                        <div class="p-2 text-center font-bold text-slate-500">{{ $heatmapHeading }}</div>
                    <?php endforeach; ?>
                    <?php foreach ($signalRows as $signalRow): ?>
                        <div class="flex items-center rounded bg-slate-50 p-3 font-semibold text-slate-700">{{ $signalRow['label'] }}</div>
                        <?php foreach ($signalRow['values'] as $signalValue): ?>
                            <?php $heatmapBand = $signalValue >= 90 ? 'bg-emerald-500 text-white' : ($signalValue >= 75 ? 'bg-emerald-200 text-emerald-950' : ($signalValue >= 60 ? 'bg-amber-200 text-amber-950' : 'bg-orange-500 text-white')); ?>
                            <div class="flex min-h-12 items-center justify-center rounded font-bold {{ $heatmapBand }}" title="{{ $signalRow['label'] }}: {{ $signalValue }}%">{{ number_format($signalValue, 1) }}%</div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    {{-- DASHBOARD EXPORT MODAL --}}
    <div class="modal" id="dashboard-export-modal" role="dialog" aria-modal="true">
        <div class="modal-card" style="width:min(520px, 94vw);">
            <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
                <div class="flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4 text-[#E67E22]"></i>
                    <div>
                        <h2 class="text-sm font-bold text-white">Export Institutional Intelligence Data</h2>
                        <small style="color:rgba(255,255,255,0.85);">Export live filtered datasets in Excel, CSV, or formatted PDF</small>
                    </div>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <form class="panel-body p-5" method="GET" action="{{ route('dashboard.export') }}" target="_blank">
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Select Dataset to Export <span class="text-red-500">*</span></label>
                        <select name="dataset" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                            <option value="applications" selected>Admission Applications Register (Full Details)</option>
                            <option value="admissions">Official Admitted Candidates Roster</option>
                            <option value="enrolments">Enrolled Student Nominal Roll (Active & Registered)</option>
                            <option value="graduated">Graduation & Alumni Registry</option>
                            <option value="programmes">Academic Programmes & Curriculum Roster</option>
                            <option value="financials">Financial Collections & Fee Payment Ledger</option>
                            <option value="demographics">Applicant Demographics & Regional Distribution</option>
                            <option value="staff">University Staff & Academic Faculty Directory</option>
                            <option value="executive_kpis">Institutional Executive Scorecard & KPIs</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Export Format <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2.5">
                            <label class="border border-slate-300 rounded-lg p-3 flex flex-col items-center justify-center cursor-pointer hover:border-[#0A3E50] has-[:checked]:border-[#0A3E50] has-[:checked]:bg-teal-50/50 transition-all">
                                <input type="radio" name="format" value="xlsx" checked class="sr-only">
                                <i data-lucide="file-spreadsheet" class="w-5 h-5 text-emerald-600 mb-1"></i>
                                <span class="font-bold text-slate-800 text-[11px]">Excel (.xlsx)</span>
                                <span class="text-[9.5px] text-slate-500">OpenXML Sheet</span>
                            </label>
                            <label class="border border-slate-300 rounded-lg p-3 flex flex-col items-center justify-center cursor-pointer hover:border-[#0A3E50] has-[:checked]:border-[#0A3E50] has-[:checked]:bg-teal-50/50 transition-all">
                                <input type="radio" name="format" value="csv" class="sr-only">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-600 mb-1"></i>
                                <span class="font-bold text-slate-800 text-[11px]">CSV (.csv)</span>
                                <span class="text-[9.5px] text-slate-500">UTF-8 Comma</span>
                            </label>
                            <label class="border border-slate-300 rounded-lg p-3 flex flex-col items-center justify-center cursor-pointer hover:border-[#0A3E50] has-[:checked]:border-[#0A3E50] has-[:checked]:bg-teal-50/50 transition-all">
                                <input type="radio" name="format" value="pdf" class="sr-only">
                                <i data-lucide="file" class="w-5 h-5 text-rose-600 mb-1"></i>
                                <span class="font-bold text-slate-800 text-[11px]">Print / PDF</span>
                                <span class="text-[9.5px] text-slate-500">Quicksand report</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-600 space-y-1">
                        <div class="font-bold text-[#0A3E50] flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Live PostgreSQL Direct Extraction
                        </div>
                        <p>Exports reflect the current verified state of the database with institutional header, font typography in <strong>Quicksand</strong>, and system brand colors <strong>#0A3E50</strong> (Primary Dark Teal) and <strong>#E67E22</strong> (Accent Orange).</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-[#E67E22]"></i> Download Dataset
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- INTERACTIVE WEB VIEW DATA MODAL (SCREENSHOT 3 & 4 STYLE) --}}
    <div id="web-data-view-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-xs hidden" style="z-index:9999;">
        <div class="bg-white w-full max-w-7xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Teal Header --}}
            <div class="bg-[#007A8C] text-white px-6 py-3.5 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="table" class="w-5 h-5 text-teal-200"></i>
                    <h2 class="text-base font-extrabold text-white tracking-tight" id="data-modal-title">Applied</h2>
                </div>
                <button type="button" onclick="closeDataViewModal()" class="w-8 h-8 rounded-lg bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer text-lg font-bold">
                    &times;
                </button>
            </div>

            {{-- Action Bar: EXCEL, PDF, CSV, Search --}}
            <div class="px-6 py-3 bg-white border-b border-slate-200 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="exportDataModal('xlsx')" class="px-3.5 py-1 rounded border border-orange-400 text-orange-600 bg-white hover:bg-orange-50 font-extrabold text-xs tracking-wider transition-colors shadow-2xs cursor-pointer">
                        EXCEL
                    </button>
                    <button type="button" onclick="exportDataModal('pdf')" class="px-3.5 py-1 rounded border border-red-300 text-red-600 bg-white hover:bg-red-50 font-extrabold text-xs tracking-wider transition-colors shadow-2xs cursor-pointer">
                        PDF
                    </button>
                    <button type="button" onclick="exportDataModal('csv')" class="px-3.5 py-1 rounded border border-blue-300 text-blue-600 bg-white hover:bg-blue-50 font-extrabold text-xs tracking-wider transition-colors shadow-2xs cursor-pointer">
                        CSV
                    </button>
                    <span class="text-xs text-slate-500 font-medium ml-2" id="data-modal-count-badge"></span>
                </div>

                <div class="relative w-full sm:w-72">
                    <input type="text" id="data-modal-search" placeholder="Search..." oninput="filterDataModalTable()" class="w-full bg-slate-50 border border-slate-300 rounded px-3 py-1 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#007A8C] shadow-2xs">
                </div>
            </div>

            {{-- Table Container --}}
            <div class="flex-1 overflow-auto p-0 min-h-[300px]">
                <table class="w-full text-left border-collapse text-xs" id="data-modal-table">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-[#007A8C] text-white" id="data-modal-thead-tr">
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">S.No</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Reference Number</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">First Name</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Surname</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Programme Name</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Application Status</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Username / Email</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Mobile Number</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Date Of Birth</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Gender</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">Country</th>
                            <th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] whitespace-nowrap">County</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="data-modal-tbody">
                        <tr>
                            <td colspan="12" class="p-8 text-center text-slate-400">
                                <span class="w-4 h-4 border-2 border-[#007A8C] border-t-transparent rounded-full animate-spin inline-block mr-2"></span>
                                Fetching records from live database...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-2.5 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
                <span class="text-[11px]">Real-time Database Telemetry • MEMA ERP</span>
                <button type="button" onclick="closeDataViewModal()" class="px-3.5 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentModalDataset = 'applications';
        let currentModalRawRows = [];
        let currentModalHeaders = [];

        function openDashboardExportModal() {
            document.getElementById('dashboard-export-modal').classList.add('open');
        }

        const datasetTitles = {
            'applications': 'Applied',
            'in_progress': 'Applications In Progress',
            'interested': 'Interested Applicants',
            'reverify': 'Documents Requiring Re-verification',
            'admissions': 'Admitted',
            'l2_rejected': 'L2 Rejected Applications',
            'offer_rejected': 'Offer Rejected Applications',
            'enrolments': 'Enrolled Students Nominal Roll',
            'accepted': 'Accepted Offers',
            'initiated': 'Initiated Draft Applications',
            'graduated': 'Graduation & Alumni Registry',
            'programmes': 'Academic Programmes & Curriculum',
            'financials': 'Financial Collections & Fees Ledger'
        };

        function openDataViewModal(dataset) {
            currentModalDataset = dataset;
            const modal = document.getElementById('web-data-view-modal');
            const titleElem = document.getElementById('data-modal-title');
            const countBadge = document.getElementById('data-modal-count-badge');
            const tbody = document.getElementById('data-modal-tbody');
            const searchInput = document.getElementById('data-modal-search');

            if (titleElem) titleElem.textContent = datasetTitles[dataset] || dataset.toUpperCase();
            if (searchInput) searchInput.value = '';
            if (countBadge) countBadge.textContent = 'Querying live database...';
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="12" class="p-8 text-center text-slate-400"><span class="w-4 h-4 border-2 border-[#007A8C] border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Loading records...</td></tr>`;
            }

            modal.classList.remove('hidden');

            fetch(`{{ route('dashboard.records-preview') }}?dataset=${encodeURIComponent(dataset)}`)
                .then(r => r.json())
                .then(data => {
                    currentModalHeaders = data.headers || [];
                    currentModalRawRows = data.rows || [];
                    if (titleElem && data.title) titleElem.textContent = datasetTitles[dataset] || data.title;
                    if (countBadge) countBadge.textContent = `Showing ${currentModalRawRows.length} records`;
                    renderModalTableHeader(currentModalHeaders);
                    renderModalTableRows(currentModalRawRows);
                })
                .catch(err => {
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="12" class="p-8 text-center text-rose-600 font-bold">Failed to load dataset records. Please try again.</td></tr>`;
                    }
                });
        }

        function closeDataViewModal() {
            document.getElementById('web-data-view-modal').classList.add('hidden');
        }

        function renderModalTableHeader(headers) {
            const tr = document.getElementById('data-modal-thead-tr');
            if (!tr) return;
            let html = '<th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] border-r border-white/20 whitespace-nowrap">S.No</th>';
            headers.forEach((h, idx) => {
                const border = (idx < headers.length - 1) ? 'border-r border-white/20' : '';
                html += `<th class="py-2.5 px-3 font-bold text-white uppercase text-[11px] ${border} whitespace-nowrap">${h}</th>`;
            });
            tr.innerHTML = html;
        }

        function renderModalTableRows(rows) {
            const tbody = document.getElementById('data-modal-tbody');
            if (!tbody) return;
            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${currentModalHeaders.length + 1}" class="p-8 text-center text-slate-400">No records found matching current criteria.</td></tr>`;
                return;
            }

            let html = '';
            rows.forEach((row, i) => {
                const bg = i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50';
                html += `<tr class="${bg} hover:bg-teal-50/40 transition-colors border-b border-slate-100">`;
                html += `<td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">${i + 1}</td>`;
                row.forEach(cell => {
                    const text = (cell === null || cell === undefined || cell === '') ? 'NA' : cell;
                    let badgeClass = '';
                    if (text === 'ENROLLED' || text === 'ACCEPTED' || text === 'PAID' || text === 'VERIFIED') {
                        badgeClass = 'bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded font-bold text-[10.5px] inline-block';
                    } else if (text === 'SUBMITTED' || text === 'UNDER_REVIEW' || text === 'READY_TO_ENROL' || text === 'FOLLOW UP') {
                        badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200 px-2 py-0.5 rounded font-bold text-[10.5px] inline-block';
                    } else if (text === 'REJECTED' || text === 'DECLINED') {
                        badgeClass = 'bg-rose-50 text-rose-800 border border-rose-200 px-2 py-0.5 rounded font-bold text-[10.5px] inline-block';
                    }

                    if (badgeClass) {
                        html += `<td class="py-2.5 px-3"><span class="${badgeClass}">${text}</span></td>`;
                    } else {
                        html += `<td class="py-2.5 px-3 text-slate-800 font-medium whitespace-nowrap">${text}</td>`;
                    }
                });
                html += `</tr>`;
            });
            tbody.innerHTML = html;
        }

        function filterDataModalTable() {
            const query = (document.getElementById('data-modal-search').value || '').toLowerCase().trim();
            if (!query) {
                renderModalTableRows(currentModalRawRows);
                document.getElementById('data-modal-count-badge').textContent = `Showing ${currentModalRawRows.length} records`;
                return;
            }

            const filtered = currentModalRawRows.filter(row => {
                return row.some(cell => String(cell || '').toLowerCase().includes(query));
            });

            renderModalTableRows(filtered);
            document.getElementById('data-modal-count-badge').textContent = `Showing ${filtered.length} of ${currentModalRawRows.length} records`;
        }

        function exportDataModal(format) {
            const url = `{{ route('dashboard.export') }}?dataset=${encodeURIComponent(currentModalDataset)}&format=${encodeURIComponent(format)}`;
            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>

</div>
