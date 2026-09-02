@extends('layouts.app')

@section('title', 'Reviewer Assignments & Scoring Rubrics')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reviewer Workload Allocation &amp; Academic Scoring Rubrics</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Assign candidate dossiers to Deans &amp; Faculty Review Panels, score prerequisite credentials, and record recommendations</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.reviews.assign') }}">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs">Assign Reviewers</button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">In Faculty Review</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['inReview'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dossiers assigned to deans.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Active Pipeline</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Reviewers</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['activeReviewers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Across 6 academic schools.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Deans &amp; Chairs</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Score</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['avgScore'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Composite rubric index.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Min Pass: 65%</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Completed Today</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['completedToday'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Scored and ready for shortlist.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Stage Progress</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $reviewsList,
        'noun' => 'applications under academic review',
        'search' => 'Search applicant, app no...',
        'selects' => [
            ['name' => 'stage', 'label' => 'All Stages', 'options' => ['pending' => 'Scoring In Progress', 'complete' => 'Scoring Complete']],
            ['name' => 'recommendation', 'label' => 'All Recommendations', 'options' => ['Recommended' => 'Recommended', 'Conditional' => 'Conditional', 'Not Recommended' => 'Not Recommended']],
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
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Reviewer &amp; Department</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px] text-center" style="color:#ffffff !important;">Academic Merit (30)</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px] text-center" style="color:#ffffff !important;">Prereqs (30)</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px] text-center" style="color:#ffffff !important;">Interview/SOP (40)</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px] text-center" style="color:#ffffff !important;">Total Score</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Verdict / Recommendation</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-24" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reviewsList as $rev)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $rev['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $rev['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $rev['programme'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $rev['reviewer_name'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $rev['department'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $rev['academic_merit'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $rev['prereq_score'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $rev['sop_interview'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-sm text-[#0A3E50]">{{ $rev['total_score'] }}%</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $rev['recommendation'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.show', $rev['application_id']) }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Score
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 9,
                            'message' => 'No application is currently under departmental review.',
                            'hint' => 'Verified applications are routed here for scoring.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $reviewsList])
    </div>
</div>
@endsection
