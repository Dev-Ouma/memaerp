@extends('layouts.app')

@section('title', 'E-Assessment & Online Quizzes')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">E-Assessment, Timed Online Quizzes & Proctoring</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure timed online continuous assessments, randomized question banks, browser lock proctoring, and instant automated grading</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Create Online Quiz
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Quizzes</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeTimedQuizzes'] }} Quizzes</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Continuous assessment tests.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">CAT Quizzes</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Question Pool</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['randomizedQuestionBank']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Randomized item bank.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Randomized</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">AI Proctoring</div>
            <div class="text-sm font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['aiProctoringActive'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Automated integrity checks.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Proctored</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Auto-Grading Feedback</div>
            <div class="text-xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['instantGradeFeedback'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Instant score compilation.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Real-Time Marks</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Quiz Title & Course</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Duration & Weight</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Completed Attempts & Average</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Proctoring Mode</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($quizzes as $q)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $q['quiz_title'] }}</div>
                                <div class="text-[11px] text-purple-900 font-mono mt-0.5">{{ $q['course_code'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-semibold text-slate-800">{{ $q['duration_minutes'] }}</div>
                                <div class="font-bold text-purple-900 text-[10.5px] mt-0.5">{{ $q['weight'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-mono">
                                <div><strong class="text-emerald-800">{{ $q['completed_attempts'] }}</strong></div>
                                <div class="text-slate-500 font-medium text-[10.5px] mt-0.5">Avg: {{ $q['avg_score'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-[#0A3E50] text-xs">{{ $q['proctoring_mode'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($q['status'], 'Active'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $q['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $q['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Results</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
