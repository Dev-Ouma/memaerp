@extends('layouts.app')

@section('title', 'Cohort Creation')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Cohort Creation & Intake Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Create and manage institutional student intake cohorts, delivery modes (ODeL, Full-Time, Executive), capacity ceilings, and graduation timelines</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Create New Cohort
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Cohorts</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalActiveCohorts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active university cohorts.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Intake Groups</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">ODeL / Virtual Cohorts</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['odelVirtualCohorts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Open, Distance & E-Learning.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Virtual Track</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Regular & Executive</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['regularCampusCohorts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Hybrid and weekend cohorts.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Executive Cohorts</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Cohort Population</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['totalEnrolledInCohorts']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Registered student headcount.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Enrolled Students</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cohort Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Intake Session & Mode</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Enrolled / Target Capacity</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Expected Graduation</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($cohorts as $c)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $c['cohort_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $c['cohort_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $c['academic_year'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">{{ $c['intake_session'] }}</div>
                                <div class="text-[11px] text-purple-900 font-semibold mt-0.5">{{ $c['study_mode'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                <div><strong class="text-emerald-800">{{ number_format($c['enrolled']) }}</strong> of {{ number_format($c['capacity']) }}</div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                    <div class="bg-emerald-600 h-full rounded-full" style="width: {{ min(100, round(($c['enrolled'] / $c['capacity']) * 100)) }}%"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $c['graduation_expected'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($c['status'], 'Enrolling'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $c['status'] }}</span>
                                @elseif(str_contains($c['status'], 'Continuing'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $c['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $c['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Manage</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
