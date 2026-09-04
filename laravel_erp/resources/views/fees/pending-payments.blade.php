@extends('layouts.app')

@section('title', 'Pending Payment Confirmation')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pending Payment Confirmation & Statement Reconciliation</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify student offline bank deposit slips uploads, matching against bank statement ledgers, and resolve M-Pesa push API drops</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Upload Bank Statement
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Unconfirmed Queue</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['unconfirmedTransactions'] }} Trans.</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Pending confirmation checks.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Unconfirmed</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Bank Slip Uploads</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['bankSlipUploads'] }} slips</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Manual slip vetting queues.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Manual Slips</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">M-Pesa Discrepancy</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['mpesaDiscrepancies'] }} Logs</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Reconciliation mismatches.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">M-Pesa Vetting</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Awaiting Audit Value</div>
            <div class="text-sm font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['totalAwaitingAudit'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Outstanding validation value.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Audit Value</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Ref & Student</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Mode Channel</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Transaction Ref ID</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Amount</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Upload Date / Time</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Audit Verdict Log</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($pendings as $pnd)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $pnd['payment_ref'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $pnd['student_name'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $pnd['reg_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">{{ $pnd['payment_method'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-purple-900 font-bold">{{ $pnd['transaction_ref'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#0A3E50]">{{ $pnd['amount'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $pnd['upload_timestamp'] }}</td>
                            <td class="py-3.5 px-4 text-xs font-medium text-purple-900">{{ $pnd['verdict'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $pnd['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(! empty($pnd['id']))
                                    <form method="POST" action="{{ route('fees.payments.confirm', $pnd['id']) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded border border-emerald-500 text-emerald-700 hover:bg-emerald-50 font-semibold text-xs transition-colors">
                                            Confirm
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
