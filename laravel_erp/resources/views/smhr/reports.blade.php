@extends('layouts.app')

@section('title', 'SMHR Reports & Statutory Returns - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Reports Centre</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">SMHR Reports &amp; Statutory Returns</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Payroll variance analysis, KRA P9 batches, SHA/NSSF statutory schedules, and staff establishment audits</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="exportReportCSV()" class="px-3.5 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Export Active Report (CSV)</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Annual Payroll Spend</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">KES {{ number_format($reportMetrics['annualPayrollSpend'] / 1000000, 1) }}M</div>
            <p class="text-[11px] text-slate-500 mt-0.5">148 Employees</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Annual PAYE Remitted</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-1.5">KES {{ number_format($reportMetrics['totalPAYERemitted'] / 1000000, 1) }}M</div>
            <p class="text-[11px] text-slate-500 mt-0.5">KRA Domestic Taxes</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Leave Accrual Liability</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-1.5">{{ $reportMetrics['leaveLiabilityHours'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Accumulated leave days</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Statutory Compliance</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $reportMetrics['complianceRate'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Zero penalty rating</p>
        </div>
    </div>

    {{-- Interactive Report Section with Quick Switch Tabs --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h2 id="reportTitle" class="text-base font-bold text-slate-900">Monthly Payroll Variance Ledger</h2>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" onclick="switchSmhrReport('variance')" class="report-tab px-3 py-1 rounded text-xs font-semibold bg-[#0A3E50] text-white" data-target="variance">Payroll Variance</button>
                    <button type="button" onclick="switchSmhrReport('statutory')" class="report-tab px-3 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="statutory">Statutory Remittances</button>
                    <button type="button" onclick="switchSmhrReport('p9batch')" class="report-tab px-3 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" data-target="p9batch">Staff P9 Forms</button>
                </div>
            </div>

            {{-- Live Search --}}
            <div class="mt-4 relative max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" id="reportSearch" onkeyup="filterReportRows()" placeholder="Search report entries..." class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        {{-- 1. Payroll Variance Table --}}
        <div id="section-variance" class="report-section overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="table-variance">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Pay Period</th>
                        <th class="py-3 px-4 text-center">Headcount</th>
                        <th class="py-3 px-4">Gross Payroll</th>
                        <th class="py-3 px-4">PAYE Deducted</th>
                        <th class="py-3 px-4 text-center">Variance %</th>
                        <th class="py-3 px-4">Variance Driver / Explanatory Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($payrollVarianceReport as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['month'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-900">{{ $row['staff_count'] }}</td>
                            <td class="py-3 px-4 font-mono font-extrabold text-[#0A3E50]">{{ $row['gross'] }}</td>
                            <td class="py-3 px-4 font-mono text-rose-700 font-semibold">{{ $row['paye'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-emerald-700">{{ $row['variance'] }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $row['reason'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 2. Statutory Remittances Table --}}
        <div id="section-statutory" class="report-section overflow-x-auto hidden">
            <table class="w-full text-left border-collapse text-xs" id="table-statutory">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Obligation Name</th>
                        <th class="py-3 px-4">Regulatory Authority</th>
                        <th class="py-3 px-4">Filing Frequency</th>
                        <th class="py-3 px-4">Remittance Amount</th>
                        <th class="py-3 px-4">Filing Reference</th>
                        <th class="py-3 px-4 text-center">Compliance Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($statutorySchedule as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors report-row">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['obligation'] }}</td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $row['authority'] }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $row['frequency'] }}</td>
                            <td class="py-3 px-4 font-mono font-extrabold text-[#1E8449]">{{ $row['amount'] }}</td>
                            <td class="py-3 px-4 font-mono text-blue-900 font-semibold">{{ $row['ref'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 3. Staff P9 Batch Quick Access Table --}}
        <div id="section-p9batch" class="report-section overflow-x-auto hidden">
            <table class="w-full text-left border-collapse text-xs" id="table-p9batch">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Staff ID</th>
                        <th class="py-3 px-4">Employee Full Name</th>
                        <th class="py-3 px-4">KRA PIN</th>
                        <th class="py-3 px-4">Designation &amp; Faculty</th>
                        <th class="py-3 px-4 text-center">Tax Year</th>
                        <th class="py-3 px-4 text-center">P9 Certificate Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50/70 transition-colors report-row">
                        <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">EMP-2026-001</td>
                        <td class="py-3 px-4 font-bold text-slate-900">Prof. Allan Wabwire</td>
                        <td class="py-3 px-4 font-mono text-slate-700">A009876543Z</td>
                        <td class="py-3 px-4 text-slate-700">Dean, Computing &amp; Informatics</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">2025</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('smhr.p9-form', 'EMP-2026-001') }}" class="px-3 py-1 rounded bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs inline-flex items-center gap-1 shadow-2xs" style="color:#ffffff !important;">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-white"></i>
                                <span>Generate P9 Form</span>
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors report-row">
                        <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">EMP-2026-014</td>
                        <td class="py-3 px-4 font-bold text-slate-900">Dr. Mercy Chebet</td>
                        <td class="py-3 px-4 font-mono text-slate-700">A008765432Y</td>
                        <td class="py-3 px-4 text-slate-700">Senior Lecturer, AI</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">2025</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('smhr.p9-form', 'EMP-2026-014') }}" class="px-3 py-1 rounded bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs inline-flex items-center gap-1 shadow-2xs" style="color:#ffffff !important;">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-white"></i>
                                <span>Generate P9 Form</span>
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors report-row">
                        <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">EMP-2026-035</td>
                        <td class="py-3 px-4 font-bold text-slate-900">Prof. Peter Omwenga</td>
                        <td class="py-3 px-4 font-mono text-slate-700">A007654321X</td>
                        <td class="py-3 px-4 text-slate-700">Associate Professor, Electrical Eng.</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">2025</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('smhr.p9-form', 'EMP-2026-035') }}" class="px-3 py-1 rounded bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs inline-flex items-center gap-1 shadow-2xs" style="color:#ffffff !important;">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-white"></i>
                                <span>Generate P9 Form</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let activeSectionKey = 'variance';

    function switchSmhrReport(key) {
        activeSectionKey = key;
        const titles = {
            'variance': 'Monthly Payroll Variance Ledger',
            'statutory': 'Statutory Remittances & Regulatory Schedule',
            'p9batch': 'KRA Form P9A Annual Tax Cards Directory'
        };

        document.getElementById('reportTitle').textContent = titles[key] || 'SMHR Report';

        document.querySelectorAll('.report-section').forEach(sec => sec.classList.add('hidden'));
        document.getElementById('section-' + key)?.classList.remove('hidden');

        document.querySelectorAll('.report-tab').forEach(tab => {
            if (tab.dataset.target === key) {
                tab.className = 'report-tab px-3 py-1 rounded text-xs font-semibold bg-[#0A3E50] text-white';
            } else {
                tab.className = 'report-tab px-3 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200';
            }
        });

        document.getElementById('reportSearch').value = '';
        filterReportRows();
    }

    function filterReportRows() {
        const query = (document.getElementById('reportSearch')?.value || '').toLowerCase().trim();
        const activeTable = document.getElementById('table-' + activeSectionKey);
        if (!activeTable) return;

        const rows = activeTable.querySelectorAll('tbody tr.report-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }

    function exportReportCSV() {
        const activeTable = document.getElementById('table-' + activeSectionKey);
        if (!activeTable) return;

        let csv = [];
        const rows = activeTable.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cols = row.querySelectorAll('th, td');
                const rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
                    rowData.push('"' + text + '"');
                });
                csv.push(rowData.join(','));
            }
        });

        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `smhr_${activeSectionKey}_report_${new Date().toISOString().slice(0,10)}.csv`;
        link.click();
    }
</script>
@endsection
