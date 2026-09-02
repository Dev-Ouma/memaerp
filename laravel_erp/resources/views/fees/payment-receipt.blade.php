@extends('layouts.app')

@section('title', 'Payment Receipt')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Tuition Payment Receipts & Statement Ledger</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify student transaction logs, print official payment receipts with QR validation, and view trimester statement balances</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Export Statement
            </button>
            <button type="button" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs">
                Print Official Receipt
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Receipts Issued</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['receiptsIssued']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current academic trimesters.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Receipts</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Issued Today</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['receiptsIssuedToday'] }} Receipts</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Tuition clearances processed.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Daily Clear</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Reconciliation Accuracy</div>
            <div class="text-sm font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['receiptAccuracy'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Reconciled bank records.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Reconciled</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Audit Trail Integrity</div>
            <div class="text-xs font-extrabold text-purple-900 mt-3 mb-2 leading-snug">{{ $stats['auditLogIntegrity'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Tamper-proof ledger logs.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Audit Secured</span></div>
        </div>
    </div>

    {{-- Receipt Statement Block --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs p-6 mb-6">
        <div class="border-b border-slate-200 pb-5 mb-5 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $studentInfo['name'] }}</h2>
                <p class="text-xs font-mono text-purple-900 mt-0.5 font-bold">{{ $studentInfo['reg_no'] }}</p>
                <p class="text-xs text-slate-600 mt-1">{{ $studentInfo['programme'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $studentInfo['school'] }}</p>
            </div>
            <div class="text-center sm:text-right">
                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-[#0A3E50] text-white">TUITION FEE STATEMENT</span>
                <p class="text-xs text-slate-600 mt-2"><strong>Total Billed:</strong> {{ $studentInfo['total_billed_trimester'] }}</p>
                <p class="text-xs text-emerald-700 font-bold mt-1"><strong>Total Paid:</strong> {{ $studentInfo['total_cleared_trimester'] }}</p>
                <p class="text-[11px] text-slate-500 mt-1"><strong>Outstanding Arrears:</strong> {{ $studentInfo['balance_remaining'] }}</p>
            </div>
        </div>

        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-1 mb-2">Reconciled Payments & Receipts</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Receipt No</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Amount Paid</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Mode Channel</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Bank Transaction ID Reference</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Timestamp</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                    @foreach($receipts as $rec)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 text-slate-900 font-bold">{{ $rec['receipt_no'] }}</td>
                            <td class="py-3 px-4 text-emerald-800 font-bold">{{ $rec['amount_paid'] }}</td>
                            <td class="py-3 px-4 font-sans font-semibold text-slate-800">{{ $rec['payment_mode'] }}</td>
                            <td class="py-3 px-4 text-purple-900 font-bold">{{ $rec['bank_transaction_id'] }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $rec['timestamp'] }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 font-sans">{{ $rec['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
