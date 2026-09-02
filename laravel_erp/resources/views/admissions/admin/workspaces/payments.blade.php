@extends('layouts.app')

@section('title', 'Application Payments')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Application Fee Payments &amp; M-Pesa Daraja 2.0 Ledger</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Real-time payment attempts, M-Pesa C2B Paybill 880100 transactions, bank slips, and fee waivers</p>
        </div>
        <div class="flex items-center gap-2">
            <details class="relative">
                <summary class="list-none cursor-pointer px-4 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-2xs">
                    Manual Fee Waiver
                </summary>
                <form method="post" action="{{ route('admissions.payments.waiver') }}"
                      class="absolute right-0 z-20 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-2.5">
                    @csrf
                    <input type="text" name="application_number" required placeholder="Application number"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-[#0A3E50]">
                    <select name="reason_code" required
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-[#0A3E50]">
                        <option value="FINANCIAL_HARDSHIP">Financial hardship</option>
                        <option value="SCHOLARSHIP">Scholarship or sponsorship</option>
                        <option value="STAFF_DEPENDANT">Staff dependant</option>
                        <option value="INSTITUTIONAL_ERROR">Institutional error</option>
                    </select>
                    <textarea name="justification" required rows="3" placeholder="Justification recorded in the audit trail"
                              class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-[#0A3E50]"></textarea>
                    <button type="submit" class="w-full px-4 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors">
                        Authorise Waiver
                    </button>
                </form>
            </details>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Paid Revenue</div>
            <div class="text-3xl font-extrabold text-[#1E8449] mt-2 mb-1.5 leading-none">KES {{ number_format((float) $stats['totalPaidRevenue']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Application fees collected.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">100% Settled</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Paid Transactions</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format((float) $stats['paidTransactions']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Successful payment receipts.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">KES 1,500 Mean</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">M-Pesa Dominance</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['mpesaPercentage'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Instant mobile payments.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Daraja 2.0 Realtime</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authorized Waivers</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['waivedApplications'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">VC / Need-based waivers.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Audited &amp; Signed</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $paymentRecords,
        'noun' => 'application fee transactions',
        'search' => 'Search applicant, reference...',
        'selects' => [
            ['name' => 'status', 'label' => 'All Statuses', 'options' => ['PAID' => 'Paid', 'PENDING' => 'Pending', 'FAILED' => 'Failed', 'WAIVED' => 'Waived']],
            ['name' => 'channel', 'label' => 'All Channels', 'options' => $channels],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Transaction Ref &amp; App No</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Applicant Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Applied Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Amount (KES)</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Channel &amp; Account</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Timestamp</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($paymentRecords as $pay)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-emerald-900 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">{{ $pay['transaction_ref'] }}</span>
                                <div class="font-mono text-[11px] text-slate-500 mt-1">{{ $pay['app_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $pay['applicant_name'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $pay['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-extrabold text-[#1E8449] text-sm">KES {{ number_format((float) $pay['amount']) }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $pay['channel'] }}</div>
                                <div class="font-mono text-[10.5px] text-slate-400">Ref: {{ $pay['account_ref'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $pay['timestamp'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($pay['status'], 'PAID'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">PAID &amp; SETTLED</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-purple-100 text-purple-800">WAIVED</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(in_array($pay['status'], ['PAID', 'WAIVED'], true))
                                    <a href="{{ route('admissions.payments.receipt', $pay['attempt_id']) }}" target="_blank" rel="noopener"
                                       class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition-colors">
                                        Receipt
                                    </a>
                                @else
                                    <span class="px-3 py-1 rounded border border-slate-200 text-slate-300 font-semibold text-xs">Receipt</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 8,
                            'message' => 'No application fee has been recorded.',
                            'hint' => 'Transactions appear here as applicants pay the application fee.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $paymentRecords])
    </div>
</div>
@endsection
