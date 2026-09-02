@extends('layouts.app')

@section('title', 'Budget Permissions')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Budget Proposal & Allocation Permissions</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify department roles authorized to submit budget requests, adjust trimester allocations, and edit financial pipelines</p>
        </div>
        <button type="button" data-modal-open="budget-submitter-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Proposal Submitter</button>
    </div>

    {{-- Rules Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Budget Approval Tier Chain</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#0A3E50] text-white uppercase mb-2">Approval Tier Level</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['approvalTierLevel'] }}</h3>
                <p class="text-xs text-slate-500">Every departmental proposal triggers notifications sequentially starting from the department head to the Vice Chancellor for final signature locking.</p>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase mb-2">Status Audit Trail</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['status'] }}</h3>
                <p class="text-xs text-slate-500">Submissions, revisions, and veto approvals logs are cryptographically locked for trimester fiscal accountability checks.</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs mt-6">
        <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-[#0A3E50] text-white"><th class="p-3 text-white">Staff member</th><th class="p-3 text-white">Department</th><th class="p-3 text-white">Granted</th><th class="p-3 text-white">Status</th><th class="p-3 text-white">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($submitters as $submitter)
                <tr><td class="p-3 font-bold">{{ $submitter->user?->name }}</td><td class="p-3">{{ $submitter->department }}</td><td class="p-3">{{ $submitter->granted_at?->format('d M Y H:i') }}</td><td class="p-3">{{ $submitter->is_active ? 'Active' : 'Revoked' }}</td><td class="p-3">@if($submitter->is_active)<form method="post" action="{{ route('budgeting.permissions.destroy', $submitter) }}">@csrf @method('DELETE')<button class="text-red-700 font-bold" type="submit">Revoke</button></form>@endif</td></tr>
            @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-500">No proposal submitters have been authorized.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="budget-submitter-modal"><div class="modal-card"><div class="panel-head"><h2>Add Proposal Submitter</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form method="post" action="{{ route('budgeting.permissions.store') }}" class="panel-body">@csrf<div class="form-grid"><div class="field full"><label>Staff member</label><select name="user_id" required><option value="">Select staff member</option>@foreach($eligibleUsers as $user)<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>@endforeach</select></div><div class="field full"><label>Department</label><input name="department" maxlength="190" required></div></div><button class="btn" type="submit" style="margin-top:16px">Grant submitter access</button></form></div></div>
@endsection
