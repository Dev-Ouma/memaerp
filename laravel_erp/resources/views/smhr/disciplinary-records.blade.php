@extends('layouts.app')

@section('title', 'Disciplinary & HR Governance - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Disciplinary &amp; Governance</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Disciplinary &amp; HR Governance Ledger</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Official commendations, formal cautions, disciplinary hearings, and grievance resolutions log</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="alert('Opening new HR Incident / Commendation Entry Form...')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">New Governance Entry</span>
            </button>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Total Incidents Logged</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ $governanceStats['totalIncidents'] }} Records</div>
            <p class="text-[11px] text-slate-500 mt-0.5">2025/2026 academic year</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Resolved &amp; Closed</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $governanceStats['resolved'] }} Resolved</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Formal resolutions filed</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Active Hearings</div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $governanceStats['activeHearings'] }} Active</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Staff committee review</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Official Commendations</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-1.5">{{ $governanceStats['officialCommendations'] }} Citations</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Excellence &amp; grants awarded</p>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-900">Official HR Governance &amp; Disciplinary Records</h2>
            <span class="text-xs text-slate-500 font-medium">Confidential Staff Registry</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Record ID</th>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Record Nature &amp; Category</th>
                        <th class="py-3 px-4">Date Logged</th>
                        <th class="py-3 px-4">Incident / Commendation Summary</th>
                        <th class="py-3 px-4">Official Action / Sanction</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($records as $rec)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $rec['id'] }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $rec['staff_name'] }}</div>
                                <div class="text-[10.5px] text-slate-500">{{ $rec['dept'] }} &middot; {{ $rec['staff_id'] }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold @if(str_contains($rec['category'], 'COMMENDATION') || str_contains($rec['category'], 'OUTSTANDING') || str_contains($rec['category'], 'CITATION')) text-[#1E8449] @else text-amber-700 @endif">
                                    {{ $rec['type'] }}
                                </span>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $rec['category'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $rec['date'] }}</td>
                            <td class="py-3 px-4 text-slate-700 max-w-xs">{{ $rec['description'] }}</td>
                            <td class="py-3 px-4 text-slate-800 font-semibold max-w-xs">{{ $rec['action_taken'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if(str_contains($rec['status'], 'RESOLVED')) bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $rec['status'] }}
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
