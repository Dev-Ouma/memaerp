@extends('layouts.app')

@section('title', 'Admissions & Registry Reports')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admissions, Registration Status & Gender Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Generate Application Status Reports, Programme-Wise Applicants lists, Nominal Rolls, and KUCCPS quota lists</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Download Admissions Book</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Target Programme</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Total Applicants</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Shortlisted Count</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Registered Count</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Gender-Wise Allocation</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">KUCCPS Placed Count</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($applications as $app)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-900 text-xs">{{ $app['programme'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ number_format($app['applicants']) }}</td>
                        <td class="py-3.5 px-4 font-mono text-purple-900 font-semibold">{{ $app['shortlisted'] }}</td>
                        <td class="py-3.5 px-4 font-mono text-emerald-800 font-bold">{{ $app['registered'] }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700">{{ $app['gender_split'] }}</td>
                        <td class="py-3.5 px-4 font-mono text-[#0A3E50] font-bold">{{ $app['kuccps_placed'] }} placed</td>
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
