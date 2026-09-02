@extends('layouts.app')

@section('title', 'Budget Proposals')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Trimester Departmental Budget Proposals</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify department budget requests, compile capital and operational expenditure proposals, and track CFO approvals status</p>
        </div>
        @if($canSubmit)<button type="button" data-modal-open="budget-proposal-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Create Budget Proposal</button>@endif
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Proposals</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalProposals'] }} Proposals</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current fiscal trimester block.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Proposals logged</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Approved Amount</div>
            <div class="text-xl font-extrabold text-emerald-700 mt-2.5 mb-2 leading-none">{{ $stats['approvedAmount'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Net signed-off capital.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Allocated Cash</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Requested Amount</div>
            <div class="text-xl font-extrabold text-blue-900 mt-2.5 mb-2 leading-none">{{ $stats['requestedAmount'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Gross proposals value.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Requested Cash</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Variance Deficit</div>
            <div class="text-xl font-extrabold text-red-700 mt-2.5 mb-2 leading-none">{{ $stats['varianceDeficit'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Vetoed reduction savings.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Variance Lock</span></div>
        </div>
    </div>

    {{-- Proposals Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Proposal Ref ID</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Department / Origin School</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Proposal Description Objective</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Requested Value</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Approved Value</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Approval Pipeline Standing</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($proposals as $prop)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-blue-900">{{ $prop->proposal_ref }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 text-xs">{{ $prop->department }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $prop->description }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">KES {{ number_format((float) $prop->requested_amount, 2) }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">KES {{ number_format((float) $prop->approved_amount, 2) }}</td>
                        <td class="py-3.5 px-4 text-purple-900 font-semibold">{{ str_replace('_', ' ', $prop->status) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($prop->status === 'DRAFT' || $prop->status === 'RETURNED')
                                <form method="post" action="{{ route('budgeting.proposals.transition', $prop) }}">@csrf<input type="hidden" name="status" value="SUBMITTED"><input type="hidden" name="lock_version" value="{{ $prop->lock_version }}"><button class="px-3 py-1 rounded border border-orange-400 text-orange-600 font-semibold text-xs">Submit</button></form>
                            @elseif(auth()->user()->isAdmin() && !in_array($prop->status, ['APPROVED', 'REJECTED']))
                                <form method="post" action="{{ route('budgeting.proposals.transition', $prop) }}" class="space-y-1">@csrf<input type="hidden" name="lock_version" value="{{ $prop->lock_version }}"><select name="status" class="text-xs border rounded" required>@foreach(['HOD_APPROVED','DEAN_APPROVED','CFO_APPROVED','APPROVED','RETURNED','REJECTED'] as $status)<option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>@endforeach</select><input name="approved_amount" type="number" min="0" max="{{ $prop->requested_amount }}" step="0.01" placeholder="Approved amount" class="text-xs border rounded w-28"><input name="reason" minlength="10" maxlength="1000" placeholder="Reason if returned/rejected" class="text-xs border rounded w-36"><button class="px-3 py-1 rounded border border-orange-400 text-orange-600 font-semibold text-xs">Process</button></form>
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="7" class="p-8 text-center text-slate-500">No budget proposals have been recorded.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $proposals->links() }}</div>
</div>

@if($canSubmit)<div class="modal" id="budget-proposal-modal"><div class="modal-card"><div class="panel-head"><h2>Create Budget Proposal</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form method="post" action="{{ route('budgeting.proposals.store') }}" class="panel-body">@csrf<div class="form-grid"><div class="field"><label>Fiscal year</label><input name="fiscal_year" type="number" min="2020" max="2200" value="{{ now()->year }}" required></div><div class="field"><label>Trimester</label><select name="trimester" required><option>Trimester I</option><option>Trimester II</option><option>Trimester III</option></select></div><div class="field full"><label>Department</label><input name="department" maxlength="190" value="{{ auth()->user()->department }}" required></div><div class="field full"><label>Objective / description</label><textarea name="description" minlength="10" maxlength="3000" required></textarea></div><div class="field full"><label>Requested amount (KES)</label><input name="requested_amount" type="number" min="1" step="0.01" required></div></div><button class="btn" type="submit" style="margin-top:16px">Create draft proposal</button></form></div></div>@endif
@endsection
