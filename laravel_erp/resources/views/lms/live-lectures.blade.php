@extends('layouts.app')

@section('title', 'Live Virtual Lectures & Timetable')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Live Virtual Lectures & Web Conference Timetable</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Coordinate live interactive lectures via BigBlueButton and Zoom Enterprise, capture automated student attendance, and manage recording archives</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Schedule Live Lecture
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Live Sessions This Week</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['liveSessionsThisWeek'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Scheduled live webclasses.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Virtual Timetable</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Live Attendance Rate</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['avgAttendanceRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Real-time telemetry verified.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">High Participation</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Recordings Archived</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['recordedHoursArchived'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Available for student replay.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">On-Demand Replay</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Conference Bridges</div>
            <div class="text-sm font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['activeVideoPlatform'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dual-redundancy server cluster.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Zero-Downtime</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Course Code & Session Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Lecturer / Instructor</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Scheduled Time & Room</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Attendance & Cloud Recording</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Session Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($sessions as $sess)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $sess['course_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $sess['session_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">{{ $sess['instructor'] }}</td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-semibold text-purple-900">{{ $sess['scheduled_time'] }}</div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">{{ $sess['platform'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-medium text-emerald-800">{{ $sess['attendance_mode'] }}</div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">{{ $sess['recording_status'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($sess['session_status'], 'Live Now'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Live Now
                                    </span>
                                @elseif(str_contains($sess['session_status'], 'Upcoming'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $sess['session_status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $sess['session_status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(str_contains($sess['session_status'], 'Live Now'))
                                    <button type="button" class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700 font-bold text-xs transition-colors shadow-xs">Join Room</button>
                                @else
                                    <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Details</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
