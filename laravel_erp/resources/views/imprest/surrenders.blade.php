@extends('layouts.app')

@section('title', 'Imprest Surrenders')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Imprest Surrenders & Expense Reconciliation</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify electronic KRA ETIMS receipts, actual expenditure audits, unspent balance banking, and supplementary claim processing</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Submit Imprest Surrender
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Accounted Volume</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['surrenderedThisMonth'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current month surrenders.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Reconciliation Cycle</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Fully Cleared</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['fullyReconciled'] }} Vouchers</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Internal Audit approved.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Zero Liability</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Audit Vetting</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingAuditVerification'] }} Surrenders</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Audit desk checking.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Under Review</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Unspent Refunds Banked</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['refundsRecovered'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Returned to University bank.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Cash Recovered</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Surrender No & Staff</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Requisition Ref & Dept</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Imprest / Actual Spent</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Refund / Supplementary & ETIMS</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Audit Verdict & Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($surrenders as $sur)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $sur['surrender_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $sur['staff_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-[11px] text-purple-900 font-bold">{{ $sur['requisition_ref'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $sur['department'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                <div>Imprest: <strong class="text-slate-900">{{ $sur['imprest_amount'] }}</strong></div>
                                <div class="text-emerald-800 font-bold mt-0.5">Spent: {{ $sur['actual_expenditure'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                @if(isset($sur['unspent_refund']))
                                    <div class="font-mono font-bold text-blue-900">{{ $sur['unspent_refund'] }}</div>
                                @elseif(isset($sur['supplementary_claim']))
                                    <div class="font-mono font-bold text-purple-900">{{ $sur['supplementary_claim'] }}</div>
                                @endif
                                <div class="text-[10.5px] text-slate-500 mt-0.5">{{ $sur['etims_compliance'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] font-semibold text-[#0A3E50]">{{ $sur['audit_verdict'] }}</div>
                                <div class="mt-1">
                                    @if(str_contains($sur['surrender_status'], 'Closed'))
                                        <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Closed</span>
                                    @elseif(str_contains($sur['surrender_status'], 'Under Verification'))
                                        <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Under Verification</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $sur['surrender_status'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Audit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
