@extends('layouts.app')

@section('title', 'Exam Session Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Exam Trimester Sessions Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Create and manage active examination periods, start/end calendars, slot divisions, and academic moderation deadlines</p>
        </div>
        @can('admin')<button type="button" data-modal-open="exam-session-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Create Exam Session</button>@endcan
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Session Code & Title</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Start Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">End Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Daily Slots</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Candidates</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Moderation Deadline</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($sessions as $s)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $s['session_code'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $s['session_title'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-700 font-semibold">{{ $s['start_date'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700 font-semibold">{{ $s['end_date'] }}</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold">{{ $s['daily_slots'] }} daily slots</td>
                        <td class="py-3 px-4 font-mono text-emerald-800 font-bold">{{ number_format($s['candidate_count']) }}</td>
                        <td class="py-3 px-4 font-mono text-red-700 font-semibold">{{ $s['moderation_deadline'] }}</td>
                        <td class="py-3 px-4">
                            @if(str_contains($s['status'], 'Active'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $s['status'] }}</span>
                            @elseif(str_contains($s['status'], 'Upcoming'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $s['status'] }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700">{{ $s['status'] }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@can('admin')<div class="modal" id="exam-session-modal"><div class="modal-card"><div class="panel-head"><h2>Create Exam Session</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form class="panel-body" method="post" action="{{ route('examination.exam-session.store') }}">@csrf<div class="form-grid"><div class="field"><label>Code</label><input name="session_code" required></div><div class="field"><label>Title</label><input name="session_title" required></div><div class="field"><label>Start date</label><input type="date" name="start_date" required></div><div class="field"><label>End date</label><input type="date" name="end_date" required></div><div class="field"><label>Daily slots</label><input type="number" name="daily_slots" min="1" max="6" required></div><div class="field"><label>Moderation deadline</label><input type="date" name="moderation_deadline" required></div><div class="field"><label>Status</label><select name="status"><option>DRAFT</option><option>SCHEDULED</option><option>ACTIVE</option><option>CLOSED</option></select></div></div><button class="btn" style="margin-top:16px">Save session</button></form></div></div>@endcan
@endsection
