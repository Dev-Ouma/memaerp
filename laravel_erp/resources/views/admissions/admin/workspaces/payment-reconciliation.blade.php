@extends('layouts.app')

@section('title', 'Payment Reconciliation')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Payment Reconciliation &amp; Settlement Batches</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Automatic settlement matching between Safaricom M-Pesa C2B statements, commercial bank feeds, and application payment ledger</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('admissions.reconciliation.run') }}">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs">Run Auto-Match Job</button>
            </form>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Reconciled Total</div>
            <div class="text-3xl font-extrabold text-[#1E8449] mt-2 mb-1.5 leading-none">KES {{ number_format($stats['reconciledTotal']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Matched against bank GL.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Balanced Ledger</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Reconciliation Rate</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['reconciliationRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Zero unaccounted variance.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">High Precision</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Unmatched Deposits</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['unmatchedDeposits'] }} Items</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Pending applicant reference match.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">In Triage</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Discrepancies</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['pendingDiscrepancies'] }} Cases</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Chargebacks or reversals.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Clean Audit</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $reconciliations,
        'noun' => 'reconciliation runs',
        'search' => 'Search statement reference...',
        'selects' => [
            ['name' => 'provider', 'label' => 'All Providers', 'options' => $providers],
            ['name' => 'status', 'label' => 'All Statuses', 'options' => ['COMPLETED' => 'Completed', 'EXCEPTIONS' => 'Exceptions', 'IN_PROGRESS' => 'In Progress']],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Batch ID &amp; Source</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Statement Period</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">ERP Ledger Sum</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Bank / M-Pesa Sum</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Variance</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px] text-center" style="color:#ffffff !important;">Matched / Unmatched</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reconciliations as $rec)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $rec['batch_id'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $rec['source'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $rec['period'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">KES {{ number_format($rec['erp_sum']) }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#1E8449]">KES {{ number_format($rec['bank_sum']) }}</td>
                            <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">KES {{ number_format($rec['variance']) }}</td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="font-bold text-emerald-700">{{ $rec['matched_count'] }}</span> /
                                <span class="font-bold text-slate-400">{{ $rec['unmatched_count'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $rec['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.workspace.audit', ['q' => $rec['batch_id']]) }}" class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition-colors">
                                    Audit
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 8,
                            'message' => 'No reconciliation run has been recorded.',
                            'hint' => 'Run the ledger match to produce the first reconciliation.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $reconciliations])
    </div>
</div>
@endsection
