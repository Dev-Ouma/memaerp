@extends('layouts.app')

@section('title', 'Gradebook & ERP Marks Sync')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">LMS Continuous Assessment Gradebook & ERP Examination Sync</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Synchronize continuous assessment test (CAT) marks (30% weight) from LMS shells directly into MEMA ERP examination & grading hub</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Batch Sync CAT Marks
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Synced Marks</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['totalSyncedGrades']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Continuous assessment records.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">CAT Records</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">CAT Weight Model</div>
            <div class="text-lg font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['catMarksWeight'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">30% CAT + 70% Final Exam.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">CUE Standard</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Internal Audit Status</div>
            <div class="text-sm font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['erpSyncAuditStatus'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Immutable marks integrity.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Audit Cleared</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Sync Accuracy</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['syncAccuracy'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Zero ledger discrepancies.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Zero Error</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Sync Ref & Course Unit</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cohort & Enrolled Headcount</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">CAT 1 & CAT 2 Breakdown</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">ERP Exam Engine Integration</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Sync Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($syncLedgers as $g)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $g['sync_ref'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $g['course_code'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-mono text-purple-900 font-semibold">{{ $g['cohort'] }}</div>
                                <div class="text-slate-500 font-medium text-[10.5px] mt-0.5">{{ $g['enrolled_students'] }} Enrolled Scholars</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div>CAT 1: <strong class="text-slate-800">{{ $g['cat1_weight'] }}</strong> | CAT 2: <strong class="text-slate-800">{{ $g['cat2_weight'] }}</strong></div>
                                <div class="text-emerald-800 font-bold font-mono text-[10.5px] mt-0.5">{{ $g['total_cat_synced'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-semibold text-[#0A3E50]">{{ $g['erp_exam_engine_sync'] }}</div>
                                <div class="text-slate-400 font-mono text-[10px] mt-0.5">{{ $g['sync_timestamp'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($g['status'], 'Synchronized'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $g['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $g['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Audit Sync</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
