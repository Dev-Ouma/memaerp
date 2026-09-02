@extends('layouts.app')

@section('title', 'Admissions Work Queues')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admissions Processing Work Queues &amp; SLA Management</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Prioritized triage pipelines, officer task allocation, turnaround time monitoring, and bottleneck escalation</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.work-queues.auto-assign') }}">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                    Auto-Assign Batches
                </button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Urgent SLA Queue</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['urgentSLA'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">&lt; 24h before deadline breach.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Critical Priority</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Triage</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingTriage'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">New submissions unassigned.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Awaiting Routing</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">In Active Review</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['inReviewQueue'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Distributed among faculty officers.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">{{ $stats['reviewerCount'] }} Reviewers</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Avg Turnaround Time</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['avgResolutionTime'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Submission to offer dispatch.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Target: 3.0 Days</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $queues,
        'noun' => 'active priority queue items',
        'search' => 'Search applicant, app no...',
        'selects' => [
            ['name' => 'queue', 'label' => 'All Queues', 'options' => $stages],
            ['name' => 'priority', 'label' => 'All Priorities', 'options' => ['Urgent' => 'Urgent', 'High' => 'High', 'Normal' => 'Normal']],
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
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Queue Stage</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Assigned Desk / Reviewer</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">SLA Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($queues as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $item['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $item['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">{{ $item['programme'] }}</div>
                                @if($item['priority'] === 'Urgent')
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-100 text-red-800 uppercase">Urgent</span>
                                @elseif($item['priority'] === 'High')
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">High Priority</span>
                                @else
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">Normal</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-indigo-900">{{ $item['queue_type'] }}</td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">{{ $item['assigned_to'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($item['sla_status'], 'Overdue'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800 border border-red-200">{{ $item['sla_status'] }}</span>
                                @elseif(str_contains($item['sla_status'], 'Expires in') || str_contains($item['sla_status'], 'today'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800 border border-amber-200">{{ $item['sla_status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">{{ $item['sla_status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.show', $item['application_id']) }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Process
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 6,
                            'message' => 'No applications are waiting in a queue.',
                            'hint' => 'Submitted applications appear here once triage desks are opened.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $queues])
    </div>
</div>
@endsection
