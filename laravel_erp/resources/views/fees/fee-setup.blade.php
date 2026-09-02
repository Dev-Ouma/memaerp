@extends('layouts.app')

@section('title', 'Fee Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Programme Cohort Fee Structure Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Create cohort-specific and programme-specific tuition schedules, trimester administrative fees, and lab charges</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Configure New Structure
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Structures</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeStructures'] }} Schemes</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Configured programme structures.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Fee Structures</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Max Trimester Tuition</div>
            <div class="text-xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['highestTrimesterFee'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">PhD candidates rate.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Postgraduate Maximum</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Min Trimester Tuition</div>
            <div class="text-xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['lowestTrimesterFee'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Undergraduate base rate.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-[#0A3E50] bg-blue-50 border border-blue-200">Undergrad Minimum</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Tuition Rate</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['averageTuition'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Trimester median cost.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Median Cost</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Structure Code & Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Target Cohort Mapping</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Trimester Tuition Fee</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Trimester Administrative Fees</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Trimester Total Billing</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Last Config Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($structures as $str)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $str['structure_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $str['programme'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-purple-900 font-semibold">{{ $str['cohort'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $str['tuition_fee'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $str['admin_fee'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#0A3E50]">{{ $str['total_per_trimester'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $str['last_updated'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $str['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Configure</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
