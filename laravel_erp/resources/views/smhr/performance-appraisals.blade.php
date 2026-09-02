@extends('layouts.app')

@section('title', 'Performance Appraisals & KPIs - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Performance Appraisals</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Staff Performance Appraisals &amp; KPIs</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Annual evaluation cycle, research publications index, student teaching evaluation, and dean score sign-offs</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="alert('Initiating new 2026/2027 Staff Appraisal Review Cycle...')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="award" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">New Appraisal Cycle</span>
            </button>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Evaluations Completed</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $appraisalStats['completed'] }} Staff</div>
            <p class="text-[11px] text-slate-500 mt-0.5">82.4% compliance rate</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Pending Review</div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $appraisalStats['pendingReview'] }} Staff</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Dean endorsement stage</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Average Score</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ $appraisalStats['averageScore'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">University-wide average</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Top Performers (Grade A)</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-1.5">{{ $appraisalStats['topPerformers'] }} Staff</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Eligible for promotion</p>
        </div>
    </div>

    {{-- Appraisals Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-900">Annual Academic &amp; Staff Performance Scores</h2>
            <span class="text-xs text-slate-500">2025/2026 Evaluation Cycle</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4 text-center">Teaching Eval (40%)</th>
                        <th class="py-3 px-4 text-center">Research Output (40%)</th>
                        <th class="py-3 px-4 text-center">Service (20%)</th>
                        <th class="py-3 px-4 text-center font-bold">Overall Score</th>
                        <th class="py-3 px-4 text-center">Performance Grade</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($appraisals as $apr)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $apr['name'] }}</div>
                                <div class="font-mono text-[11px] text-[#0A3E50]">{{ $apr['staff_id'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $apr['dept'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-800">{{ $apr['teaching_eval'] }}%</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-purple-800">{{ $apr['research_publications'] }}%</td>
                            <td class="py-3 px-4 text-center font-mono text-slate-700">{{ $apr['community_service'] }}%</td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-[#1E8449] text-sm">{{ $apr['overall_score'] }}%</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $apr['grade'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($apr['status'] === 'APPROVED') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $apr['status'] }}
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
