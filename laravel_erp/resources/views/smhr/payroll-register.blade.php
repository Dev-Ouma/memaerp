@extends('layouts.app')

@section('title', 'Payroll Register & Payslips - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Payroll Register</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Payroll &amp; Statutory Compensation Register</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Monthly staff payroll ledger, PAYE, SHA/NHIF, NSSF, Housing Levy, and banking batch disbursement</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="alert('Generating Bank Electronic Funds Transfer (EFT) Batch File...')" class="px-3.5 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="file-check" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Export Bank EFT Batch</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print Master Sheet</span>
            </button>
        </div>
    </div>

    {{-- Payroll Summary Card --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs mb-7">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wider">Payroll Cycle: {{ $payrollSummary['month'] }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Disbursement batch reference #PAY-2026-AUG-B01</p>
            </div>
            <div>
                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-200 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $payrollSummary['disbursedStatus'] }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="text-slate-500 font-medium">Gross Salaries</div>
                <div class="font-mono font-extrabold text-[#0A3E50] text-base mt-1">KES {{ number_format($payrollSummary['grossSalary']) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="text-slate-500 font-medium">Allowances</div>
                <div class="font-mono font-extrabold text-blue-700 text-base mt-1">KES {{ number_format($payrollSummary['totalAllowances']) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="text-slate-500 font-medium">Statutory PAYE</div>
                <div class="font-mono font-extrabold text-rose-700 text-base mt-1">KES {{ number_format($payrollSummary['statutoryPAYE']) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="text-slate-500 font-medium">SHA / NHIF</div>
                <div class="font-mono font-extrabold text-purple-700 text-base mt-1">KES {{ number_format($payrollSummary['statutoryNHIF']) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="text-slate-500 font-medium">Housing Levy (1.5%)</div>
                <div class="font-mono font-extrabold text-amber-700 text-base mt-1">KES {{ number_format($payrollSummary['housingLevy']) }}</div>
            </div>
            <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                <div class="text-emerald-800 font-bold">Net Remittance</div>
                <div class="font-mono font-extrabold text-[#1E8449] text-base mt-1">KES {{ number_format($payrollSummary['netPayable']) }}</div>
            </div>
        </div>
    </div>

    {{-- Master Payroll Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-900">Staff Payroll Schedule &amp; Individual Slips</h2>
            <span class="text-xs text-slate-500">148 Active Employees Processed</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Pay Slip ID</th>
                        <th class="py-3 px-4">Staff Member &amp; Bank</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4 text-right">Basic Pay</th>
                        <th class="py-3 px-4 text-right">Allowances</th>
                        <th class="py-3 px-4 text-right">Gross Pay</th>
                        <th class="py-3 px-4 text-right">PAYE Tax</th>
                        <th class="py-3 px-4 text-right">Statutory Deductions</th>
                        <th class="py-3 px-4 text-right font-bold text-slate-900">Net Salary</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($payrollItems as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $item['id'] }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $item['name'] }}</div>
                                <div class="text-[10.5px] text-slate-500">{{ $item['bank'] }} &middot; {{ $item['staff_id'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $item['dept'] }}</td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">KES {{ number_format($item['basic_pay']) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-blue-700">KES {{ number_format($item['allowances']) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#0A3E50]">KES {{ number_format($item['gross']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-rose-700">KES {{ number_format($item['paye']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-amber-700">KES {{ number_format($item['statutory']) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-[#1E8449] text-sm">KES {{ number_format($item['net_pay']) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
