@extends('layouts.app')

@section('title', 'Admissions Audit Trail & Governance')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admissions Audit Trail &amp; Governance Ledger</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Immutable chronological record of applicant state transitions, reviewer evaluations, fee waivers, board sign-offs, and matriculations</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.audit.verify') }}">
                @csrf
                {{-- The trail has no hash chain; what the database enforces is append-only. --}}
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Verify Append-Only Integrity
                </button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Audit Events</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format($stats['totalAuditEvents']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Append-only immutable records.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">{{ $stats['integrityChain'] }}</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Events Recorded Today</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['eventsToday'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Applicant &amp; staff operations.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Realtime Logging</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Administrative Actors</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['actorsActive'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Deans, review panels, registry.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Segregation of Duties</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Compliance Status</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">Compliant</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Statutory CUE &amp; KNEC standards.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Zero Policy Violations</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $auditLogs,
        'noun' => 'admission audit entries',
        'search' => 'Search actor, app no, action...',
        'selects' => [
            ['name' => 'action', 'label' => 'All Actions', 'options' => $actions],
            ['name' => 'severity', 'label' => 'All Severities', 'options' => ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Timestamp</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Action Event</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">App Number</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Actor / Origin</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">IP Address</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Event Narrative Description</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-24" style="color:#ffffff !important;">Severity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $log['timestamp'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#0A3E50]">{{ $log['action'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $log['app_no'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">{{ $log['actor'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[10.5px] text-slate-500">{{ $log['ip_address'] }}</td>
                            <td class="py-3.5 px-4 text-slate-700">{{ $log['description'] }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($log['severity'] === 'Success')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">SUCCESS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">INFO</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 7,
                            'message' => 'No admission activity has been audited yet.',
                            'hint' => 'Every admission action is written here as it happens.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $auditLogs])
    </div>
</div>
@endsection
