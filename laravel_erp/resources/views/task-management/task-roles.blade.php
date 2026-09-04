@extends('layouts.app')

@section('title', 'Task in Roles')

@section('content')
<style>
    .task-mgmt-nav {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 2px;
    }
    .task-mgmt-nav-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        border-radius: 8px 8px 0 0;
        border-bottom: 3px solid transparent;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .task-mgmt-nav-item:hover {
        color: #0A3E50;
        background: #f8fafc;
    }
    .task-mgmt-nav-item.active {
        color: #0A3E50;
        border-bottom-color: #0A3E50;
        background: #f1f5f9;
    }
</style>

<div class="mema-dashboard-container py-2">
    {{-- Top Module Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#0A3E50] mb-0.5">MEMA Identity, Governance & Task Center</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">System Role-Based Task Event Bindings</h1>
            <p class="text-xs text-slate-500 font-medium">Link specific operational tasks to user roles based on system trigger events and SLA durations</p>
        </div>
        <button type="button" data-modal-open="task-binding-modal" class="px-4 py-2 rounded-lg bg-[#0A3E50] text-white hover:bg-[#062631] font-bold text-xs transition-all shadow-xs flex items-center gap-2">
            <i data-lucide="link" class="w-4 h-4"></i> Link Task to Role
        </button>
    </div>

    {{-- Sub-Navigation Tabs --}}
    <nav class="task-mgmt-nav" aria-label="Task Management Sections">
        <a href="{{ route('task-management.users') }}" class="task-mgmt-nav-item">
            <i data-lucide="users" class="w-4 h-4"></i> College Users & Roles
        </a>
        <a href="{{ route('task-management.roles') }}" class="task-mgmt-nav-item">
            <i data-lucide="shield" class="w-4 h-4"></i> Task Roles
        </a>
        <a href="{{ route('task-management.task-roles') }}" class="task-mgmt-nav-item active">
            <i data-lucide="git-merge" class="w-4 h-4 text-[#0A3E50]"></i> Task in Roles Bindings
        </a>
        <a href="{{ route('task-management.task-manager') }}" class="task-mgmt-nav-item">
            <i data-lucide="check-square" class="w-4 h-4"></i> Task Manager & Tickets
        </a>
        <a href="{{ route('admin.setups.access.index') }}" class="task-mgmt-nav-item">
            <i data-lucide="key" class="w-4 h-4"></i> Platform Access Control
        </a>
    </nav>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Mapping Ref</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Role Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Task Template</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">System Trigger Event</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">SLA Duration</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($taskRoles as $tr)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $tr->mapping_ref }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $tr->role?->name }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-xs">{{ $tr->task_template }}</td>
                        <td class="py-3 px-4 text-purple-900 font-semibold">{{ $tr->trigger_event }}</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold text-xs">{{ $tr->sla_hours }} Hours</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $tr->is_active ? 'Active Binding' : 'Inactive' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-slate-400">Persisted</span>
                        </td>
                    </tr>
                @empty<tr><td colspan="7" class="p-8 text-center text-slate-500">No role task bindings configured.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="task-binding-modal"><div class="modal-card"><div class="panel-head"><h2>Link Task to Role</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form method="post" action="{{ route('task-management.task-roles.store') }}" class="panel-body">@csrf<div class="form-grid"><div class="field full"><label>Role</label><select name="task_management_role_id" required>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div><div class="field"><label>Mapping reference</label><input name="mapping_ref" pattern="[A-Z0-9_-]+" required></div><div class="field"><label>SLA hours</label><input name="sla_hours" type="number" min="1" max="8760" required></div><div class="field full"><label>Task template</label><input name="task_template" required></div><div class="field full"><label>Trigger event</label><input name="trigger_event" required></div></div><button class="btn" style="margin-top:16px">Create binding</button></form></div></div>
@endsection
