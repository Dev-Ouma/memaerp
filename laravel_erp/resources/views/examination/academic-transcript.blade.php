@extends('layouts.app')

@section('title', 'Academic Transcript')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Official Academic Transcript Registry</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Issue and verify official, final graduation academic transcripts with Senate seals, security watermarks, and CGPA classification honors</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Verify Senate Seal
            </button>
            <button type="button" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs">
                Issue Official Transcript
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Transcripts Issued</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['officialTranscriptsIssued']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Graduated alumni credentials.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Alumni Ledger</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Security Features</div>
            <div class="text-md font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['securityFeatures'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Anti-forgery protections.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Secured</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Senate Ratification</div>
            <div class="text-xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['academicStanding'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Ratiified graduation lists.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Ratified</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Sealed Diplomas</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['sealedDiplomas']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Certificates stamped & sealed.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Sealed</span></div>
        </div>
    </div>

    {{-- Official Academic Transcript Block --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs p-6 mb-6">
        <div class="border-b border-slate-200 pb-5 mb-5 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $studentInfo['name'] }}</h2>
                <p class="text-xs font-mono text-purple-900 mt-0.5 font-bold">{{ $studentInfo['reg_no'] }}</p>
                <p class="text-xs text-slate-600 mt-1">{{ $studentInfo['programme'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $studentInfo['school'] }} | <strong>Specialization:</strong> {{ $studentInfo['specialization'] }}</p>
            </div>
            <div class="text-center sm:text-right">
                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-[#0A3E50] text-white">OFFICIAL ACADEMIC TRANSCRIPT</span>
                <p class="text-xs text-slate-600 mt-2"><strong>Award:</strong> {{ $studentInfo['award'] }}</p>
                <p class="text-[11px] text-emerald-700 font-bold mt-1">Senate Date: {{ $studentInfo['senate_approval_date'] }}</p>
            </div>
        </div>

        @foreach($transcriptSemesters as $sem)
            <div class="mb-5">
                <h3 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-1 mb-2">{{ $sem['semester_name'] }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                                <th class="py-2 px-4 uppercase text-[10px] w-24">Unit Code</th>
                                <th class="py-2 px-4 uppercase text-[10px]">Unit Title</th>
                                <th class="py-2 px-4 uppercase text-[10px] w-20">Grade</th>
                                <th class="py-2 px-4 uppercase text-[10px] w-24">Grade Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                            @foreach($sem['units'] as $unit)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2 px-4">{{ $unit['code'] }}</td>
                                    <td class="py-2 px-4 font-sans font-semibold text-slate-800">{{ $unit['title'] }}</td>
                                    <td class="py-2 px-4 text-[#0A3E50] font-bold">{{ $unit['grade'] }}</td>
                                    <td class="py-2 px-4 text-purple-900 font-bold">{{ $unit['points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
