@extends('layouts.app')

@section('title', 'Fees & Financial Reports')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tuition Fee Collection, Debtors & Ageing Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify total trimester invoiced fees, direct net collection payments, outstanding balance arrears, and fee split sponsors allocations</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Download Financial Book</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Target Programme</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Total Billed Invoiced</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Total Net Collected</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Outstanding Debtors Balance</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Overpayment Ledger Credits</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Debtors Ageing Overdue > 60 Days</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                @foreach($financials as $f)
                    <tr class="hover:bg-slate-50/70 transition-colors font-sans">
                        <td class="py-3.5 px-4 font-sans font-bold text-slate-900 text-xs">{{ $f['programme'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">{{ $f['invoiced'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">{{ $f['collected'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-red-700">{{ $f['arrears'] }}</td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-[#0A3E50]">{{ $f['overpayments'] }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $f['debtors_ageing'] }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Audit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
