@extends('layouts.app')

@section('title', 'Role Config')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Administrative Role Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure roles within the MEMA ERP platform, assign permission matrices, and track staff members enrollment count</p>
        </div>
        <button type="button" data-modal-open="task-role-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Create Role</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Role Code</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Role Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Department Location</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Assigned Staff Members</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Privilege Matrix Level</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($roles as $r)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $r->role_code }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $r->name }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-xs">{{ $r->department }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $r->bindings_count }} task bindings</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold text-xs">{{ $r->privilege_level }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $r->is_active ? 'Active Role' : 'Inactive Role' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('task-management.task-roles') }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Bindings</a>
                        </td>
                    </tr>
                @empty<tr><td colspan="7" class="p-8 text-center text-slate-500">No task roles configured.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="task-role-modal"><div class="modal-card"><div class="panel-head"><h2>Create Role</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form method="post" action="{{ route('task-management.roles.store') }}" class="panel-body">@csrf<div class="form-grid"><div class="field"><label>Role code</label><input name="role_code" pattern="[A-Z0-9_-]+" required></div><div class="field"><label>Name</label><input name="name" required></div><div class="field full"><label>Department</label><input name="department" required></div><div class="field full"><label>Privilege level</label><input name="privilege_level" required></div></div><button class="btn" style="margin-top:16px">Create role</button></form></div></div>
@endsection
