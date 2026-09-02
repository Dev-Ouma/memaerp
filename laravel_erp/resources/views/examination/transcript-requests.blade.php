@extends('layouts.app')

@section('title', 'Transcript Requests')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Transcript Requests & Dispatch Registry</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Log and track student requests for provisional or official transcripts, payment references, and courier/email delivery status</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">New Request</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Request No</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Student Reg & Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Transcript Type Requested</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Reference</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Dispatch Method</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Request Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Request Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($requests as $r)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $r['request_no'] }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $r['reg_no'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $r['student_name'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-[#0A3E50]">{{ $r['transcript_type'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-850">{{ $r['payment_ref'] }}</td>
                        <td class="py-3 px-4 text-purple-900 font-semibold">{{ $r['dispatch_method'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600 font-semibold">{{ $r['request_date'] }}</td>
                        <td class="py-3 px-4">
                            @if(str_contains($r['status'], 'Processed'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $r['status'] }}</span>
                            @elseif(str_contains($r['status'], 'Awaiting'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $r['status'] }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $r['status'] }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Process</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
