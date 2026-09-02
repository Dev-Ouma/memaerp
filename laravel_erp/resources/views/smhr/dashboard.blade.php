@extends('layouts.app')

@section('title', 'SMHR - Staff Management & HR Dashboard')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">SMHR — Staff &amp; Human Resources</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Strategic staff lifecycle management, faculty workload allocation, leave management, appraisals, and payroll registry</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('smhr.staff-directory') }}" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Staff Directory</span>
            </a>
            <a href="{{ route('smhr.leave-management') }}" class="px-3.5 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Leave Requests</span>
            </a>
            <a href="{{ route('smhr.payroll-register') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="wallet" class="w-3.5 h-3.5 text-slate-600"></i>
                <span>Payroll Ledger</span>
            </a>
        </div>
    </div>

    {{-- 4 Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total University Staff</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format($metrics['totalStaff']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">{{ $metrics['teachingFaculty'] }} Faculty &middot; {{ $metrics['administrativeStaff'] }} Administrative.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">{{ $metrics['retentionRate'] }} Staff Retention</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Staff on Approved Leave</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $metrics['onLeave'] }} Staff</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active leave &amp; sabbatical coverage.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">{{ $metrics['onLeave'] }} active leave records</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Monthly Gross Payroll</div>
            <div class="text-3xl font-extrabold text-[#1E8449] mt-2 mb-1.5 leading-none">KES {{ number_format($metrics['monthlyPayrollGross'] / 1000000, 1) }}M</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Persisted payroll records.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Database total</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Annual Appraisals</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $metrics['pendingAppraisals'] }} Pending</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Persisted KPI reviews underway.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Database total</span></div>
        </div>
    </div>

    {{-- 6 Quick Submodule Action Tiles --}}
    <div class="mb-8">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">SMHR Operational Workspaces</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Tile 1: Staff Directory --}}
            <a href="{{ route('smhr.staff-directory') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-[#0A3E50] hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-[#0A3E50] uppercase group-hover:text-teal-700 transition-colors">STAFF DIRECTORY &amp; PROFILES</span>
                        <i data-lucide="users" class="w-4 h-4 text-slate-400 group-hover:text-[#0A3E50]"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Central personnel ledger, designations, contracts, rank classifications, and academic qualifications.</p>
                </div>
                <div class="text-xs font-bold text-[#0A3E50] inline-flex items-center gap-1">
                    <span>Manage Staff</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            {{-- Tile 2: Leave Management --}}
            <a href="{{ route('smhr.leave-management') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-[#1E8449] hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-emerald-700 uppercase group-hover:text-emerald-800 transition-colors">LEAVE &amp; TIME-OFF WORKFLOW</span>
                        <i data-lucide="calendar-check" class="w-4 h-4 text-slate-400 group-hover:text-emerald-700"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Submit and approve annual, sabbatical, study, and compassionate leave requests with balance tracking.</p>
                </div>
                <div class="text-xs font-bold text-emerald-700 inline-flex items-center gap-1">
                    <span>Review Leaves</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            {{-- Tile 3: Workload Allocation --}}
            <a href="{{ route('smhr.workload-allocation') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-blue-600 hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-blue-600 uppercase group-hover:text-blue-700 transition-colors">TEACHING WORKLOAD ALLOCATION</span>
                        <i data-lucide="book-open" class="w-4 h-4 text-slate-400 group-hover:text-blue-600"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Assign lecture course units, monitor weekly contact hours, and ensure compliance with senate guidelines.</p>
                </div>
                <div class="text-xs font-bold text-blue-600 inline-flex items-center gap-1">
                    <span>Allocate Workload</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            {{-- Tile 4: Performance Appraisals --}}
            <a href="{{ route('smhr.performance-appraisals') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-amber-600 hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-amber-600 uppercase group-hover:text-amber-700 transition-colors">PERFORMANCE APPRAISALS &amp; KPIS</span>
                        <i data-lucide="award" class="w-4 h-4 text-slate-400 group-hover:text-amber-600"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Annual academic reviews, research output metrics, peer evaluations, and dean score sign-offs.</p>
                </div>
                <div class="text-xs font-bold text-amber-600 inline-flex items-center gap-1">
                    <span>View Appraisals</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            {{-- Tile 5: Payroll Register --}}
            <a href="{{ route('smhr.payroll-register') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-purple-600 hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-purple-700 uppercase group-hover:text-purple-800 transition-colors">PAYROLL &amp; STATUTORY LEDGER</span>
                        <i data-lucide="wallet" class="w-4 h-4 text-slate-400 group-hover:text-purple-700"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Monthly salary batches, allowances, statutory deductions (PAYE, NHIF/SHA, NSSF), and individual payslips.</p>
                </div>
                <div class="text-xs font-bold text-purple-700 inline-flex items-center gap-1">
                    <span>Manage Payroll</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            {{-- Tile 6: Disciplinary & Governance --}}
            <a href="{{ route('smhr.disciplinary-records') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-slate-600 hover:shadow-sm transition-all flex flex-col justify-between group text-decoration-none">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold tracking-wide text-slate-700 uppercase group-hover:text-slate-900 transition-colors">DISCIPLINARY &amp; HR GOVERNANCE</span>
                        <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 group-hover:text-slate-700"></i>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">Official commendations, formal notices, disciplinary committee hearings, and grievance records.</p>
                </div>
                <div class="text-xs font-bold text-slate-700 inline-flex items-center gap-1">
                    <span>View Records</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- Two Column Split: Department Staffing Distribution & Pending Leave Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Department Staffing Distribution --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wider">Faculty &amp; Departmental Staffing</h3>
                <span class="text-xs font-semibold text-slate-500">{{ number_format($metrics['totalStaff']) }} Total Staff</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3">Academic Department</th>
                            <th class="py-2.5 px-3 text-center">Faculty</th>
                            <th class="py-2.5 px-3 text-center">Admin</th>
                            <th class="py-2.5 px-3 text-right">Monthly Budget</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($departmentStats as $dept)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $dept['dept'] }}</td>
                                <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-700">{{ $dept['teaching'] }}</td>
                                <td class="py-2.5 px-3 text-center font-mono text-slate-600">{{ $dept['admin'] }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-[#1E8449]">{{ $dept['budget'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending Leave Approvals Queue --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Pending Leave Approvals Queue</h3>
                    <a href="{{ route('smhr.leave-management') }}" class="text-xs font-bold text-[#0A3E50] hover:underline">View All &rarr;</a>
                </div>
                <div class="space-y-3">
                    @foreach($pendingLeaves as $lv)
                        <div class="p-3 rounded-lg border border-slate-200 hover:border-[#1E8449] transition-all bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <div class="font-bold text-slate-900 text-xs">{{ $lv['name'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $lv['type'] }} &middot; <strong>{{ $lv['days'] }} Days</strong> ({{ $lv['from'] }} to {{ $lv['to'] }})</div>
                                <div class="text-[10.5px] text-amber-700 font-semibold mt-0.5">{{ $lv['status'] }}</div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('smhr.leave-management.approve', $lv['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded bg-[#1E8449] hover:bg-[#166534] font-bold text-[11px] text-white transition-colors" style="color:#ffffff !important;">Approve</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between items-center">
                <span>Statutory Leave Policy compliant</span>
                <span class="font-bold text-[#0A3E50]">Kenya Employment Act 2007</span>
            </div>
        </div>
    </div>
</div>
@endsection
