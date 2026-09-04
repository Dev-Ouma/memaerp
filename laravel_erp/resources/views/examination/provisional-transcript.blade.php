@extends('layouts.app')

@section('title', 'Provisional Transcript')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Provisional Transcript</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Generate and audit student trimester-by-trimester results printout, raw continuous assessment scores, and temporary GPA rankings</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Export PDF
            </button>
            <button type="button" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs">
                Print Transcript
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Transcripts Printed</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['printedToday']) }} Today</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current trimester requests.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Daily Print Vol</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Standing</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['averageGpa'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Selected student CGPA.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Good Standing</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Audit Verification</div>
            <div class="text-sm font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['provisionalAccuracy'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Zero ledger errors.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">System Audited</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Requested</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['transcriptsRequested']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Total provisional requests.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Requests Logged</span></div>
        </div>
    </div>

    {{-- Transcript Block --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs p-6 mb-6">
        <div class="border-b border-slate-200 pb-5 mb-5 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $studentInfo['name'] }}</h2>
                <p class="text-xs font-mono text-purple-900 mt-0.5 font-bold">{{ $studentInfo['reg_no'] }}</p>
                <p class="text-xs text-slate-600 mt-1">{{ $studentInfo['programme'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $studentInfo['school'] }}</p>
            </div>
            <div class="text-center sm:text-right">
                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-[#0A3E50] text-white">{{ $studentInfo['cohort'] }}</span>
                <p class="text-xs text-slate-600 mt-2"><strong>Academic Stage:</strong> {{ $studentInfo['academic_year'] }}</p>
                <p class="text-[11px] text-emerald-700 font-bold mt-1">{{ $studentInfo['verdict'] }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Course Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Credit Hours</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Raw Marks</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Grade Letter</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Grade Points</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                    @foreach($transcriptLines as $line)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-sans text-xs">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $line['unit_code'] }}</span>
                                <div class="font-bold text-slate-900 mt-1">{{ $line['unit_title'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-800">{{ $line['credit_hours'] }} Hrs</td>
                            <td class="py-3 px-4 text-slate-800 font-bold">{{ $line['marks'] }}</td>
                            <td class="py-3 px-4 text-[#0A3E50] font-bold text-xs">{{ $line['grade'] }}</td>
                            <td class="py-3 px-4 text-purple-900 font-bold">{{ $line['gpa_points'] }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 font-sans">{{ $line['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
