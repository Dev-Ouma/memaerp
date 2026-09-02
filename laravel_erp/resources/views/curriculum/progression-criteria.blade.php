@extends('layouts.app')

@section('title', 'Progression Criteria')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Progression & Retention Criteria</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure semester advancement rules, credit pass thresholds, supplementary exam limits, and academic discontinuation policies</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add Progression Rule
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Rulesets</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeRulesets'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">UG, PG Master, Doctoral.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Senate Enforced</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Standard Pass Mark</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['passGpaThreshold'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Minimum unit passing bar.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Quality Standard</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Supplementary Allowance</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['maxSuppUnits'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Special/re-sit examination.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Re-Sit Gating</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Discontinuation Rule</div>
            <div class="text-xs font-bold text-red-700 mt-2 mb-1.5 leading-tight">{{ $stats['discontinuationThreshold'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Automatic academic warning.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Strict Retention</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Rule Code & Level</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Pass Mark / Bar</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Advancement Minimum</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Supplementary / Repeat Policy</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Discontinuation Grounds</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($rules as $r)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $r['rule_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $r['programme_level'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-800 text-xs">{{ $r['pass_mark'] }}</td>
                            <td class="py-3.5 px-4 text-slate-700 text-xs">{{ $r['min_credits_to_advance'] }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div><strong class="text-amber-800">Supp:</strong> {{ $r['supplementary_allowance'] }}</div>
                                <div class="mt-0.5"><strong class="text-slate-700">Repeat:</strong> {{ $r['repeat_conditions'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-red-700 text-xs">{{ $r['discontinuation'] }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
