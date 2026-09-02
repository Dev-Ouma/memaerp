@extends('layouts.app')

@section('title', 'Department Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Department Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage academic departments, faculty leadership, affiliated schools, and programme hosting allocations</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateDeptModal()" class="px-4 py-1.5 rounded-md bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Create Department
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Departments</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalDepartments'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Across all University Schools.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Institutional Framework</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Academic Depts</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['activeAcademicDepts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Offering degree programmes.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Fully Operational</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Service Units</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['serviceDepts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Common university courses.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Cross-Cutting</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Faculty</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['totalAcademicStaff'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Professors, Lecturers, Fellows.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Staff Headcount</span></div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-4">
        <div class="w-full sm:w-64">
            <select id="dept-status-filter" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="w-full sm:w-72">
            <div class="relative">
                <input type="text" id="dept-search-input" placeholder="Search department, HOD, school…" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-2.5"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="dept-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Code & Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Parent School</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Head of Department (HOD)</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programmes / Staff</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="dept-tbody">
                    @forelse($departments as $d)
                        <tr class="hover:bg-slate-50/70 transition-colors dept-row" data-status="{{ strtolower($d->status) }}" data-search="{{ strtolower($d->code.' '.$d->name.' '.$d->school.' '.$d->hod.' '.$d->email) }}">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $d->code }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $d->name }}</div>
                                @if($d->email)
                                    <div class="text-[10.5px] font-mono text-slate-500 mt-0.5">{{ $d->email }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">{{ $d->school ?: '—' }}</td>
                            <td class="py-3.5 px-4 font-bold text-[#0A3E50] text-xs">{{ $d->hod ?: '— Unassigned —' }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                <div><strong class="text-slate-800">{{ $d->programmes_count }}</strong> Programmes</div>
                                <div class="text-slate-500">{{ $d->staff_count }} Faculty Members</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold {{ $d->status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $d->status }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditDeptModal(@json($d))' 
                                            class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors flex items-center gap-1"
                                            title="Edit Department">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i> Edit
                                    </button>
                                    <button type="button" 
                                            onclick="confirmDeleteDept('{{ $d->id }}', '{{ addslashes($d->name) }}')" 
                                            class="p-1 rounded border border-red-200 text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete Department">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No academic departments registered. Click "Create Department" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: CREATE DEPARTMENT --}}
<div class="modal" id="create-dept-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Create Academic Department</h2>
                <small style="color:rgba(255,255,255,0.85);">Register a new department under an affiliated university school.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" method="POST" action="{{ route('curriculum.department.store') }}" data-processing-message="Saving academic department…">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Department Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" placeholder="e.g. DEPT-LAW" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Department of Public Law & Jurisprudence" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Parent School</label>
                    <select name="school" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select Parent School...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->name }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Head of Department (HOD)</label>
                    <input type="text" name="hod" placeholder="e.g. Dr. John Kamau (Senior Lecturer)" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Official Email</label>
                    <input type="email" name="email" placeholder="e.g. hod.law@mema.ac.ke" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="e.g. +254 700 112 005" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programmes Count</label>
                    <input type="number" name="programmes_count" value="0" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Staff Count</label>
                    <input type="number" name="staff_count" value="0" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Academic units and focus areas under this department..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Save Department
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT DEPARTMENT --}}
<div class="modal" id="edit-dept-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Edit Academic Department</h2>
                <small style="color:rgba(255,255,255,0.85);" id="edit-dept-sub">Update department leadership and details.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="edit-dept-form" method="POST" action="" data-processing-message="Updating academic department…">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Department Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-dept-code" name="code" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="edit-dept-status" name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-dept-name" name="name" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Parent School</label>
                    <select id="edit-dept-school" name="school" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select Parent School...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->name }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Head of Department (HOD)</label>
                    <input type="text" id="edit-dept-hod" name="hod" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Official Email</label>
                    <input type="email" id="edit-dept-email" name="email" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Phone Number</label>
                    <input type="text" id="edit-dept-phone" name="phone" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programmes Count</label>
                    <input type="number" id="edit-dept-programmes" name="programmes_count" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Staff Count</label>
                    <input type="number" id="edit-dept-staff" name="staff_count" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description</label>
                    <textarea id="edit-dept-description" name="description" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Update Department
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DELETE CONFIRMATION --}}
<div class="modal" id="delete-dept-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(440px, 94vw);">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Delete Academic Department</h2>
                <small style="color:rgba(255,255,255,0.85);">Move department record to Recycle Bin.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="delete-dept-form" method="POST" action="" data-processing-message="Moving to recycle bin…">
            @csrf
            @method('DELETE')
            <p class="text-xs text-slate-600 mb-2">
                Are you sure you want to delete <strong id="delete-dept-name" class="text-slate-900"></strong>?
            </p>
            <label class="text-xs font-bold text-slate-700 block mt-3 mb-1">Reason for deletion</label>
            <textarea name="deletion_reason" required minlength="10" maxlength="500" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs" placeholder="Explain why this record should be removed..."></textarea>
            <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80">
                <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i> The department will be moved to the <strong>Recycle Bin</strong> where it can be restored within 30 days.
            </p>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-red-600 hover:bg-red-700 text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Move to Recycle Bin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateDeptModal() {
        document.getElementById('create-dept-modal').classList.add('open');
    }

    function openEditDeptModal(dept) {
        document.getElementById('edit-dept-sub').textContent = `${dept.code} • ${dept.name}`;
        document.getElementById('edit-dept-code').value = dept.code || '';
        document.getElementById('edit-dept-name').value = dept.name || '';
        document.getElementById('edit-dept-school').value = dept.school || '';
        document.getElementById('edit-dept-hod').value = dept.hod || '';
        document.getElementById('edit-dept-email').value = dept.email || '';
        document.getElementById('edit-dept-phone').value = dept.phone || '';
        document.getElementById('edit-dept-programmes').value = dept.programmes_count || 0;
        document.getElementById('edit-dept-staff').value = dept.staff_count || 0;
        document.getElementById('edit-dept-description').value = dept.description || '';
        document.getElementById('edit-dept-status').value = dept.status || 'Active';

        document.getElementById('edit-dept-form').action = `/curriculum/department/${dept.id}`;
        document.getElementById('edit-dept-modal').classList.add('open');
    }

    function confirmDeleteDept(id, name) {
        document.getElementById('delete-dept-name').textContent = name;
        document.getElementById('delete-dept-form').action = `/curriculum/department/${id}`;
        document.getElementById('delete-dept-modal').classList.add('open');
    }

    // Instant client filter and search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('dept-search-input');
        const statusSelect = document.getElementById('dept-status-filter');
        const rows = document.querySelectorAll('.dept-row');

        function filterDepts() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const status = (statusSelect?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const rowSearch = row.dataset.search || '';
                const rowStatus = row.dataset.status || '';

                const matchesQuery = !query || rowSearch.includes(query);
                const matchesStatus = !status || rowStatus === status;

                row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterDepts);
        statusSelect?.addEventListener('change', filterDepts);
    });
</script>
@endsection
