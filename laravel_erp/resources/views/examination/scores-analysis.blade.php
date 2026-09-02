@extends('layouts.app')

@section('title', 'Class Scores Analysis')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Class Grades Statistical Performance Analysis</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Statistical reports tracking mean class scores, standard deviation, highest scores, and failure rate alerts</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Export Analytics</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Unit Code & Course Title</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Mean Score</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Median Score</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Standard Deviation</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Highest Score</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Failure Rate</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Performance Verdict</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($analyses as $a)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $a['unit_code'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $a['unit_title'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $a['mean_score'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $a['median_score'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $a['std_deviation'] }}</td>
                        <td class="py-3 px-4 font-mono text-emerald-800 font-bold">{{ $a['highest_score'] }}</td>
                        <td class="py-3 px-4 font-mono text-red-700 font-bold">{{ $a['failure_rate'] }}</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold">{{ $a['verdict'] }}</td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Bellcurve</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
