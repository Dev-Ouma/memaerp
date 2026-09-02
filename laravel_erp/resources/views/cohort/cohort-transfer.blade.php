@extends('layouts.app')

@section('title', 'Cohort Transfer')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Cohort Transfer & Deferral Migrations</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage student intake cohort migrations, academic deferrals, study mode adjustments, and earned credit/fee carry-forwards</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                New Cohort Transfer
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Transfers</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalCohortTransfers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current academic year.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Cohort Shifts</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Approved Migrations</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['approvedMigrations'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Registrar Academic cleared.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">87.1% Approved</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Review</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingDeanReview'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dean / HOD desk review.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Review Queue</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Medical Deferrals</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['specialDeferrals'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Special sabbatical grants.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Sabbaticals</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Transfer Ref & Scholar</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Source &rarr; Target Cohort</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Reason & Financial Audit</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Approval Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($transfers as $tr)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $tr['transfer_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $tr['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $tr['reg_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">{{ $tr['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <span class="text-slate-600 font-semibold">{{ $tr['source_cohort'] }}</span>
                                <div class="text-emerald-700 font-bold mt-0.5">&rarr; {{ $tr['target_cohort'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div><strong class="text-slate-800">{{ $tr['transfer_reason'] }}</strong></div>
                                <div class="text-emerald-700 text-[10.5px] font-mono mt-0.5">{{ $tr['fee_credit_transferred'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($tr['approval_status'], 'Approved'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending Review</span>
                                @endif
                                <div class="text-[10.5px] text-slate-500 mt-1">{{ $tr['approval_status'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Review</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
