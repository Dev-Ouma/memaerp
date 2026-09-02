@extends('layouts.app')

@section('title', 'Teaching Workload Allocation - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Workload Allocation</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Teaching Workload &amp; Faculty Allocation</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Senate teaching hours monitoring, assigned course units, postgraduate supervision units, and faculty balance</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="alert('Exporting Workload Master Schedule (CSV)...')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="download" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Export Workload Register</span>
            </button>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Average Workload</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ $workloadStats['averageHours'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Contact &amp; supervision</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Max Senate Limit</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-1.5">{{ $workloadStats['maxAllowedHours'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Per lecturer per week</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Allocation Index</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $workloadStats['fullyAllocatedFaculty'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Fully assigned units</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Overload Flags</div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $workloadStats['overloadCount'] }} Faculty</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Eligible for adjunct support</p>
        </div>
    </div>

    {{-- Workload Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-900">Faculty Course Unit Load &amp; Supervision Matrix</h2>
            <span class="text-xs text-slate-500">2026/2027 Academic Session</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Faculty Member</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Allocated Course Units</th>
                        <th class="py-3 px-4 text-center">Teaching Hrs</th>
                        <th class="py-3 px-4 text-center">Supervision</th>
                        <th class="py-3 px-4 text-center">Admin / HOD</th>
                        <th class="py-3 px-4 text-center font-bold">Total / Week</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($allocations as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $item['name'] }}</div>
                                <div class="font-mono text-[11px] text-[#0A3E50]">{{ $item['staff_id'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $item['dept'] }}</td>
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    @foreach($item['units'] as $unit)
                                        <div class="inline-block px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-[11px] font-semibold text-slate-800 mr-1 mb-1">
                                            {{ $unit }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ $item['teaching_hours'] }} Hrs</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-purple-800">{{ $item['supervision_hours'] }} Hrs</td>
                            <td class="py-3 px-4 text-center font-mono text-slate-600">{{ $item['admin_hours'] }} Hrs</td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-[#0A3E50] text-sm">{{ $item['total_hours'] }} Hrs/Wk</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($item['status'] === 'OPTIMAL') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
