@extends('layouts.app')

@section('title', 'Shortlist Workspace')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Candidate Shortlisting &amp; Quota Selection Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Rank candidates by cluster points, calculate competitive cut-off thresholds, and advance batches to Board approval</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.shortlists.submit') }}"
                  onsubmit="return confirm('Submit every shortlisted candidate to the board?');">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-2xs">Batch Submit to Board</button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Shortlisted</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['totalShortlisted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Met or exceeded cut-off score.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Across All Programmes</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Target Quota Capacity</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['targetQuota'] }} Slots</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Authorized intake ceiling.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Approved by Senate</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Quota Fill Rate</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['quotaFillRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Shortlisted vs capacity.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Near Full Capacity</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Mean Cut-Off Point</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['cutOffMean'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Weighted aggregate mean.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Competitive Intake</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $shortlists,
        'noun' => 'shortlisted candidates',
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
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cluster Points</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">KCSE Mean Grade</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Merit Rank</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Selection Quota Type</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-24" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($shortlists as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $item['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $item['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $item['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-900">{{ $item['cluster_points'] }}</td>
                            <td class="py-3.5 px-4 font-bold text-purple-900">{{ $item['mean_grade'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">{{ $item['rank'] }}</td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">{{ $item['selection_quota'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="post" action="{{ route('admissions.shortlists.advance', $item['application_id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Advance</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 8,
                            'message' => 'No candidate has been shortlisted yet.',
                            'hint' => 'Shortlisting happens after review scoring is complete.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $shortlists])
    </div>
</div>
@endsection
