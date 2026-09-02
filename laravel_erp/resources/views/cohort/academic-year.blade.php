@extends('layouts.app')

@section('title', 'Academic Year Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">University Academic Year Setup & Trimesters</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure institutional academic year calendars, trimester commencement windows, census audit dates, and operational status</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateYearModal()" class="px-4 py-1.5 rounded-md bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Create Academic Year
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Academic Year</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeAcademicYear'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current operational cycle.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Active / In Session</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Current Trimester</div>
            <div class="text-lg font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['currentTrimester'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Ongoing semester block.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Trimester System</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Enrolled Scholars</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['registeredStudents']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Across all active cohorts.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Active Population</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Senate Audit</div>
            <div class="text-xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['censusAuditStatus'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Statutory calendar ratifications.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Ratified</span></div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-4">
        <div class="w-full sm:w-64">
            <select id="year-status-filter" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="upcoming">Upcoming</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="w-full sm:w-72">
            <div class="relative">
                <input type="text" id="year-search-input" placeholder="Search academic year name, code…" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-2.5"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="years-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Year Code & Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Start Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">End Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="years-tbody">
                    @forelse($years as $y)
                        <tr class="hover:bg-slate-50/70 transition-colors year-row" data-status="{{ strtolower($y->status) }}" data-search="{{ strtolower($y->code.' '.$y->name.' '.$y->description) }}">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $y->code }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $y->name }}</div>
                                @if($y->description)
                                    <div class="text-[10.5px] text-slate-500 mt-0.5">{{ $y->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                {{ $y->start_date ? $y->start_date->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                {{ $y->end_date ? $y->end_date->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($y->status === 'Active')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Active / Current</span>
                                @elseif($y->status === 'Upcoming')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Upcoming / Planned</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Closed / Completed</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditYearModal(@json($y))' 
                                            class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors flex items-center gap-1"
                                            title="Edit Academic Year">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i> Edit
                                    </button>
                                    <button type="button" 
                                            onclick="confirmDeleteYear('{{ $y->id }}', '{{ addslashes($y->name) }}')" 
                                            class="p-1 rounded border border-red-200 text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete Academic Year">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No academic years registered. Click "Create Academic Year" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: CREATE ACADEMIC YEAR --}}
<div class="modal" id="create-year-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Create Academic Year</h2>
                <small style="color:rgba(255,255,255,0.85);">Configure a new university academic cycle.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" method="POST" action="{{ route('cohort.academic-year.store') }}" data-processing-message="Creating academic year…">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Year Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" placeholder="e.g. AY-2028-2029" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active">Active</option>
                        <option value="Upcoming" selected>Upcoming</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Academic Year Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="e.g. 2028/2029 Academic Year" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Commencement / Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Completion / End Date <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description / Calendar Notes</label>
                    <textarea name="description" rows="2" placeholder="Trimester breakdowns, census dates, and Senate ratifications..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Save Academic Year
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT ACADEMIC YEAR --}}
<div class="modal" id="edit-year-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Edit Academic Year</h2>
                <small style="color:rgba(255,255,255,0.85);" id="edit-year-sub">Update calendar dates and operational status.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="edit-year-form" method="POST" action="" data-processing-message="Updating academic year…">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Year Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-year-code" name="code" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="edit-year-status" name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active">Active</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Academic Year Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-year-name" name="name" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Commencement / Start Date <span class="text-red-500">*</span></label>
                    <input type="date" id="edit-year-start" name="start_date" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Completion / End Date <span class="text-red-500">*</span></label>
                    <input type="date" id="edit-year-end" name="end_date" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Description / Calendar Notes</label>
                    <textarea id="edit-year-desc" name="description" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Update Academic Year
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DELETE CONFIRMATION --}}
<div class="modal" id="delete-year-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(440px, 94vw);">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Delete Academic Year</h2>
                <small style="color:rgba(255,255,255,0.85);">Move academic year to Recycle Bin.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="delete-year-form" method="POST" action="" data-processing-message="Moving to recycle bin…">
            @csrf
            @method('DELETE')
            <p class="text-xs text-slate-600 mb-2">
                Are you sure you want to delete <strong id="delete-year-title" class="text-slate-900"></strong>?
            </p>
            <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80">
                <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i> The academic calendar will be moved to the <strong>Recycle Bin</strong> and can be restored within 30 days.
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
    function openCreateYearModal() {
        document.getElementById('create-year-modal').classList.add('open');
    }

    function openEditYearModal(year) {
        document.getElementById('edit-year-sub').textContent = `${year.code} • ${year.name}`;
        document.getElementById('edit-year-code').value = year.code || '';
        document.getElementById('edit-year-name').value = year.name || '';
        document.getElementById('edit-year-start').value = year.start_date ? year.start_date.substring(0, 10) : '';
        document.getElementById('edit-year-end').value = year.end_date ? year.end_date.substring(0, 10) : '';
        document.getElementById('edit-year-desc').value = year.description || '';
        document.getElementById('edit-year-status').value = year.status || 'Active';

        document.getElementById('edit-year-form').action = `/cohort/academic-year/${year.id}`;
        document.getElementById('edit-year-modal').classList.add('open');
    }

    function confirmDeleteYear(id, name) {
        document.getElementById('delete-year-title').textContent = name;
        document.getElementById('delete-year-form').action = `/cohort/academic-year/${id}`;
        document.getElementById('delete-year-modal').classList.add('open');
    }

    // Instant client filter and search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('year-search-input');
        const statusSelect = document.getElementById('year-status-filter');
        const rows = document.querySelectorAll('.year-row');

        function filterYears() {
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

        searchInput?.addEventListener('input', filterYears);
        statusSelect?.addEventListener('change', filterYears);
    });
</script>
@endsection
