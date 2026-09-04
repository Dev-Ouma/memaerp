@extends('layouts.app')

@section('title', 'Task Manager')

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
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Institutional Task & Workflow Manager</h1>
            <p class="text-xs text-slate-500 font-medium">Create, delegate, and track administrative task tickets, follow up on overdue queues, and audit SLA compliances</p>
        </div>
        @can('admin')
            <button type="button" data-modal-open="institutional-task-modal" class="px-4 py-2 rounded-lg bg-[#0A3E50] text-white hover:bg-[#062631] font-bold text-xs transition-all shadow-xs flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Task Ticket
            </button>
        @endcan
    </div>

    {{-- Sub-Navigation Tabs --}}
    <nav class="task-mgmt-nav" aria-label="Task Management Sections">
        <a href="{{ route('task-management.users') }}" class="task-mgmt-nav-item">
            <i data-lucide="users" class="w-4 h-4"></i> College Users & Roles
        </a>
        <a href="{{ route('task-management.roles') }}" class="task-mgmt-nav-item">
            <i data-lucide="shield" class="w-4 h-4"></i> Task Roles
        </a>
        <a href="{{ route('task-management.task-roles') }}" class="task-mgmt-nav-item">
            <i data-lucide="git-merge" class="w-4 h-4"></i> Task in Roles Bindings
        </a>
        <a href="{{ route('task-management.task-manager') }}" class="task-mgmt-nav-item active">
            <i data-lucide="check-square" class="w-4 h-4 text-[#0A3E50]"></i> Task Manager & Tickets
        </a>
        <a href="{{ route('admin.setups.access.index') }}" class="task-mgmt-nav-item">
            <i data-lucide="key" class="w-4 h-4"></i> Platform Access Control
        </a>
    </nav>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Task ID Reference</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Task Title & Objective</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Assigned Department Officer</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Priority Rank</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Due Target Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">SLA Resolution Progress</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($tasks as $t)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $t->task_ref }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $t->title }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-xs">{{ $t->assignee?->name ?? 'Unassigned' }}</td>
                        <td class="py-3 px-4">
                            @if(in_array($t->priority, ['CRITICAL', 'HIGH']))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $t->priority }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $t->priority }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600 font-semibold">{{ $t->due_at->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-purple-900 font-semibold text-xs">{{ $t->status === 'COMPLETED' ? 'Completed' : ($t->due_at->isPast() ? 'Overdue by '.$t->due_at->diffForHumans(null, true) : 'Due '.$t->due_at->diffForHumans()) }}</td>
                        <td class="py-3 px-4">
                            @if($t->due_at->isPast() && !in_array($t->status, ['COMPLETED','CANCELLED']))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">OVERDUE</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $t->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if(!in_array($t->status, ['COMPLETED','CANCELLED']))<form method="post" action="{{ route('task-management.tasks.transition', $t) }}">@csrf<input type="hidden" name="lock_version" value="{{ $t->lock_version }}"><select name="status" class="text-xs border rounded"><option value="IN_PROGRESS">In progress</option><option value="BLOCKED">Blocked</option><option value="COMPLETED">Completed</option><option value="CANCELLED">Cancelled</option></select><input name="note" maxlength="2000" class="text-xs border rounded w-28" placeholder="Update note"><button class="px-3 py-1 rounded border border-orange-400 text-orange-600 font-semibold text-xs">Process</button></form>@endif
                        </td>
                    </tr>
                @empty<tr><td colspan="8" class="p-8 text-center text-slate-500">No tasks assigned.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@can('admin')<div class="modal" id="institutional-task-modal"><div class="modal-card"><div class="panel-head"><h2>Create Task Ticket</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form method="post" action="{{ route('task-management.tasks.store') }}" class="panel-body">@csrf<div class="form-grid"><div class="field full"><label>Title</label><input name="title" minlength="5" required></div><div class="field full"><label>Description</label><textarea name="description" maxlength="5000"></textarea></div><div class="field"><label>Assignee</label><select name="assignee_user_id" required>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div><div class="field"><label>Priority</label><select name="priority"><option>LOW</option><option selected>MEDIUM</option><option>HIGH</option><option>CRITICAL</option></select></div><div class="field full"><label>Due at</label><input name="due_at" type="datetime-local" required></div></div><button class="btn" style="margin-top:16px">Create task</button></form></div></div>@endcan
@endsection
