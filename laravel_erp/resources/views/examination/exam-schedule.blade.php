@extends('layouts.app')

@section('title', 'Exam Schedule')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Exam Schedule & Clashes Resolution Engine</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify class-clash free examination timetables, invigilator duty dockets, slot allocations, and hall assignments</p>
        </div>
        @can('admin')<button type="button" data-modal-open="exam-schedule-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Schedule Paper</button>@endcan
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Paper Code & Course</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Daily Slot</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Exam Venue</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Candidates</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Chief Invigilator</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($schedules as $s)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $s['paper_code'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $s['course_title'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-700 font-semibold">{{ $s['date'] }}</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold">{{ $s['slot'] }}</td>
                        <td class="py-3 px-4 text-slate-700 font-semibold">{{ $s['venue'] }}</td>
                        <td class="py-3 px-4 font-mono text-emerald-800 font-bold">{{ $s['candidates'] }} Candidates</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $s['chief_invigilator'] }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $s['status'] }}</span>
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
@can('admin')<div class="modal" id="exam-schedule-modal"><div class="modal-card"><div class="panel-head"><h2>Schedule Examination Paper</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form class="panel-body" method="post" action="{{ route('examination.exam-schedule.store') }}">@csrf<div class="form-grid"><div class="field"><label>Session</label><select name="exam_session_id" required>@foreach($examSessions as $session)<option value="{{ $session->id }}">{{ $session->session_title }}</option>@endforeach</select></div><div class="field"><label>Subject</label><select name="subject_id" required>@foreach($subjectsForSchedule as $subject)<option value="{{ $subject->id }}">{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select></div><div class="field"><label>Center</label><select name="exam_center_id" required>@foreach($centersForSchedule as $center)<option value="{{ $center->id }}">{{ $center->name }}</option>@endforeach</select></div><div class="field"><label>Chief invigilator</label><select name="chief_invigilator_id"><option value="">Unassigned</option>@foreach($invigilators as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div><div class="field"><label>Date</label><input type="date" name="exam_date" required></div><div class="field"><label>Slot</label><input name="slot" placeholder="08:30 - 11:30" required></div><div class="field"><label>Candidates</label><input type="number" name="candidate_count" min="0" required></div><div class="field"><label>Status</label><select name="status"><option>SCHEDULED</option><option>PUBLISHED</option></select></div></div><button class="btn" style="margin-top:16px">Save schedule</button></form></div></div>@endcan
@endsection
