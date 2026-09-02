@extends('layouts.app')

@section('title', 'Instructor Mapping')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Instructor & Course Unit Allocation Mapping</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Map academic staff and faculty instructors to active course unit cohorts and LMS virtual lecture rooms</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Map Instructor
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Unit Allocations</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalAllocations'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Semester teaching units.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Active Semester</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Teaching Faculty</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['assignedLecturers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Professors & Lecturers mapped.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Load Assigned</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Unallocated Units</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['unallocatedCourseUnits'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting HOD assignment.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Action Required</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Avg Teaching Load</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['avgTeachingLoad'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">CUE workload ceiling.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">CUE Compliant</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Course Unit</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Primary Instructor</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Secondary / Co-Lecturer</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Department & Cohort</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($mappings as $m)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $m['unit_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $m['unit_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 text-xs">{{ $m['primary_instructor'] }}</td>
                            <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $m['secondary_instructor'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                <div>{{ $m['department'] }}</div>
                                <div class="text-slate-500 font-semibold">{{ $m['enrolled_students'] }} Students Enrolled</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $m['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Reassign</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
