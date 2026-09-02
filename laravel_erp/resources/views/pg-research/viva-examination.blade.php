@extends('layouts.app')

@section('title', 'Graduate Level Viva Examination')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Graduate Level Viva Examination</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Schedule doctoral & master oral defense sessions, configure examination panellists, and record viva verdicts</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" onclick="openScheduleModal()" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Schedule Viva
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="viva-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
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
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Doctoral & Master Oral Examination Board Constitution</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Statute XXXII - Oral Defense Governance</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="user-check" class="w-4 h-4 text-[#0A3E50]"></i> Board Chairperson
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Dean of School or Appointed Senior Professor presiding over examination proceedings without grading voting power.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="award" class="w-4 h-4 text-blue-600"></i> External Examiner
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Independent academic from another recognized university probing thesis contribution and methodology.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i> Internal Examiners
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Two departmental subject specialists examining literature, analytical rigour, and local contextual impact.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="scale" class="w-4 h-4 text-orange-600"></i> 4 Standard Verdicts
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    (1) Pass without corrections, (2) Minor corrections (30d), (3) Major corrections (90d), or (4) Re-sit / Fail.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Scheduled Vivas</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['scheduledVivas'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Upcoming oral defenses.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Semester 1 Calendar</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Completed This Month</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['completedThisMonth'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Boards concluded.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">On Track</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Historical Pass Rate</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['passRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">First-sitting viva clearances.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">High Academic Quality</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Panels Pending Emplacement</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingPanels'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting external acceptance.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Needs Emplacement</span>
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
            <label for="viva-search">Search:</label>
            <input type="text" id="viva-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search viva session...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="viva-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Candidate & Degree</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Viva Schedule & Venue</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Board Panellists</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Viva Status</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Action</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="viva-tbody">
                    @foreach($vivas as $v)
                        <tr class="hover:bg-slate-50/70 transition-colors viva-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $v['candidate_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $v['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $v['degree'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-blue-900 font-mono text-xs">{{ $v['viva_date'] }}</div>
                                <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i> {{ $v['venue'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 space-y-1">
                                <div class="text-[11px] text-slate-800"><strong class="text-slate-500">Chair:</strong> {{ $v['board_chair'] }}</div>
                                <div class="text-[11px] text-slate-800"><strong class="text-slate-500">External:</strong> {{ $v['external_examiner'] }}</div>
                                <div class="text-[11px] text-slate-800"><strong class="text-slate-500">Internal:</strong> {{ $v['internal_examiner'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($v['status'], 'Scheduled') || str_contains($v['status'], 'Confirmed'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Confirmed & Scheduled</span>
                                @elseif(str_contains($v['status'], 'Completed'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Completed</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending External</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openVerdictModal('{{ addslashes($v['candidate_name']) }}', '{{ $v['reg_no'] }}', '{{ addslashes($v['degree']) }}', '{{ $v['viva_date'] }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Verdict
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
                Showing 1 to {{ count($vivas) }} of {{ count($vivas) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: RECORD VIVA VERDICT --}}
<div class="modal" id="verdict-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Record Oral Viva Examination Verdict</h2>
                <small style="color:rgba(255,255,255,0.85);">Formally table the Examination Board's collective decision.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5 text-xs space-y-3.5" onsubmit="event.preventDefault(); saveVerdict();">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Scholar Name & Degree</div>
                <div class="font-bold text-slate-900 text-xs mt-0.5" id="modal-v-student"></div>
                <div class="text-slate-600 text-[11px] font-mono mt-0.5" id="modal-v-reg"></div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Board Collective Verdict</label>
                <select id="modal-v-verdict" class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" required>
                    <option value="pass_minor">Pass with Minor Corrections (30-day window)</option>
                    <option value="pass_clean">Pass without Corrections (Immediate Hardbound)</option>
                    <option value="pass_major">Pass with Major Corrections (90-day re-examination)</option>
                    <option value="resit">Re-sit Oral Defense / Re-write Thesis</option>
                    <option value="fail">Fail / Discontinue Candidature</option>
                </select>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Internal Mark (%)</label>
                    <input type="number" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" min="0" max="100" value="82" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">External Mark (%)</label>
                    <input type="number" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" min="0" max="100" value="85" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Viva Presentation (%)</label>
                    <input type="number" class="w-full border border-slate-300 rounded p-2 text-xs font-mono" min="0" max="100" value="84" required>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Board Recommendations / Required Corrections Summary</label>
                <textarea class="w-full border border-slate-300 rounded p-2 text-xs text-slate-800" rows="3" placeholder="Specify required amendments to Chapter 3 methodology, sample size clarifications, and formatting updates..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold">Submit Official Verdict</button>
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
        const box = document.getElementById('viva-alert-box');
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
        document.getElementById('viva-alert-box').classList.add('hidden');
    }

    function openVerdictModal(student, reg, degree, date) {
        document.getElementById('modal-v-student').textContent = student + ' (' + degree + ')';
        document.getElementById('modal-v-reg').textContent = reg + ' • Defended on: ' + date;
        document.getElementById('verdict-modal').classList.add('open');
    }

    function openScheduleModal() {
        triggerActionAlert('info', 'Viva Scheduling Assistant', 'Select eligible candidate from Defence Clearance list to assign panellists and book venue.');
    }

    function saveVerdict() {
        document.getElementById('verdict-modal').classList.remove('open');
        triggerActionAlert('success', 'Viva Verdict Tabulated', 'Examination board verdict officially logged and transmitted to Directorate of Postgraduate Studies.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('viva-search');
        const rows = document.querySelectorAll('.viva-row');

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
