@extends('layouts.app')

@section('title', 'KRA Form P9A (Tax Deduction Card) - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Top Action Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.reports') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Reports</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">KRA Form P9A</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">KRA Form P9A — Tax Deduction Card</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Statutory annual tax return certificate for individual staff member tax filing on KRA iTax</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Staff & Year Selector --}}
            <form method="GET" action="{{ route('smhr.p9-form') }}" class="flex items-center gap-2 flex-wrap">
                @if(isset($allStaff))
                <select name="staff_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                    @foreach($allStaff as $idKey => $stf)
                        <option value="{{ $idKey }}" {{ ($selectedId ?? '') === $idKey ? 'selected' : '' }}>
                            {{ $stf['id'] }} &middot; {{ $stf['name'] }}
                        </option>
                    @endforeach
                </select>
                @endif
                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                    <option value="2025" {{ $year == '2025' ? 'selected' : '' }}>Tax Year 2025</option>
                    <option value="2024" {{ $year == '2024' ? 'selected' : '' }}>Tax Year 2024</option>
                    <option value="2023" {{ $year == '2023' ? 'selected' : '' }}>Tax Year 2023</option>
                </select>
            </form>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Print Form P9A (PDF)</span>
            </button>
        </div>
    </div>

    {{-- Official Printable P9 Card --}}
    <div class="bg-white border border-slate-300 rounded-xl shadow-md p-6 sm:p-8 print:border-none print:shadow-none print:p-0">
        {{-- KRA Header --}}
        <div class="text-center pb-4 border-b-2 border-slate-900 mb-6">
            <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">KENYA REVENUE AUTHORITY</h2>
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mt-0.5">DOMESTIC TAXES DEPARTMENT</h3>
            <div class="mt-2 inline-block px-4 py-1 rounded bg-slate-900 font-bold text-xs text-white uppercase tracking-wider">
                TAX DEDUCTION CARD (FORM P9A) — YEAR {{ $year }}
            </div>
        </div>

        {{-- Employer & Employee Particulars --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-6 pb-4 border-b border-slate-300">
            <div class="space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-600 font-semibold">Employer's Name:</span>
                    <span class="font-bold text-slate-900">{{ $staffDetails['employer_name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600 font-semibold">Employer's PIN:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $staffDetails['employer_pin'] }}</span>
                </div>
            </div>
            <div class="space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-600 font-semibold">Employee's Name:</span>
                    <span class="font-bold text-slate-900">{{ $staffDetails['name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600 font-semibold">Employee's PIN:</span>
                    <span class="font-mono font-bold text-[#0A3E50]">{{ $staffDetails['kra_pin'] }} ({{ $staffDetails['staff_id'] }})</span>
                </div>
            </div>
        </div>

        {{-- Complete 12-Month Table (Cols A to L) --}}
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-left border-collapse text-[11px] border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 font-bold border-b border-slate-300 text-center">
                        <th class="p-2 border-r border-slate-300">Month</th>
                        <th class="p-2 border-r border-slate-300">Basic Salary<br><span class="font-normal text-[10px]">(A)</span></th>
                        <th class="p-2 border-r border-slate-300">Benefits Non-Cash<br><span class="font-normal text-[10px]">(B)</span></th>
                        <th class="p-2 border-r border-slate-300">Value of Quarters<br><span class="font-normal text-[10px]">(C)</span></th>
                        <th class="p-2 border-r border-slate-300">Total Gross Pay<br><span class="font-normal text-[10px]">(D)</span></th>
                        <th class="p-2 border-r border-slate-300">Pension/NSSF Cont.<br><span class="font-normal text-[10px]">(F)</span></th>
                        <th class="p-2 border-r border-slate-300">Chargeable Pay<br><span class="font-normal text-[10px]">(H)</span></th>
                        <th class="p-2 border-r border-slate-300">Tax Charged<br><span class="font-normal text-[10px]">(J)</span></th>
                        <th class="p-2 border-r border-slate-300">Personal Relief<br><span class="font-normal text-[10px]">(K)</span></th>
                        <th class="p-2">P.A.Y.E Tax<br><span class="font-normal text-[10px]">(L)</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($p9Rows as $row)
                        <tr class="text-right font-mono hover:bg-slate-50 transition-colors">
                            <td class="p-2 text-left font-sans font-semibold text-slate-800 border-r border-slate-300">{{ $row['month'] }}</td>
                            <td class="p-2 border-r border-slate-300">{{ number_format($row['col_a']) }}</td>
                            <td class="p-2 border-r border-slate-300">{{ number_format($row['col_b']) }}</td>
                            <td class="p-2 border-r border-slate-300">{{ number_format($row['col_c']) }}</td>
                            <td class="p-2 border-r border-slate-300 font-bold text-slate-900">{{ number_format($row['col_d']) }}</td>
                            <td class="p-2 border-r border-slate-300">{{ number_format($row['col_f']) }}</td>
                            <td class="p-2 border-r border-slate-300 font-bold text-blue-900">{{ number_format($row['col_h']) }}</td>
                            <td class="p-2 border-r border-slate-300 text-rose-900">{{ number_format($row['col_j']) }}</td>
                            <td class="p-2 border-r border-slate-300 text-slate-700">{{ number_format($row['col_k']) }}</td>
                            <td class="p-2 font-extrabold text-[#1E8449]">{{ number_format($row['col_l']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-mono font-extrabold text-slate-900 text-right border-t-2 border-slate-900">
                        <td class="p-2 text-left font-sans uppercase">TOTALS (KES)</td>
                        <td class="p-2 border-r border-slate-300">{{ number_format($totals['col_a']) }}</td>
                        <td class="p-2 border-r border-slate-300">{{ number_format($totals['col_b']) }}</td>
                        <td class="p-2 border-r border-slate-300">{{ number_format($totals['col_c']) }}</td>
                        <td class="p-2 border-r border-slate-300 text-[#0A3E50]">{{ number_format($totals['col_d']) }}</td>
                        <td class="p-2 border-r border-slate-300">{{ number_format($totals['col_f']) }}</td>
                        <td class="p-2 border-r border-slate-300 text-blue-900">{{ number_format($totals['col_h']) }}</td>
                        <td class="p-2 border-r border-slate-300 text-rose-900">{{ number_format($totals['col_j']) }}</td>
                        <td class="p-2 border-r border-slate-300">{{ number_format($totals['col_k']) }}</td>
                        <td class="p-2 font-black text-[#1E8449] text-xs">{{ number_format($totals['col_l']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Employer Certification Notice --}}
        <div class="text-xs text-slate-600 bg-slate-50 p-4 rounded-lg border border-slate-200">
            <div class="font-bold text-slate-900 mb-1">EMPLOYER'S CERTIFICATE OF ACCURACY</div>
            <p class="leading-relaxed">
                I/We certify that the summary above gives the true and complete particulars of the employee's emoluments and tax deducted under P.A.Y.E rules during the year <strong>{{ $year }}</strong> in accordance with Section 37 of the Income Tax Act (Cap. 470).
            </p>
            <div class="mt-4 flex justify-between items-center pt-2 border-t border-slate-200">
                <span>Authorized Signatory: <strong>{{ auth()->user()?->name ?? 'Head of HR' }}</strong></span>
                <span class="font-mono">Date: <strong>{{ date('d M Y') }}</strong></span>
            </div>
        </div>
    </div>
</div>
@endsection
