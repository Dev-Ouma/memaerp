@extends('layouts.app')

@section('title', 'Admissions Command Dashboard')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Intake: September 2026 Academic Session
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admissions Command Centre &amp; Pipeline Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Real-time intake velocity, stage funnel bottlenecks, applicant qualification audits, and conversion yield</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admissions.catalogue') }}" target="_blank" class="px-3.5 py-1.5 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
                Public Catalogue
            </a>
            <a href="{{ route('admissions.reports.applications') }}" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Export Nominal CSV
            </a>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide flex justify-between items-center">
                <span>Total Applications</span>
                <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format($stats['totalApplications']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Direct &amp; KUCCPS candidates.</p>
            <div class="flex items-center justify-between text-[11px]">
                <span class="inline-block px-2 py-0.5 rounded font-bold text-blue-800 bg-blue-50 border border-blue-200">Persisted applications</span>
                <span class="text-emerald-700 font-bold">Live total</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide flex justify-between items-center">
                <span>Verified Applicants</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['verifiedApplicants']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Passed KCSE &amp; ID audit.</p>
            <div class="flex items-center justify-between text-[11px]">
                <span class="inline-block px-2 py-0.5 rounded font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Authenticity: {{ $stats['slaCompliance'] }}</span>
                <span class="text-slate-500 font-semibold">Database verified</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide flex justify-between items-center">
                <span>Offers Issued</span>
                <i data-lucide="award" class="w-4 h-4 text-orange-500"></i>
            </div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['offersIssued']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Senate approved admissions.</p>
            <div class="flex items-center justify-between text-[11px]">
                <span class="inline-block px-2 py-0.5 rounded font-bold text-amber-800 bg-amber-50 border border-amber-200">Acceptance: {{ $stats['acceptanceRate'] }}%</span>
                <span class="text-slate-500 font-semibold">89 awaiting</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide flex justify-between items-center">
                <span>Application Revenue</span>
                <i data-lucide="credit-card" class="w-4 h-4 text-[#1E8449]"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#1E8449] mt-2 mb-1.5 leading-none">KES {{ number_format($stats['revenueCollected']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">M-Pesa Daraja 2.0 &amp; Bank collections.</p>
            <div class="flex items-center justify-between text-[11px]">
                <span class="inline-block px-2 py-0.5 rounded font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Reconciled 100%</span>
                <span class="text-emerald-700 font-bold">KES 1.5K / app</span>
            </div>
        </div>
    </div>

    {{-- Funnel Pipeline Bar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-xs">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Intake Lifecycle Stage Distribution</h2>
            <span class="text-xs text-slate-500 font-medium">Conversion Velocity: <strong class="text-slate-900">{{ $stats['conversionRate'] }}% overall</strong></span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 lg:grid-cols-11 gap-2 text-center">
            @foreach([
                ['Drafts', $funnel['DRAFT'] ?? 0, 'bg-slate-100 text-slate-700 border-slate-300'],
                ['Submitted', $funnel['SUBMITTED'] ?? 0, 'bg-blue-50 text-blue-800 border-blue-200'],
                ['In Review', $funnel['UNDER_REVIEW'] ?? 0, 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                ['Verified', $funnel['VERIFIED'] ?? 0, 'bg-cyan-50 text-cyan-800 border-cyan-200'],
                ['Shortlisted', $funnel['SHORTLISTED'] ?? 0, 'bg-amber-50 text-amber-800 border-amber-200'],
                ['Approval', $funnel['APPROVAL_PENDING'] ?? 0, 'bg-purple-50 text-purple-800 border-purple-200'],
                ['Admitted', $funnel['ADMITTED'] ?? 0, 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                ['Accepted', $funnel['ACCEPTED'] ?? 0, 'bg-teal-50 text-teal-800 border-teal-200'],
                ['Ready Enrol', $funnel['READY_TO_ENROL'] ?? 0, 'bg-lime-50 text-lime-800 border-lime-200'],
                ['Enrolled', $funnel['ENROLLED'] ?? 0, 'bg-emerald-100 text-emerald-900 border-emerald-300'],
                ['Rejected', $funnel['REJECTED'] ?? 0, 'bg-red-50 text-red-800 border-red-200'],
            ] as [$stageName, $stageCount, $style])
                <div class="p-2.5 rounded-lg border {{ $style }} transition-transform hover:-translate-y-0.5">
                    <div class="text-[10px] uppercase font-bold tracking-wider opacity-80">{{ $stageName }}</div>
                    <div class="text-lg font-extrabold mt-0.5">{{ number_format($stageCount) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main 2 Column Grid: Recent Applications & Programme Quota Health --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Left 2 Cols: Recent Applications Queue --}}
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Recent Applications Requiring Action</h2>
                    <p class="text-xs text-slate-500">Live feed of incoming applicant dossiers and stage milestones</p>
                </div>
                <a href="{{ route('admissions.index') }}" class="text-xs font-bold text-[#0A3E50] hover:text-[#E67E22] transition-colors">
                    View All &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#0A3E50] text-white">
                            <th class="py-2.5 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">App Number &amp; Candidate</th>
                            <th class="py-2.5 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme</th>
                            <th class="py-2.5 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                            <th class="py-2.5 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Fee</th>
                            <th class="py-2.5 px-4 font-bold tracking-wider text-center uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($recentApplications as $app)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $app->application_number }}</span>
                                    <div class="font-bold text-slate-900 text-xs mt-0.5">{{ $app->applicant?->user?->name ?? 'Direct Applicant' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 text-xs">{{ $app->offering?->course?->name ?? 'General Degree' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $app->offering?->course?->code }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ str_replace('_', ' ', $app->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($app->isPaid())
                                        <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">PENDING</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('admissions.show', $app) }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">APP-2026-0891</span>
                                    <div class="font-bold text-slate-900 text-xs mt-0.5">Wanjiku Mary Njeri</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 text-xs">BSc. Computer Science</div>
                                    <div class="text-[11px] text-slate-400 font-mono">CS-101</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">VERIFIED</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('admissions.index') }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Review</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right 1 Col: Programme Quota & Intake Setups Overview --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Programme Capacity Tracker</h2>
                    <a href="{{ route('admissions.setups.index') }}" class="text-xs font-bold text-[#E67E22] hover:underline">Manage Quotas</a>
                </div>
                <div class="space-y-3.5">
                    @forelse($offerings as $offering)
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-slate-800 mb-1">
                                <span class="truncate">{{ $offering->name }}</span>
                                <span class="text-slate-500 font-mono">{{ $offering->capacity ?? 0 }} slots</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-[#0A3E50] h-2 rounded-full" style="width: {{ $offering->capacity > 0 ? min(100, round(($offering->applications_count / $offering->capacity) * 100)) : 0 }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-slate-800 mb-1">
                                <span>BSc. Computer Science</span>
                                <span class="text-slate-500 font-mono">60 slots (45 filled)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-[#0A3E50] h-2 rounded-full" style="width: 75%;"></div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 bg-slate-50 -mx-5 -mb-5 p-4 rounded-b-xl">
                <div class="text-xs font-bold text-slate-800 mb-2">Quick Navigation Shortcuts</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admissions.workspace.work-queues') }}" class="p-2 bg-white rounded border border-slate-200 text-center font-bold text-[11px] text-[#0A3E50] hover:bg-slate-100 transition-colors">
                        Work Queues
                    </a>
                    <a href="{{ route('admissions.workspace.document-verification') }}" class="p-2 bg-white rounded border border-slate-200 text-center font-bold text-[11px] text-[#0A3E50] hover:bg-slate-100 transition-colors">
                        Doc Verification
                    </a>
                    <a href="{{ route('admissions.workspace.approvals') }}" class="p-2 bg-white rounded border border-slate-200 text-center font-bold text-[11px] text-[#0A3E50] hover:bg-slate-100 transition-colors">
                        Board Approvals
                    </a>
                    <a href="{{ route('admissions.workspace.offers') }}" class="p-2 bg-white rounded border border-slate-200 text-center font-bold text-[11px] text-[#0A3E50] hover:bg-slate-100 transition-colors">
                        Issue Offers
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
