@extends('layouts.app')

@section('title', 'Board & Senate Approvals')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admissions Board &amp; Senate Final Approval Workspace</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Review Dean recommendations, record statutory board resolutions, authorize unconditional and conditional admissions, and approve rejections</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.approvals.authorize') }}"
                  onsubmit="return confirm('Authorise every file with a complete approval ladder?');">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs">Batch Authorize Offers</button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Awaiting Board Signoff</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['awaitingSignoff'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dean recommendations ready.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Session Queue</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Approved This Session</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['approvedThisSession'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Authorized for offer letters.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Ready to Dispatch</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Conditional Admissions</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['conditionalAdmissions'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Subject to final certificates.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Gated Enrolment</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Rejected Verdicts</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['rejectedVerdicts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Inadequate prerequisite criteria.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Formal Rejections</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $approvalsList,
        'noun' => 'files on the approval ladder',
        'search' => 'Search applicant, app no...',
        'selects' => [
            ['name' => 'stage', 'label' => 'All Ladder Stages', 'options' => ['dean' => 'Awaiting Dean', 'board' => 'Awaiting Senate Board']],
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
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Dean Recommendation</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Admissions Board Resolution</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Intake Session</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($approvalsList as $app)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $app['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $app['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $app['programme'] }}</td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">{{ $app['dean_recommendation'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-indigo-900">{{ $app['board_resolution'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $app['intake_name'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($app['status'], 'Approved'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $app['status'] }}</span>
                                @elseif(str_contains($app['status'], 'Conditional'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-purple-100 text-purple-800">{{ $app['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $app['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="post" action="{{ route('admissions.approvals.sign-off', $app['application_id']) }}" class="inline-flex gap-1">
                                    @csrf
                                    <button type="submit" name="verdict" value="APPROVED" class="px-3 py-1 rounded border border-emerald-500 text-emerald-700 hover:bg-emerald-50 font-semibold text-xs transition-colors">Signoff</button>
                                    <button type="submit" name="verdict" value="REJECTED"
                                            onclick="return confirm('Refuse this file at the current rung?');"
                                            class="px-3 py-1 rounded border border-red-400 text-red-600 hover:bg-red-50 font-semibold text-xs transition-colors">Refuse</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 7,
                            'message' => 'No file is waiting on an approval signature.',
                            'hint' => 'Files reach the ladder once they are advanced from the shortlist.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $approvalsList])
    </div>
</div>
@endsection
