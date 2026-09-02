@extends('layouts.app')

@section('title', 'Summary of Examination Results')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Summary of School & Faculty Examination Results</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">High-level statistical summaries by Faculty/School for Senate Board review and promotion approval</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Print Senate Report</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Faculty / School Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Candidates Enrolled</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Papers Offered</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Total Fail Units</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Percentage Pass</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">First Class Expected</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Senate Standing Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($summaries as $s)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $s['school_name'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ number_format($s['candidate_enrolled']) }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $s['papers_offered'] }} Papers</td>
                        <td class="py-3 px-4 font-mono text-red-700 font-bold">{{ $s['total_fails'] }} Fails</td>
                        <td class="py-3 px-4 font-mono text-emerald-800 font-bold">{{ $s['percentage_pass'] }}</td>
                        <td class="py-3 px-4 font-mono text-[#0A3E50] font-bold">{{ $s['first_class_expected'] }} Candidates</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $s['senate_standing'] }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Details</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
