@extends('layouts.app')

@section('title', 'Academic & Progression Reports')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Unit Registration & progression Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify class registration counts, student progression summaries, exemption approvals, and re-attempt files</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Download Progression Sheets</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Target Programme</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Year 1 Progression</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Year 2 Progression</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Year 3 Progression</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Year 4 Expected Grad</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Exemptions Mapped</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Repeats / Retakes</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                @foreach($progressions as $p)
                    <tr class="hover:bg-slate-50/70 transition-colors font-sans">
                        <td class="py-3.5 px-4 font-sans font-bold text-slate-900 text-xs">{{ $p['programme'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">{{ $p['year_1'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">{{ $p['year_2'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">{{ $p['year_3'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">{{ $p['year_4'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-[#0A3E50]">{{ $p['exemptions'] }} student maps</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-red-700">{{ $p['repeats'] }} orders</td>
                        <td class="py-3.5 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Print</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
