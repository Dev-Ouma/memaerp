@extends('layouts.app')

@section('title', 'Supervisor Allocation & Workload Distribution')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Supervisor Allocation & Workload Distribution</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">HOD supervisor assignment differentiated by degree level (1 supervisor for Master's, 2 supervisors for PhD candidates) (R2)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" onclick="openAssignSupervisorModal()" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Assign Supervisors
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="alloc-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
        <div class="flex items-start gap-2.5">
            <i id="alert-icon" data-lucide="info" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
            <div>
                <strong id="alert-title" class="block font-bold"></strong>
                <span id="alert-message" class="font-normal opacity-90"></span>
            </div>
        </div>
        <button type="button" onclick="dismissAlert()" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Supervisor Allocation Rules (Report Section 4.1.2 & R2)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">School Graduate Studies Committee</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="user" class="w-4 h-4 text-blue-600"></i> Master's: 1 Supervisor
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Current policy requires <strong>one (1) supervisor</strong> for Master's candidates. Proposal stage unlocks immediately upon HOD assignment.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i> PhD: 2 Supervisors
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Doctoral candidates must have <strong>Supervisor 1 (Internal)</strong> and <strong>Supervisor 2 (Co-Supervisor)</strong> assigned.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-600"></i> Optional Mentor
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    An early-career academic mentor or industry specialist can be optionally attached to support practical fieldwork.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-[#0A3E50]"></i> Workload Balancing
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    System prevents over-allocation by enforcing CUE ceiling quotas (Max 5 PhD / Max 8 Master's candidates per faculty).
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Allocated Scholars</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['allocatedScholars'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Supervisors formally assigned.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">90.2% Allocated</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Unassigned Scholars</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['unassignedScholars'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting HOD assignment.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Needs Allocation</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">PhD 2-Supervisor Standard</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['phdTwoSupervisorRatio'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">R2 Policy Compliance.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Senate Standard</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Master 1-Supervisor Policy</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['mscOneSupervisorRatio'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Calibrated for 6-month term.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Fit-for-Purpose</span>
            </div>
        </div>

    </div>

    {{-- Filter & Search Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-3">
        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
            <span>Show</span>
            <select class="bg-white border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#0A3E50]">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
            <span>entries</span>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
            <label for="alloc-search">Search:</label>
            <input type="text" id="alloc-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search allocation...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="alloc-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Scholar & Policy Rule</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Supervisor 1 (Lead Internal)</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Supervisor 2 (Co-Supervisor)</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Mentor (Optional)</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Allocation Status</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Action</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="alloc-tbody">
                    @foreach($allocations as $al)
                        <tr class="hover:bg-slate-50/70 transition-colors alloc-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $al['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $al['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-blue-900 bg-blue-50 border border-blue-200">{{ $al['degree_level'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">
                                {{ $al['supervisor_1'] }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 text-xs">
                                {{ $al['supervisor_2'] }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 text-[11px]">
                                {{ $al['optional_mentor'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($al['status'], 'Fully Assigned'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Fully Assigned</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending Supervisor 2</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openModifyAllocModal('{{ addslashes($al['student_name']) }}', '{{ $al['reg_no'] }}', '{{ addslashes($al['degree_level']) }}', '{{ addslashes($al['supervisor_1']) }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Allocate
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer Pagination --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing 1 to {{ count($allocations) }} of {{ count($allocations) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: ASSIGN SUPERVISOR --}}
<div class="modal" id="assign-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Postgraduate Supervisor Allocation (HOD Desk)</h2>
                <small style="color:rgba(255,255,255,0.85);">Assign academic advisors adhering to CUE and Senate degree level policy.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5 text-xs space-y-3.5" onsubmit="event.preventDefault(); saveAllocation();">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Scholar Name & Degree Rule</div>
                <div class="font-bold text-slate-900 text-xs mt-0.5" id="modal-al-name"></div>
                <div class="text-slate-600 text-[11px] font-mono mt-0.5" id="modal-al-reg"></div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Supervisor 1 (Lead Internal Advisor - Mandatory)</label>
                <select class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" required>
                    <option>Prof. James Mwangi (School of Business - Load: 3/5)</option>
                    <option>Dr. Amina Hassan (School of Computing - Load: 4/5)</option>
                    <option>Dr. Daniel Otieno (Department of Economics - Load: 2/5)</option>
                    <option>Dr. Grace Njeri (School of Education - Load: 3/8)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Supervisor 2 (Co-Supervisor - Mandatory for PhD, Optional for MSc)</label>
                <select class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800">
                    <option value="">None (Master's Single Supervisor Policy)</option>
                    <option>Dr. Amina Hassan (School of Computing)</option>
                    <option>Dr. Jeremiah Onunga (Department of Statistics)</option>
                    <option>Dr. Sarah Rotich (Kenyatta University - External)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Academic Mentor / Industry Specialist (Optional)</label>
                <input type="text" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" placeholder="e.g. Prof. David Ndetei (Industry Fellow)">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold">Confirm Allocation</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleWorkflowGuide() {
        const guide = document.getElementById('admin-workflow-guide');
        const btnText = document.getElementById('workflow-toggle-btn-text');
        if (guide) {
            const isHidden = guide.classList.contains('hidden');
            guide.classList.toggle('hidden', !isHidden);
            btnText.textContent = isHidden ? 'Hide Workflow Guide' : 'Show Workflow Guide';
        }
    }

    function triggerActionAlert(type, title, message) {
        const box = document.getElementById('alloc-alert-box');
        const icon = document.getElementById('alert-icon');
        const titleEl = document.getElementById('alert-title');
        const msgEl = document.getElementById('alert-message');

        titleEl.textContent = title;
        msgEl.textContent = message;

        box.className = 'mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all';

        if (type === 'success') {
            box.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-200');
            icon.setAttribute('data-lucide', 'check-circle-2');
            icon.className = 'w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0';
        } else if (type === 'warning') {
            box.classList.add('bg-amber-50', 'text-amber-900', 'border-amber-200');
            icon.setAttribute('data-lucide', 'alert-triangle');
            icon.className = 'w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0';
        } else if (type === 'error') {
            box.classList.add('bg-red-50', 'text-red-900', 'border-red-200');
            icon.setAttribute('data-lucide', 'alert-circle');
            icon.className = 'w-4 h-4 text-red-600 mt-0.5 flex-shrink-0';
        } else {
            box.classList.add('bg-blue-50', 'text-blue-900', 'border-blue-200');
            icon.setAttribute('data-lucide', 'info');
            icon.className = 'w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0';
        }

        box.classList.remove('hidden');
        lucide.createIcons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function dismissAlert() {
        document.getElementById('alloc-alert-box').classList.add('hidden');
    }

    function openModifyAllocModal(name, reg, level, sup1) {
        document.getElementById('modal-al-name').textContent = name + ' (' + level + ')';
        document.getElementById('modal-al-reg').textContent = reg;
        document.getElementById('assign-modal').classList.add('open');
    }

    function openAssignSupervisorModal() {
        triggerActionAlert('info', 'Workload Balancing Desk', 'Select eligible candidate to assign internal Supervisor 1 and optional Co-Supervisor/Mentor.');
    }

    function saveAllocation() {
        document.getElementById('assign-modal').classList.remove('open');
        triggerActionAlert('success', 'Supervisor Allocated', 'Supervisors officially assigned. Proposal submission portal unlocked for student.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('alloc-search');
        const rows = document.querySelectorAll('.alloc-row');

        searchInput?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endsection
