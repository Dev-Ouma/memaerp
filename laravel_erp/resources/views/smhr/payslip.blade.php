@extends('layouts.app')

@section('title', 'Staff Payslip - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Top Action Header & Filter Controls --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.payroll-register') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; Payroll Register</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Official Payslip</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Employee Pay Advice / Payslip</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Confidential official university monthly salary statement with statutory deductions and bank remittance</p>
        </div>

        {{-- Dynamic Staff & Period Selector Form --}}
        <div class="flex items-center gap-2.5 flex-wrap bg-white p-2 rounded-xl border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('smhr.payslip') }}" id="payslipSelectorForm" class="flex items-center gap-2 flex-wrap">
                <div>
                    <select name="staff_id" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-slate-50 focus:ring-2 focus:ring-[#0A3E50] focus:outline-none">
                        @foreach($allStaff as $idKey => $stf)
                            <option value="{{ $idKey }}" {{ $selectedStaffId === $idKey ? 'selected' : '' }}>
                                {{ $stf['id'] }} &middot; {{ $stf['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="month" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-slate-50 focus:ring-2 focus:ring-[#0A3E50] focus:outline-none">
                        @foreach($availableMonths as $m)
                            <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="year" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-slate-50 focus:ring-2 focus:ring-[#0A3E50] focus:outline-none">
                        <option value="2026" {{ $year === '2026' ? 'selected' : '' }}>2026</option>
                        <option value="2025" {{ $year === '2025' ? 'selected' : '' }}>2025</option>
                    </select>
                </div>
            </form>

            <a href="{{ route('smhr.p9-form', ['staffId' => $payslipData['staff_id'], 'year' => $payslipData['year']]) }}" class="px-3 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="file-text" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">KRA P9 Form</span>
            </a>

            <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Print (PDF)</span>
            </button>
        </div>
    </div>

    {{-- Official Printable Payslip Document Card --}}
    <div class="bg-white border border-slate-300 rounded-xl shadow-lg p-6 sm:p-10 max-w-4xl mx-auto print:border-none print:shadow-none print:p-0 print:max-w-none text-slate-900">
        
        {{-- University Header & Logo --}}
        <div class="text-center pb-5 border-b-2 border-slate-900 mb-6 relative">
            {{-- Top Watermark Stamp for Screen & Print --}}
            <div class="absolute right-0 top-0 hidden sm:block text-right">
                <div class="inline-block border-2 border-emerald-600 rounded-lg px-2.5 py-1 transform rotate-6 bg-emerald-50/80">
                    <span class="text-[10px] font-black text-emerald-800 tracking-wider block">&check; PAID &middot; DIRECT EFT</span>
                    <span class="text-[9px] font-mono text-emerald-700 block">{{ $payslipData['eft_ref'] }}</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-3.5 justify-center mb-2">
                <div class="w-13 h-13 rounded-xl bg-[#0A3E50] flex items-center justify-center text-white font-black text-2xl shadow-sm">
                    M
                </div>
                <div class="text-left">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight leading-tight">MEMA UNIVERSITY COLLEGE</h2>
                    <p class="text-[11.5px] font-bold text-slate-700">DIVISION OF FINANCE, PLANNING &amp; ADMINISTRATION &middot; SALARIES SECTION</p>
                </div>
            </div>
            <div class="text-[11px] text-slate-500 font-mono">
                P.O. Box 90120-80100 Mombasa, Kenya &middot; Tel: +254 (0) 700 000 000 &middot; Email: payroll@mema.ac.ke &middot; KRA PIN: P051234567Z
            </div>
            <div class="mt-3.5 inline-block px-5 py-1.5 rounded-md bg-slate-900 font-black text-xs text-white uppercase tracking-widest shadow-xs">
                OFFICIAL MONTHLY SALARY ADVICE / PAYSLIP &middot; {{ strtoupper($payslipData['pay_period']) }}
            </div>
        </div>

        {{-- Personnel Particulars & Bank Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-xs mb-6 pb-6 border-b border-slate-300">
            <div class="space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Employee Name:</span>
                    <span class="font-extrabold text-slate-900">{{ $payslipData['staff_name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Staff Payroll No:</span>
                    <span class="font-mono font-black text-[#0A3E50]">{{ $payslipData['staff_id'] }} ({{ $payslipData['payslip_no'] }})</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Designation:</span>
                    <span class="font-bold text-slate-800">{{ $payslipData['designation'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Department:</span>
                    <span class="text-slate-800 font-medium">{{ $payslipData['department'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Faculty / School:</span>
                    <span class="text-slate-800 font-medium">{{ $payslipData['faculty'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Salary Grade / Scale:</span>
                    <span class="font-bold text-purple-900">{{ $payslipData['job_group'] }}</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">National ID No:</span>
                    <span class="font-mono text-slate-900 font-bold">{{ $payslipData['id_no'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">KRA Tax PIN:</span>
                    <span class="font-mono text-slate-900 font-bold">{{ $payslipData['kra_pin'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">NSSF &middot; SHA/NHIF No:</span>
                    <span class="font-mono text-slate-900">{{ $payslipData['nssf_no'] }} &middot; {{ $payslipData['nhif_no'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Bank &amp; Branch:</span>
                    <span class="font-bold text-slate-800">{{ $payslipData['bank_name'] }} ({{ $payslipData['branch'] }})</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Bank Account No:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $payslipData['account_no'] }} <small class="text-slate-400">({{ $payslipData['sort_code'] }})</small></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Disbursement Date:</span>
                    <span class="font-bold text-[#1E8449]">{{ $payslipData['pay_date'] }}</span>
                </div>
            </div>
        </div>

        {{-- Side-by-Side Earnings & Deductions Tables --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            {{-- Earnings Box --}}
            <div class="border border-slate-300 rounded-lg overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="bg-slate-100 px-3.5 py-2 font-black text-xs text-slate-900 uppercase tracking-wider border-b border-slate-300 flex justify-between items-center">
                        <span>EARNINGS &amp; ALLOWANCES</span>
                        <span class="font-mono">AMOUNT (KES)</span>
                    </div>
                    <div class="p-3.5 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-700">Basic Monthly Salary</span>
                            <span class="font-mono font-bold text-slate-900">{{ number_format($payslipData['basic_salary'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700">House Allowance</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['house_allowance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700">Commuter Allowance</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['commuter_allowance'], 2) }}</span>
                        </div>
                        @if($payslipData['responsibility_allowance'] > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-700">Responsibility Allowance</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['responsibility_allowance'], 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-slate-100 px-3.5 py-2.5 border-t-2 border-slate-300 flex justify-between font-black text-xs">
                    <span class="text-slate-900 uppercase">TOTAL GROSS EARNINGS</span>
                    <span class="font-mono text-[#0A3E50] text-sm font-black">KES {{ number_format($payslipData['gross_earnings'], 2) }}</span>
                </div>
            </div>

            {{-- Deductions Box --}}
            <div class="border border-slate-300 rounded-lg overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="bg-slate-100 px-3.5 py-2 font-black text-xs text-slate-900 uppercase tracking-wider border-b border-slate-300 flex justify-between items-center">
                        <span>STATUTORY &amp; OTHER DEDUCTIONS</span>
                        <span class="font-mono">AMOUNT (KES)</span>
                    </div>
                    <div class="p-3.5 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <div>
                                <span class="text-slate-700 font-medium">P.A.Y.E Tax (Net of Reliefs)</span>
                                <span class="text-[10px] text-slate-400 block">Gross Tax: {{ number_format($payslipData['gross_tax']) }} &middot; Relief: {{ number_format($payslipData['total_relief']) }}</span>
                            </div>
                            <span class="font-mono font-bold text-rose-700">{{ number_format($payslipData['net_tax'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700">NSSF Tier I ({{ number_format($payslipData['nssf_tier1']) }}) &amp; Tier II ({{ number_format($payslipData['nssf_tier2']) }})</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['total_nssf'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700">Social Health Authority (SHA 2.75%)</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['nhif_sha'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700">Affordable Housing Levy (1.5%)</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['housing_levy'], 2) }}</span>
                        </div>
                        @if($payslipData['sacco_shares'] > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-700">Staff Welfare &amp; SACCO Savings</span>
                            <span class="font-mono font-semibold text-slate-900">{{ number_format($payslipData['sacco_shares'], 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-slate-100 px-3.5 py-2.5 border-t-2 border-slate-300 flex justify-between font-black text-xs">
                    <span class="text-slate-900 uppercase">TOTAL DEDUCTIONS</span>
                    <span class="font-mono text-rose-700 text-sm font-black">KES {{ number_format($payslipData['total_deductions'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Net Salary Banner --}}
        <div class="p-4 rounded-xl bg-emerald-50 border-2 border-[#1E8449] mb-6 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div>
                <span class="text-xs font-black text-emerald-950 uppercase tracking-wider block">NET PAYABLE SALARY (DIRECT EFT BANK REMITTANCE)</span>
                <span class="text-[11.5px] text-emerald-800 font-semibold italic">Amount in words: {{ $payslipData['net_pay_words'] }}</span>
            </div>
            <div class="font-mono font-black text-2xl text-[#1E8449]">
                KES {{ number_format($payslipData['net_pay'], 2) }}
            </div>
        </div>

        {{-- Cumulative Year-To-Date (YTD) Summary Card --}}
        <div class="mb-6 p-3.5 rounded-lg border border-slate-300 bg-slate-50/80 text-xs">
            <div class="font-black text-slate-800 uppercase tracking-wider mb-2 text-[10.5px]">
                YEAR-TO-DATE (YTD) ACCUMULATED TOTALS &middot; JAN {{ $payslipData['year'] }} TO {{ strtoupper(substr($payslipData['month'], 0, 3)) }} {{ $payslipData['year'] }} ({{ $payslipData['month_index'] }} Months)
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 text-center font-mono">
                <div class="p-2 bg-white rounded border border-slate-200">
                    <span class="text-[9.5px] text-slate-500 font-sans block font-bold">YTD Gross</span>
                    <span class="font-black text-slate-900 text-xs">{{ number_format($payslipData['ytd_gross']) }}</span>
                </div>
                <div class="p-2 bg-white rounded border border-slate-200">
                    <span class="text-[9.5px] text-slate-500 font-sans block font-bold">YTD Taxable</span>
                    <span class="font-bold text-blue-900 text-xs">{{ number_format($payslipData['ytd_taxable']) }}</span>
                </div>
                <div class="p-2 bg-white rounded border border-slate-200">
                    <span class="text-[9.5px] text-slate-500 font-sans block font-bold">YTD PAYE Tax</span>
                    <span class="font-bold text-rose-700 text-xs">{{ number_format($payslipData['ytd_paye']) }}</span>
                </div>
                <div class="p-2 bg-white rounded border border-slate-200">
                    <span class="text-[9.5px] text-slate-500 font-sans block font-bold">YTD NSSF</span>
                    <span class="font-bold text-slate-800 text-xs">{{ number_format($payslipData['ytd_nssf']) }}</span>
                </div>
                <div class="p-2 bg-white rounded border border-slate-200">
                    <span class="text-[9.5px] text-slate-500 font-sans block font-bold">YTD SHA</span>
                    <span class="font-bold text-slate-800 text-xs">{{ number_format($payslipData['ytd_sha']) }}</span>
                </div>
                <div class="p-2 bg-emerald-50 rounded border border-emerald-300">
                    <span class="text-[9.5px] text-emerald-800 font-sans block font-bold">YTD Net Pay</span>
                    <span class="font-black text-emerald-900 text-xs">{{ number_format($payslipData['ytd_net_pay']) }}</span>
                </div>
            </div>
        </div>

        {{-- Verification Signatures & Digital Security Footnote --}}
        <div class="pt-6 border-t-2 border-slate-300">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center text-xs mb-4">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="text-[10px] text-slate-500 font-bold uppercase">Prepared &amp; Verified By:</div>
                    <div class="mt-2 font-black text-slate-800">Faith Muthoni</div>
                    <div class="text-[10px] text-slate-500">Senior Payroll Officer &middot; HR</div>
                    <div class="text-[9.5px] text-emerald-700 font-bold mt-1">&check; Verified on {{ $payslipData['pay_date'] }}</div>
                </div>

                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="text-[10px] text-slate-500 font-bold uppercase">Authorised For Payment:</div>
                    <div class="mt-2 font-black text-slate-800">Prof. Allan Wabwire</div>
                    <div class="text-[10px] text-slate-500">Deputy Vice Chancellor &middot; Finance</div>
                    <div class="text-[9.5px] text-emerald-700 font-bold mt-1">&check; Digital Senate Authorisation</div>
                </div>

                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex flex-col justify-center items-center">
                    <div class="text-[10px] text-slate-500 font-bold uppercase">Cryptographic Audit Seal:</div>
                    <div class="font-mono text-[9px] text-slate-600 font-bold mt-1 break-all">
                        {{ substr($payslipData['verification_hash'], 0, 32) }}
                    </div>
                    <div class="text-[9px] text-slate-400 font-mono mt-0.5">SHA-256 Validated System Record</div>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-500 border-t border-slate-200 pt-2 font-mono">
                This is a computer-generated official salary advice document issued under the authority of MEMA University College. Questions regarding deductions should be directed to the Salaries Section within 14 days of pay date.
            </div>
        </div>
    </div>
</div>
@endsection
