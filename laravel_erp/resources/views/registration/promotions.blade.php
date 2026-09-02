@extends('layouts.app')

@section('title', 'Promotions & Academic Progression')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Promotions & Academic Progression Board</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Evaluate trimester and annual student academic standing, Senate promotions, Dean's List honours, trailing credits, and repeat determinations</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Run Progression Board
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Promoted Scholars</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['promotedToNextYear']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Normal academic progression.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Progressed</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Dean's List Honours</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['deansListHonours']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">GPA >= 3.70 excellence.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">First Class Track</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Warning</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['academicWarning'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Trailing failed units.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Remedial Support</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Repeat Determinations</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['repeatYearOrders'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Failed prerequisite repeat.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Senate Repeat</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Reg No & Student Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Progression (From &rarr; To)</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cumulative GPA & Credits</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Senate Promotion Verdict</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($promotions as $pr)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $pr['reg_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $pr['student_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">{{ $pr['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <span class="text-slate-600">{{ $pr['from_stage'] }}</span>
                                <div class="text-emerald-700 font-bold mt-0.5">&rarr; {{ $pr['to_stage'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div>GPA: <strong class="text-purple-900">{{ $pr['cumulative_gpa'] }}</strong></div>
                                <div class="text-slate-500 font-mono text-[10.5px] mt-0.5">{{ $pr['credits_passed'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($pr['promotion_verdict'], 'Dean'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-purple-100 text-purple-800 mb-1">Dean's Honours</span>
                                @elseif(str_contains($pr['promotion_verdict'], 'Warning'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800 mb-1">Academic Warning</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 mb-1">Promoted</span>
                                @endif
                                <div class="text-[10.5px] text-slate-500">Senate: {{ $pr['senate_date'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Transcript</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
