@extends('layouts.app')

@section('title', 'College Users & Role Management')

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
    .kpi-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .kpi-stat-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07);
    }
    .btn-mema-primary {
        background-color: #0A3E50 !important;
        color: #ffffff !important;
        font-weight: 700;
        padding: 9px 18px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(10, 62, 80, 0.2);
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .btn-mema-primary:hover {
        background-color: #062631 !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }
    .btn-mema-secondary {
        background-color: #1E8449 !important;
        color: #ffffff !important;
        font-weight: 700;
        padding: 9px 18px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(30, 132, 73, 0.2);
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .btn-mema-secondary:hover {
        background-color: #145a32 !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }
    .badge-role-admin { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-role-staff { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-role-student { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-role-parent { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .badge-role-applicant { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
</style>

<div class="mema-dashboard-container py-2">

    {{-- Top Module Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#0A3E50] mb-0.5">MEMA Identity, Governance & Task Center</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">College Users & Task Management</h1>
            <p class="text-xs text-slate-500 font-medium">Provision institutional accounts, manage stakeholder lifecycles, and enforce granular RBAC authority</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" data-modal-open="college-user-modal" class="btn-mema-primary">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Add College User
            </button>
            <button type="button" data-modal-open="assign-role-modal" class="btn-mema-secondary">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Assign RBAC Role
            </button>
        </div>
    </div>

    {{-- Sub-Navigation Tabs --}}
    <nav class="task-mgmt-nav" aria-label="Task Management Sections">
        <a href="{{ route('task-management.users') }}" class="task-mgmt-nav-item active">
            <i data-lucide="users" class="w-4 h-4 text-[#0A3E50]"></i> College Users & Roles
        </a>
        <a href="{{ route('task-management.roles') }}" class="task-mgmt-nav-item">
            <i data-lucide="shield" class="w-4 h-4"></i> Task Roles
        </a>
        <a href="{{ route('task-management.task-roles') }}" class="task-mgmt-nav-item">
            <i data-lucide="git-merge" class="w-4 h-4"></i> Task in Roles Bindings
        </a>
        <a href="{{ route('task-management.task-manager') }}" class="task-mgmt-nav-item">
            <i data-lucide="check-square" class="w-4 h-4"></i> Task Manager & Tickets
        </a>
        @can('platform.role.manage')
        <a href="{{ route('admin.setups.access.index') }}" class="task-mgmt-nav-item">
            <i data-lucide="key" class="w-4 h-4"></i> Platform Access Control
        </a>
        @endcan
    </nav>

    {{-- Executive Metric Cards (4 KPI Strip) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="kpi-stat-card">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total College Users</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-0.5">College admin and staff accounts</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="kpi-stat-card">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Accounts</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($stats['active']) }}</div>
                <div class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100, 1) : 0 }}% operational rate</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="kpi-stat-card">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff & Faculty</div>
                <div class="text-2xl font-black text-[#0A3E50] mt-1">{{ number_format($stats['staff']) }}</div>
                <div class="text-[11px] text-[#0A3E50] font-medium mt-0.5">Administrative & Teaching staff</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#0A3E50]">
                <i data-lucide="briefcase" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="kpi-stat-card">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Role Grants</div>
                <div class="text-2xl font-black text-purple-700 mt-1">{{ number_format($stats['roleAssignments']) }}</div>
                <div class="text-[11px] text-purple-600 font-medium mt-0.5">RBAC Verified Policies</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    {{-- Main User Directory Panel --}}
    <section class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-6">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900">College Users</h2>
                <p class="text-xs text-slate-500 font-medium">Search, filter, update details, or assign scoped roles to campus stakeholders</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-full shadow-2xs">
                Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} Users
            </span>
        </div>

        {{-- Filter & Search Form --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-white">
            <form method="get" action="{{ route('task-management.users') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-5">
                    <label for="q" class="block text-xs font-bold text-slate-700 mb-1">Search Directory</label>
                    <div class="relative">
                        <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email, department or phone…" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#0A3E50] focus:border-transparent outline-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="account_type" class="block text-xs font-bold text-slate-700 mb-1">Account Role</label>
                    <select id="account_type" name="account_type" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#0A3E50] outline-none">
                        <option value="">All Account Roles</option>
                        @foreach(['admin','staff'] as $type)
                            <option value="{{ $type }}" @selected(($filters['account_type'] ?? '') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="status" class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#0A3E50] outline-none">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex gap-2">
                    <button class="flex-1 py-2 px-3 rounded-lg bg-[#0A3E50] text-white font-bold text-xs hover:bg-[#062631] transition-colors shadow-2xs" type="submit">Filter</button>
                    <a class="py-2 px-3 rounded-lg border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-colors text-center" href="{{ route('task-management.users') }}">Reset</a>
                </div>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">User Details</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Account Role</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">College Placement</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Assigned RBAC Roles</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-36 uppercase text-[11px]" style="color:#ffffff !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($users as $user)
                        @php
                            $roleClass = match($user->role) {
                                'admin' => 'badge-role-admin',
                                'staff' => 'badge-role-staff',
                                'student' => 'badge-role-student',
                                'parent' => 'badge-role-parent',
                                default => 'badge-role-applicant',
                            };
                            $initials = collect(explode(' ', $user->name))->map(fn($part) => substr($part, 0, 1))->take(2)->join('');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#0A3E50]/10 text-[#0A3E50] font-bold text-xs flex items-center justify-center border border-[#0A3E50]/20 shrink-0">
                                        {{ strtoupper($initials ?: 'U') }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $user->email }}</div>
                                        @if($user->phone_number)
                                            <div class="text-[10.5px] text-slate-400 font-mono">{{ $user->phone_number }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $roleClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $user->title ?: '—' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $user->department ?: 'General Administration' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->rbacAssignments->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->rbacAssignments as $assignment)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10.5px] font-semibold border border-slate-200">
                                                <i data-lucide="shield" class="w-3 h-3 text-[#0A3E50]"></i>
                                                {{ $assignment->role?->name ?? 'Role' }}
                                                <small class="text-slate-400">({{ $assignment->scope_type }})</small>
                                                @can('admin')
                                                    <form method="post" action="{{ route('task-management.users.revoke-role', $assignment) }}" class="inline" onsubmit="return confirm('Revoke this role assignment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 ml-0.5 font-bold" title="Revoke Role">&times;</button>
                                                    </form>
                                                @endcan
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">No extra RBAC roles</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Edit Trigger --}}
                                    <button type="button" 
                                            onclick="openEditModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'title' => $user->title, 'phone_number' => $user->phone_number, 'department' => $user->department, 'is_active' => $user->is_active ? 1 : 0]) }})"
                                            class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                                            title="Edit user details">
                                        Edit
                                    </button>

                                    {{-- Toggle Status --}}
                                    @if(auth()->id() !== $user->id)
                                        <form method="post" action="{{ route('task-management.users.toggle-status', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('Change status for {{ $user->name }}?');"
                                                    class="px-2 py-1 rounded border {{ $user->is_active ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }} font-semibold text-xs transition-colors"
                                                    title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}">
                                                {{ $user->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                No college users match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </section>

    {{-- Role Administration Overview Section --}}
    <section class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900">Role administration</h2>
                <p class="text-xs text-slate-500 font-medium">Canonical RBAC permissions and default institutional scope specifications</p>
            </div>
            @can('platform.role.manage')
            <a class="px-3 py-1.5 rounded-md border border-[#0A3E50] text-[#0A3E50] hover:bg-[#0A3E50]/5 font-bold text-xs transition-colors" href="{{ route('admin.setups.access.index') }}">
                Manage Full Access Policies &rarr;
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-2.5 px-4">Role Name & Code</th>
                        <th class="py-2.5 px-4">Default Scope Level</th>
                        <th class="py-2.5 px-4">Policy Description</th>
                        <th class="py-2.5 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($roles as $role)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-4">
                                <strong class="text-slate-900">{{ $role->name }}</strong>
                                <span class="block font-mono text-[10.5px] text-slate-500">{{ $role->code }}</span>
                            </td>
                            <td class="py-2.5 px-4 font-semibold text-slate-700 uppercase text-[11px]">{{ $role->default_scope_type }}</td>
                            <td class="py-2.5 px-4 text-slate-600">{{ $role->description }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <button type="button" onclick="prefillRoleModal('{{ $role->id }}')" class="px-2.5 py-1 rounded bg-[#0A3E50]/10 text-[#0A3E50] font-bold text-xs hover:bg-[#0A3E50]/20">
                                    Assign
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

{{-- MODAL 1: Add College User --}}
<div class="modal" id="college-user-modal">
    <div class="modal-card max-w-lg">
        <div class="panel-head flex justify-between items-center border-b border-slate-200 pb-3">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5 text-[#0A3E50]"></i> Add College User
            </h2>
            <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
        </div>
        <form method="post" action="{{ route('task-management.users.store') }}" class="panel-body pt-4">
            @csrf
            <div class="form-grid grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">First name</label>
                    <input name="first_name" value="{{ old('first_name') }}" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Last name</label>
                    <input name="last_name" value="{{ old('last_name') }}" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                </div>
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">College email address</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone number</label>
                    <input name="phone_number" value="{{ old('phone_number') }}" class="w-full text-xs p-2 border border-slate-300 rounded" placeholder="+254700000000">
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Role</label>
                    <select name="account_type" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                        @foreach(['staff','admin'] as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Title / Designation</label>
                    <input name="title" value="{{ old('title') }}" class="w-full text-xs p-2 border border-slate-300 rounded" placeholder="e.g. Lecturer, Registrar">
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Department</label>
                    <input name="department" value="{{ old('department') }}" class="w-full text-xs p-2 border border-slate-300 rounded" placeholder="e.g. Computer Science">
                </div>
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Platform role (optional)</label>
                    <select name="role_id" class="w-full text-xs p-2 border border-slate-300 rounded">
                        <option value="">Assign later</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" @selected((string) old('role_id') === (string) $r->id)>{{ $r->name }} ({{ $r->code }})</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="scope_type" value="institution">
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Grant reason</label>
                    <input name="grant_reason" value="{{ old('grant_reason') }}" class="w-full text-xs p-2 border border-slate-300 rounded" minlength="10" placeholder="Required when a platform role is selected">
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-3">A secure temporary activation link is queued to the specified email address; permanent passwords are not displayed in cleartext.</p>
            <button class="btn-mema-primary w-full justify-center mt-4" type="submit">Create User Account</button>
        </form>
    </div>
</div>

{{-- MODAL 2: Assign RBAC Role --}}
<div class="modal" id="assign-role-modal">
    <div class="modal-card max-w-lg">
        <div class="panel-head flex justify-between items-center border-b border-slate-200 pb-3">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-[#1E8449]"></i> Assign RBAC Role to User
            </h2>
            <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
        </div>
        <form method="post" action="{{ route('task-management.users.assign-role') }}" class="panel-body pt-4">
            @csrf
            <div class="form-grid grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select College User</label>
                    <select id="modal_assign_user_id" name="user_id" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                        @foreach($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - {{ ucfirst($u->role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">RBAC Role Authority</label>
                    <select id="modal_assign_role_id" name="role_id" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Scope Level</label>
                    <select name="scope_type" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                        @foreach($scopeTypes as $scope)
                            <option value="{{ $scope }}" @selected($scope === 'institution')>{{ ucfirst($scope) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Scope ID (if applicable)</label>
                    <input name="scope_id" class="w-full text-xs p-2 border border-slate-300 rounded" placeholder="e.g. DEPT-CS">
                </div>
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Grant Justification Reason</label>
                    <input name="grant_reason" class="w-full text-xs p-2 border border-slate-300 rounded" placeholder="e.g. Appointed as Academic Board Reviewer" required minlength="10">
                </div>
                <div class="field full sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Expiry Target Date (Optional)</label>
                    <input name="expires_at" type="datetime-local" class="w-full text-xs p-2 border border-slate-300 rounded">
                </div>
            </div>
            <button class="btn-mema-secondary w-full justify-center mt-4" type="submit">Grant Role Authority</button>
        </form>
    </div>
</div>

{{-- MODAL 3: Edit User --}}
<div class="modal" id="edit-user-modal">
    <div class="modal-card max-w-md">
        <div class="panel-head flex justify-between items-center border-b border-slate-200 pb-3">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-[#0A3E50]"></i> Edit User Profile
            </h2>
            <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
        </div>
        <form id="edit-user-form" method="post" action="" class="panel-body pt-4">
            @csrf
            @method('PATCH')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                    <input id="edit_name" name="name" class="w-full text-xs p-2 border border-slate-300 rounded" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Title / Designation</label>
                    <input id="edit_title" name="title" class="w-full text-xs p-2 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone</label>
                    <input id="edit_phone" name="phone_number" class="w-full text-xs p-2 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Department</label>
                    <input id="edit_department" name="department" class="w-full text-xs p-2 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Status</label>
                    <select id="edit_is_active" name="is_active" class="w-full text-xs p-2 border border-slate-300 rounded">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <button class="btn-mema-primary w-full justify-center mt-4" type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_title').value = user.title || '';
        document.getElementById('edit_phone').value = user.phone_number || '';
        document.getElementById('edit_department').value = user.department || '';
        document.getElementById('edit_is_active').value = user.is_active ? "1" : "0";
        document.getElementById('edit-user-form').action = @json(url('/task-management/college-users')) + '/' + user.id;
        
        const modal = document.getElementById('edit-user-modal');
        if (modal) {
            modal.classList.add('open');
        }
    }

    function prefillRoleModal(roleId) {
        const select = document.getElementById('modal_assign_role_id');
        if (select) {
            select.value = roleId;
        }
        const modal = document.getElementById('assign-role-modal');
        if (modal) {
            modal.classList.add('open');
        }
    }
</script>
@endsection

