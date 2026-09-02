@extends('layouts.app')

@section('title', 'Claim Approval Permission')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Claim Approval Permission & Multi-Tier Routing</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure sequential approval chains, SLA escalation timelines, delegation privileges, and internal audit verification gates</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                New Approval Matrix
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Claim Vetting</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingClaimVetting'] }} Claims</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">In multi-tier routing queue.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Awaiting Sign-off</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Monthly Approved Volume</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['approvedThisMonth'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Cleared for disbursement.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Finance Cleared</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Processing SLA</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['avgProcessingSLA'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Fast turnaround tracking.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Within Target</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Escalation Triggers</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['escalationRulesActive'] }} Rules</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Auto-escalation upon delay.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Automated</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Workflow Code & Claim Category</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Originating Directorate / Unit</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Sequential Approval Chain</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">SLA Timeout / Delegation</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($approvalMatrices as $mat)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $mat['workflow_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $mat['claim_category'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $mat['originating_unit'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#0A3E50] font-bold">{{ $mat['workflow_sequence'] }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div><strong class="text-slate-900">{{ $mat['auto_escalation_hours'] }} Hours</strong> SLA timeout</div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">Delegate: {{ $mat['delegate_allowed'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $mat['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Configure</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
