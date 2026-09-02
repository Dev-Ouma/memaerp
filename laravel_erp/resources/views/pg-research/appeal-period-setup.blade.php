@extends('layouts.app')

@section('title', 'PG Appeal Period Setup')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Title & Top Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">PG Appeal Period Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure postgraduate appeal submission windows, examination review cutoffs, and Senate hearing dates</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" onclick="openAddPeriodModal()" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="period-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
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
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Postgraduate Appeals Schedule Governance & Senate Hearing Timeline</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Senate Regulation 14.3</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i> Submission Window
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    The designated period during which registered PhD and Master's scholars can file formal appeal dossiers via the portal.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-red-700 mb-1">
                    <i data-lucide="calendar-x" class="w-4 h-4 text-red-600"></i> Hard Submission Cutoff
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    System blocks late entries automatically to permit Secretariat document audit before panel hearings.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="gavel" class="w-4 h-4 text-blue-600"></i> Hearing & Board Date
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Formal sitting of the Postgraduate Appeals Tribunal to review submissions, call examiners, and deliberate verdicts.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="history" class="w-4 h-4 text-orange-600"></i> Window Extensions
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Senate Executive Chair sign-off required to adjust window dates. Modification triggers student email notification.
                </p>
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
            <label for="period-search">Search:</label>
            <input type="text" id="period-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search appeal window...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="period-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Appeal Window & Cohort</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Academic Year</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Start Date</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Cutoff Date</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Hearing Sitting</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="period-tbody">
                    @foreach($periods as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors period-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $p['window_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $p['cohort'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">{{ $p['academic_year'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">{{ $p['start_date'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">{{ $p['end_date'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-900">{{ $p['hearing_date'] }}</td>
                            <td class="py-3.5 px-4">
                                @if($p['status'] === 'Open')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Open Window</span>
                                @elseif($p['status'] === 'Scheduled')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Scheduled</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-600">Closed</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openEditPeriodModal('{{ addslashes($p['window_name']) }}', '{{ $p['academic_year'] }}', '{{ addslashes($p['cohort']) }}', '{{ $p['start_date'] }}', '{{ $p['end_date'] }}', '{{ $p['hearing_date'] }}', '{{ $p['status'] }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
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
                Showing 1 to {{ count($periods) }} of {{ count($periods) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: ADD / EDIT APPEAL PERIOD --}}
<div class="modal" id="period-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white" id="period-modal-title">Configure PG Appeal Window</h2>
                <small style="color:rgba(255,255,255,0.85);">Set submission window, deadline, and tribunal sitting dates.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" onsubmit="event.preventDefault(); savePeriod();">
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Appeal Window Title</label>
                    <input type="text" id="modal-p-name" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" placeholder="e.g. 2026/2027 Semester 1 Postgraduate Defense Appeals" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Academic Year</label>
                        <select id="modal-p-year" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" required>
                            <option>2026-2027</option>
                            <option>2025-2026</option>
                            <option>2024-2025</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Eligible Cohort</label>
                        <input type="text" id="modal-p-cohort" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" placeholder="e.g. PhD & Master Candidates" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Submission Start Date</label>
                        <input type="text" id="modal-p-start" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" placeholder="DD-MM-YYYY" value="01-09-2026" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Submission Cutoff Date</label>
                        <input type="text" id="modal-p-end" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" placeholder="DD-MM-YYYY" value="30-09-2026" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Board / Tribunal Hearing Date</label>
                        <input type="text" id="modal-p-hearing" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" placeholder="DD-MM-YYYY" value="15-10-2026" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Window Status</label>
                        <select id="modal-p-status" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800">
                            <option value="Open">Open</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold">Save Window</button>
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
        const box = document.getElementById('period-alert-box');
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
        document.getElementById('period-alert-box').classList.add('hidden');
    }

    function openAddPeriodModal() {
        document.getElementById('period-modal-title').textContent = 'Add PG Appeal Window';
        document.getElementById('modal-p-name').value = '';
        document.getElementById('modal-p-cohort').value = 'All Postgraduate Scholars';
        document.getElementById('period-modal').classList.add('open');
    }

    function openEditPeriodModal(name, year, cohort, start, end, hearing, status) {
        document.getElementById('period-modal-title').textContent = 'Edit Appeal Window (' + year + ')';
        document.getElementById('modal-p-name').value = name;
        document.getElementById('modal-p-year').value = year;
        document.getElementById('modal-p-cohort').value = cohort;
        document.getElementById('modal-p-start').value = start;
        document.getElementById('modal-p-end').value = end;
        document.getElementById('modal-p-hearing').value = hearing;
        document.getElementById('modal-p-status').value = status;
        document.getElementById('period-modal').classList.add('open');
    }

    function savePeriod() {
        document.getElementById('period-modal').classList.remove('open');
        triggerActionAlert('success', 'Appeal Schedule Saved', 'Postgraduate appeal period successfully configured and active in portal.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('period-search');
        const rows = document.querySelectorAll('.period-row');

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
