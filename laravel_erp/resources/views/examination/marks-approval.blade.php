@extends('layouts.app')

@section('title', 'Exam Marks Approval')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Board & Senate Marks Approval Panel</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify department moderation sign-offs, faculty dean approvals, and senate grade ratifications prior to portal publish</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Ratify Selected</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Approval Ref & Unit</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Department Moderator</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Dean Board Sign-off</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Senate Ratification</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Security Lock</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($approvals as $a)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $a['approval_ref'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $a['unit_code'] }}: {{ $a['unit_title'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $a['department_moderator'] }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $a['dean_signoff'] }}</td>
                        <td class="py-3 px-4 font-semibold text-[#0A3E50]">{{ $a['senate_ratification'] }}</td>
                        <td class="py-3 px-4 font-mono text-purple-900 font-bold text-xs">{{ $a['security_lock'] }}</td>
                        <td class="py-3 px-4">
                            @if(str_contains($a['status'], 'Ratified'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $a['status'] }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $a['status'] }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Approve</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
