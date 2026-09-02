@extends('layouts.app')

@section('title', 'Waitlist Workspace')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Waitlist Queue &amp; Automatic Promotion Management</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Rank standby candidates by cluster points, monitor programme slot vacancies from declined offers, and execute auto-promotions</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.waitlists.auto-promote') }}"
                  onsubmit="return confirm('Promote the top-ranked candidate of every programme with a free place?');">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-2xs">Auto-Promote Top Rank</button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Waitlisted</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['totalWaitlisted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Met minimum requirements.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Standby Pool</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Vacant Slots Reopened</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['availableVacancies'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">From declined/expired offers.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Ready to Fill</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Promoted This Week</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['promotedThisWeek'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Moved from waitlist to offer.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">High Conversion</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Avg Wait Duration</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['avgWaitDays'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Before promotion or resolution.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Within Target</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $waitlists,
        'noun' => 'waitlisted candidates',
        'search' => 'Search applicant, app no...',
        'selects' => [
            ['name' => 'offering', 'label' => 'All Programmes', 'options' => $offerings],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">App Number &amp; Applicant</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Applied Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cluster Score</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Waitlist Priority Rank</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Waitlist Reason &amp; Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Date Queued</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($waitlists as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $item['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $item['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $item['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-purple-900">{{ $item['cluster_score'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">{{ $item['waitlist_rank'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700">{{ $item['reason'] }}</div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $item['date_waitlisted'] }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="post" action="{{ route('admissions.waitlists.promote', $item['application_id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded border border-emerald-500 text-emerald-700 hover:bg-emerald-50 font-semibold text-xs transition-colors">Promote</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 7,
                            'message' => 'The waitlist is empty.',
                            'hint' => 'Candidates appear here when they are waitlisted against a full programme.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $waitlists])
    </div>
</div>
@endsection
