@extends('layouts.app')

@section('title', 'Work Study Period Setup')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Work Study Sessions & Budget Allocation</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure academic semester work study intakes, institutional financial aid budget ceilings, hourly wage rates, and weekly hour caps</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Create Work Study Session
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Session</div>
            <div class="text-lg font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeSession'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current operational cycle.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Intake Open</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Allocated Budget</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['allocatedBudget'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dean of Students vote-head.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Institutional Aid</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Hourly Wage Rate</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['hourlyRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Standardized student wage.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Statutory Rate</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Weekly Workload Cap</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['maxHoursPerWeek'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Studies balance compliance.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Academic Protection</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Academic Year & Trimester</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Application Window</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Budget Allocation & Rate</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Max Hours / Target Beneficiaries</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($periods as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $p['academic_year'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $p['trimester'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 text-xs">
                                <div><strong class="text-slate-800">Opens:</strong> {{ $p['application_start'] }}</div>
                                <div class="text-slate-500 mt-0.5"><strong class="text-red-700">Closes:</strong> {{ $p['application_deadline'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                <div><strong class="text-emerald-800">{{ $p['total_budget'] }}</strong> (Committed: {{ $p['committed_budget'] }})</div>
                                <div class="text-blue-900 font-semibold mt-0.5">{{ $p['hourly_rate'] }} / hour</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div><strong>{{ $p['max_weekly_hours'] }} hrs/week</strong> max cap</div>
                                <div class="text-slate-500 mt-0.5">{{ $p['target_beneficiaries'] }} Student Slots</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($p['status'], 'Active'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Active / Open</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $p['status'] }}</span>
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
