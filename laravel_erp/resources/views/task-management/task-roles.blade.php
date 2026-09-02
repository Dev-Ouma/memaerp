@extends('layouts.app')

@section('title', 'Task in Roles')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Role-Based Task Event Bindings</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Link specific operational tasks to user roles based on system trigger events and SLA durations</p>
        </div>
        <button type="button" data-modal-open="task-binding-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Link Task to Role</button>
    </div>

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
