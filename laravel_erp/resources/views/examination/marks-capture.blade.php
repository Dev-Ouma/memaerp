@extends('layouts.app')

@section('title', 'Marks Capture')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Examiner Marks Capture & Entry Sheet</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Lecturers entry portal for continuous assessment (30%) and final examination (70%) grade sheets</p>
        </div>
        <button type="button" data-modal-open="marks-capture-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Capture Student Marks</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Unit Code & Course Title</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cohort</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Lecturer</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">CAT Entered (30%)</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Exam Entered (70%)</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Mean Score</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($captures as $c)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $c['unit_code'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $c['unit_title'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-purple-900 font-semibold">{{ $c['cohort'] }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $c['lecturer'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $c['cat_captured'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $c['exam_captured'] }}</td>
                        <td class="py-3 px-4 font-mono text-emerald-800 font-bold">{{ $c['average_score'] }}</td>
                        <td class="py-3 px-4">
                            @if(str_contains($c['status'], 'Completed'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $c['status'] }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $c['status'] }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Capture</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="marks-capture-modal"><div class="modal-card"><div class="panel-head"><h2>Capture Student Marks</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form class="panel-body" method="post" action="{{ route('examination.marks-capture.store') }}">@csrf<div class="form-grid"><div class="field"><label>Student</label><select name="student_id" required>@foreach($studentsForMarks as $student)<option value="{{ $student->id }}">{{ $student->admission_number }} — {{ $student->user?->name }}</option>@endforeach</select></div><div class="field"><label>Subject</label><select name="subject_id" required>@foreach($subjectsForMarks as $subject)<option value="{{ $subject->id }}">{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select></div><div class="field"><label>CAT score / 30</label><input type="number" name="test_score" min="0" max="30" step="0.01" required></div><div class="field"><label>Exam score / 70</label><input type="number" name="exam_score" min="0" max="70" step="0.01" required></div></div><button class="btn" style="margin-top:16px">Save marks</button></form></div></div>
@endsection
