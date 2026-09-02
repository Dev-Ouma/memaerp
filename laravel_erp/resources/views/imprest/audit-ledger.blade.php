@extends('layouts.app')

@section('title', 'Imprest Audit Ledger')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Imprest Audit Ledger & Overdue Aging Analysis</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Track institutional outstanding imprests, 14-day compliance aging brackets, automated payroll salary deduction triggers, and audit risk flags</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Export Audit Report
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Active Imprest</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalActiveImprest'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current outstanding float.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Institutional Ledger</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Current / Not Due</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['currentNotDue'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Within 14-day activity window.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Compliant</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Grace Period Overdue</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['overdue1to14Days'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">1-14 days past deadline.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Notice Dispatched</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Salary Recovery (30+ Days)</div>
            <div class="text-2xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['criticalOverdueSalaryRecovery'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">ERP Payroll sync deduction.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Salary Recovery</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Staff Officer & ID</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Department & Imprest Ref</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Amount Due & Issued Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Due Date & Aging (Days)</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Risk Stage & Recovery Action</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($agingRecords as $rec)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $rec['staff_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $rec['staff_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">{{ $rec['department'] }}</div>
                                <div class="font-mono text-[11px] text-purple-900 mt-0.5">{{ $rec['imprest_ref'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                <div><strong class="text-red-700 font-bold text-xs">{{ $rec['amount_due'] }}</strong></div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">Issued: {{ $rec['issue_date'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-mono font-semibold text-slate-900">Due: {{ $rec['due_date'] }}</div>
                                <div class="font-bold text-red-700 mt-0.5">{{ $rec['days_overdue'] }} Days Overdue</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($rec['risk_category'], 'Critical'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800 mb-1">Critical Overdue</span>
                                @elseif(str_contains($rec['risk_category'], 'Warning'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800 mb-1">Warning Stage</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800 mb-1">Grace Period</span>
                                @endif
                                <div class="text-[11px] font-medium text-slate-700">{{ $rec['recovery_status'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Recover</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
