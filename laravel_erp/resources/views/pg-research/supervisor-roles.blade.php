@extends('layouts.app')

@section('title', 'Supervisor Role Configuration')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Title & Top Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Supervisor Role Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure postgraduate supervisor eligibility criteria, workload capacity quotas, and milestone sign-off authorization</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" onclick="openAddRoleModal()" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="role-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
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

    {{-- Governance & Lifecycle Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Commission for University Education (CUE) Supervision Quota Standards</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">CUE Harmonized Standards 2026</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="award" class="w-4 h-4 text-[#0A3E50]"></i> Minimum Qualification
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Lead doctoral supervisors must hold an earned PhD and rank at Senior Lecturer, Associate Professor, or Full Professor.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i> Workload Caps (1:5 / 1:8)
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    System prevents assigning more than 5 active doctoral or 8 master's candidates to prevent faculty burnout.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="check-square" class="w-4 h-4 text-blue-600"></i> Milestone Authority
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Defines digital signature power for Concept Papers, Proposal Defense, Notice of Intent to Submit, and Viva Clearance.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="coins" class="w-4 h-4 text-orange-600"></i> Honorarium Schedule
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Configured rates link directly to the finance module to trigger milestone disbursements upon approved viva progression.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Configured Roles</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalRoles'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Supervision & mentorship tiers.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">100% CUE Compliant</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Supervisors</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['activeSupervisors']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Approved academic faculty.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">8 Faculties Active</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Scholars</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['activeScholars']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">PhD & MSc candidates assigned.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Average Load: 3.4 / faculty</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Mandated Ratio Cap</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['maxRatio'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Hard regulatory ceiling.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Statutory Standard</span>
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
            <label for="role-search">Search:</label>
            <input type="text" id="role-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search supervisor role...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="roles-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Role Code</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Role Title & Scope</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Min Qualification</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Max Quota</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Honorarium</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Status</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="roles-tbody">
                    @foreach($roles as $r)
                        <tr class="hover:bg-slate-50/70 transition-colors role-row">
                            <td class="py-3.5 px-4 font-bold text-slate-900 font-mono">{{ $r['role_code'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $r['role_title'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5"><strong class="text-slate-700">Sign-off:</strong> {{ $r['sign_off_scope'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                    {{ $r['min_qualification'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ $r['max_quota'] }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] font-semibold text-emerald-800">
                                {{ $r['honorarium_unit'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($r['status'] === 'Active')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openEditRoleModal('{{ $r['role_code'] }}', '{{ addslashes($r['role_title']) }}', '{{ addslashes($r['min_qualification']) }}', '{{ addslashes($r['max_quota']) }}', '{{ addslashes($r['sign_off_scope']) }}', '{{ addslashes($r['honorarium_unit']) }}', '{{ $r['status'] }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Edit
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
                Showing 1 to {{ count($roles) }} of {{ count($roles) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: ADD / EDIT SUPERVISOR ROLE --}}
<div class="modal" id="role-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white" id="role-modal-title">Configure Supervisor Role</h2>
                <small style="color:rgba(255,255,255,0.85);">Define eligibility thresholds, quota limits, and honoraria.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" onsubmit="event.preventDefault(); saveRole();">
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Role Code</label>
                        <input type="text" id="modal-r-code" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" placeholder="e.g. SUP-LEAD-01" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Max Supervisee Quota</label>
                        <input type="text" id="modal-r-quota" class="w-full border border-slate-300 rounded p-2 text-xs" placeholder="e.g. 5 PhD Candidates" required>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Role Title</label>
                    <input type="text" id="modal-r-title" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" placeholder="e.g. Lead Doctoral Supervisor (Major Advisor)" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Minimum Qualification Required</label>
                    <select id="modal-r-qual" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" required>
                        <option>PhD / Associate Professor or Professor</option>
                        <option>PhD / Senior Lecturer</option>
                        <option>PhD / Lecturer with 3+ yrs experience</option>
                        <option>PhD / Certified Industry Fellow</option>
                        <option>Professor / Distinguished Scholar</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Milestone Sign-off Scope</label>
                    <input type="text" id="modal-r-scope" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" placeholder="e.g. Concept, Proposal, Ethics, Viva, Final Submission" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Honorarium Unit Rate</label>
                        <input type="text" id="modal-r-honorarium" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" placeholder="KES 45,000 / candidate" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Role Status</label>
                        <select id="modal-r-status" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold">Save Role</button>
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
        const box = document.getElementById('role-alert-box');
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
        document.getElementById('role-alert-box').classList.add('hidden');
    }

    function openAddRoleModal() {
        document.getElementById('role-modal-title').textContent = 'Add Supervisor Role';
        document.getElementById('modal-r-code').value = '';
        document.getElementById('modal-r-title').value = '';
        document.getElementById('modal-r-quota').value = '5 PhD Candidates';
        document.getElementById('modal-r-scope').value = '';
        document.getElementById('modal-r-honorarium').value = 'KES 45,000 / candidate';
        document.getElementById('role-modal').classList.add('open');
    }

    function openEditRoleModal(code, title, qual, quota, scope, honorarium, status) {
        document.getElementById('role-modal-title').textContent = 'Edit Supervisor Role (' + code + ')';
        document.getElementById('modal-r-code').value = code;
        document.getElementById('modal-r-title').value = title;
        document.getElementById('modal-r-qual').value = qual;
        document.getElementById('modal-r-quota').value = quota;
        document.getElementById('modal-r-scope').value = scope;
        document.getElementById('modal-r-honorarium').value = honorarium;
        document.getElementById('modal-r-status').value = status;
        document.getElementById('role-modal').classList.add('open');
    }

    function saveRole() {
        document.getElementById('role-modal').classList.remove('open');
        triggerActionAlert('success', 'Supervisor Role Saved', 'Postgraduate supervisor configuration successfully updated and policy limits enacted.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('role-search');
        const rows = document.querySelectorAll('.role-row');

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
