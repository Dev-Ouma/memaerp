@extends('layouts.app')

@section('title', 'ERP-Moodle Course Unit Sync')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">ERP & Moodle LMS Virtual Campus Synchronization Engine</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Synchronize ERP registered course units, student group cohorts, lecturer roles, and gradebook categories to Moodle LMS in real-time</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Trigger Full LMS Sync
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Synced Course Units</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['syncedCourseUnits'] }} Units</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active virtual classrooms.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Moodle Shells</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Synced Enrollments</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['syncedStudentEnrollments']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Student course access.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">100% In Sync</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Sync API Latency</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['syncLatency'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Real-time REST webhook.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Real-Time</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Moodle API Status</div>
            <div class="text-sm font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['moodleApiStatus'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Institutional core bridge.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Connected</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Unit Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Moodle LMS Course Shell ID</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Enrolled Scholars & Lecturer</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Last Timestamp</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Sync Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($syncLogs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $log['unit_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $log['unit_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-purple-900 text-xs">{{ $log['moodle_course_id'] }}</td>
                            <td class="py-3.5 px-4 text-xs">
                                <div><strong class="text-emerald-800">{{ $log['enrolled_students'] }} Scholars</strong></div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">{{ $log['instructor_assigned'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-xs">{{ $log['last_synced_at'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $log['sync_status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Sync Now</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
