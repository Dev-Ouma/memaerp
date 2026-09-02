@extends('layouts.app')

@section('title', 'School Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic School & Faculty Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage University Schools, Faculty Executive Deans, constituent academic departments, and official administrative dockets</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateSchoolModal()" class="px-4 py-1.5 rounded-md bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Create School
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2 shadow-2xs">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold space-y-1 shadow-2xs">
            <div class="flex items-center gap-2 font-bold">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-[11px] pl-6 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">University Schools</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none" id="kpi-total-schools">{{ $stats['totalSchools'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Constituent academic faculties.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">MEMA University Structure</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Deanship Leadership</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['deanPositionsFilled'] }} / {{ $stats['totalSchools'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Executive Dean dockets filled.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">{{ round(($stats['totalSchools'] > 0 ? ($stats['deanPositionsFilled'] / $stats['totalSchools']) * 100 : 100)) }}% Appointed</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Departments</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['totalDepartments'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Across {{ $stats['totalProgrammes'] }} active degree programmes.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Active Catalogue</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Delivery Model</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">Virtual Campus</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Open, Distance & e-Learning.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">ODeL Standard</span></div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-4">
        <div class="w-full sm:w-64">
            <select id="school-status-filter" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="w-full sm:w-72">
            <div class="relative">
                <input type="text" id="school-search-input" placeholder="Search by code, title, dean or email..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-2.5"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="schools-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">School Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Executive Dean</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Departments / Programmes</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Official Contacts & Location</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="schools-tbody">
                    @forelse($schools as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors school-row" data-status="{{ strtolower($s->status) }}" data-search="{{ strtolower($s->code.' '.$s->name.' '.$s->dean.' '.$s->email.' '.$s->building) }}">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $s->code }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $s->name }}</div>
                                @if($s->description)
                                    <div class="text-[10.5px] text-slate-500 mt-0.5 line-clamp-1">{{ $s->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-[#0A3E50] text-xs">
                                {{ $s->dean ?: '— Unassigned —' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                <div><strong class="text-slate-900">{{ $s->departments_count }}</strong> Departments</div>
                                <div class="text-slate-500">{{ $s->programmes_count }} Programmes</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div class="font-mono text-xs text-blue-900 font-semibold">{{ $s->email ?: '—' }}</div>
                                @if($s->phone)
                                    <div class="text-[10.5px] text-slate-500 mt-0.5">{{ $s->phone }}</div>
                                @endif
                                @if($s->building)
                                    <div class="text-[10.5px] text-slate-400 mt-0.5">{{ $s->building }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold {{ $s->status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $s->status }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditSchoolModal(@json($s))' 
                                            class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors flex items-center gap-1"
                                            title="Edit School">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i> Edit
                                    </button>
                                    <button type="button" 
                                            onclick="confirmDeleteSchool('{{ $s->id }}', '{{ addslashes($s->name) }}')" 
                                            class="p-1 rounded border border-red-200 text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete School">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No schools registered yet. Click "Create School" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: CREATE SCHOOL --}}
<div class="modal" id="create-school-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Create Academic School</h2>
                <small style="color:rgba(255,255,255,0.85);">Add a new constituent academic faculty / school to MEMA ERP.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" method="POST" action="{{ route('curriculum.school.store') }}" data-processing-message="Registering academic school…">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">School Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" placeholder="e.g. SCH-ENG" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">School Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="e.g. School of Engineering and Built Environment" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Executive Dean</label>
                    <input type="text" name="dean" placeholder="e.g. Prof. Alice Muthoni (Dean)" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Official Email</label>
                    <input type="email" name="email" placeholder="e.g. dean.engineering@mema.ac.ke" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="e.g. +254 700 889 900" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Departments Count</label>
                    <input type="number" name="departments_count" value="0" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programmes Count</label>
                    <input type="number" name="programmes_count" value="0" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Campus Building / Location</label>
                    <input type="text" name="building" placeholder="e.g. Technology Complex, Block D" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description / Academic Focus</label>
                    <textarea name="description" rows="2" placeholder="Summary of academic disciplines under this school..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Save School
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT SCHOOL --}}
<div class="modal" id="edit-school-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Edit Academic School</h2>
                <small style="color:rgba(255,255,255,0.85);" id="edit-modal-sub">Update faculty information and deanship leadership.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="edit-school-form" method="POST" action="" data-processing-message="Updating academic school…">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">School Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-code" name="code" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="edit-status" name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">School Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-name" name="name" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Executive Dean</label>
                    <input type="text" id="edit-dean" name="dean" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Official Email</label>
                    <input type="email" id="edit-email" name="email" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Phone Number</label>
                    <input type="text" id="edit-phone" name="phone" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Departments Count</label>
                    <input type="number" id="edit-departments" name="departments_count" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programmes Count</label>
                    <input type="number" id="edit-programmes" name="programmes_count" min="0" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Campus Building / Location</label>
                    <input type="text" id="edit-building" name="building" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description / Academic Focus</label>
                    <textarea id="edit-description" name="description" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Update School
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DELETE CONFIRMATION --}}
<div class="modal" id="delete-school-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(440px, 94vw);">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Delete Academic School</h2>
                <small style="color:rgba(255,255,255,0.85);">Confirm permanent removal of school record.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="delete-school-form" method="POST" action="" data-processing-message="Deleting school…">
            @csrf
            @method('DELETE')
            <p class="text-xs text-slate-600 mb-2">
                Are you sure you want to remove <strong id="delete-school-name" class="text-slate-900"></strong> from the university catalogue?
            </p>
            <label class="text-xs font-bold text-slate-700 block mt-3 mb-1">Reason for deletion</label>
            <textarea name="deletion_reason" required minlength="10" maxlength="500" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs" placeholder="Explain why this record should be removed..."></textarea>
            <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i> Note: Associated departments and degree programmes should be re-mapped before deleting.
            </p>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-red-600 hover:bg-red-700 text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete School
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateSchoolModal() {
        document.getElementById('create-school-modal').classList.add('open');
    }

    function openEditSchoolModal(school) {
        document.getElementById('edit-modal-sub').textContent = `${school.code} • ${school.name}`;
        document.getElementById('edit-code').value = school.code || '';
        document.getElementById('edit-name').value = school.name || '';
        document.getElementById('edit-dean').value = school.dean || '';
        document.getElementById('edit-email').value = school.email || '';
        document.getElementById('edit-phone').value = school.phone || '';
        document.getElementById('edit-departments').value = school.departments_count || 0;
        document.getElementById('edit-programmes').value = school.programmes_count || 0;
        document.getElementById('edit-building').value = school.building || '';
        document.getElementById('edit-description').value = school.description || '';
        document.getElementById('edit-status').value = school.status || 'Active';

        document.getElementById('edit-school-form').action = `/curriculum/school/${school.id}`;
        document.getElementById('edit-school-modal').classList.add('open');
    }

    function confirmDeleteSchool(id, name) {
        document.getElementById('delete-school-name').textContent = name;
        document.getElementById('delete-school-form').action = `/curriculum/school/${id}`;
        document.getElementById('delete-school-modal').classList.add('open');
    }

    // Instant client filter and search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('school-search-input');
        const statusSelect = document.getElementById('school-status-filter');
        const rows = document.querySelectorAll('.school-row');

        function filterSchools() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const status = (statusSelect?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const rowSearch = row.dataset.search || '';
                const rowStatus = row.dataset.status || '';

                const matchesQuery = !query || rowSearch.includes(query);
                const matchesStatus = !status || rowStatus === status;

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput?.addEventListener('input', filterSchools);
        statusSelect?.addEventListener('change', filterSchools);
    });
</script>
@endsection
