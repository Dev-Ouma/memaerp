@extends('layouts.app')

@section('title', 'Seminar Presentations Tracking')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Postgraduate Seminar Presentations Tracking</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Record departmental and school-level research seminar milestones distinct from formal oral defences (R3)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="seminar-schedule-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Schedule Seminar
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Postgraduate Seminar Milestone Framework (Report Section 4.1.3 & R3)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Department of Mathematics & Statistics / SST</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="presentation" class="w-4 h-4 text-blue-600"></i> Seminar 1: Concept / Inception
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Departmental presentation of research problem statement, scope, objectives, and preliminary methodology.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="line-chart" class="w-4 h-4 text-emerald-600"></i> Seminar 2: Progress & Data
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Presentation of empirical field results, statistical simulations, and laboratory data for peer review.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-purple-800 mb-1">
                    <i data-lucide="award" class="w-4 h-4 text-purple-600"></i> Seminar 3: Pre-Defense (PhD)
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    School-level public seminar presentation prior to formal submission of final draft thesis for examination.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#0A3E50]"></i> Seminar Certification
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    HOD certification of seminar attendance and presentation is a prerequisite to unlock viva scheduling.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Completed Seminars</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['seminarsCompleted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Doctoral & Master scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Academic Year 2026/27</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Departmental Seminars</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['departmentalSeminars'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Concept & Progress milestones.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Department Level</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pre-Defense Seminars</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['preDefenseSeminars'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">School-wide presentations.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">School Level</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Faculty Attendance Rate</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['attendanceRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Peer review participation.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">High Engagement</span>
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
            <label for="seminar-search">Search:</label>
            <input type="text" id="seminar-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search seminar...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="seminar-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Scholar & Programme</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Seminar Type & Tier</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Presentation Date & Moderator</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Panel Feedback</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="seminar-tbody">
                    @foreach($seminars as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors seminar-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $s['candidate_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $s['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $s['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-purple-900 bg-purple-50 border border-purple-200">
                                    {{ $s['seminar_type'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-xs font-bold text-blue-900">{{ $s['presentation_date'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5"><strong class="text-slate-700">Moderator:</strong> {{ $s['moderator'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-[11px] text-slate-700 leading-snug">
                                {{ $s['panel_feedback'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($s['status'] === 'Completed & Certified')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Certified</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Scheduled</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($s['is_open'])
                                    <button type="button" data-modal-open="seminar-conclude-modal"
                                            data-seminar="{{ $s['id'] }}"
                                            data-candidate="{{ $s['candidate_name'] }}"
                                            class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors conclude-trigger">
                                        Record outcome
                                    </button>
                                @else
                                    <span class="text-[10.5px] text-slate-500 font-semibold">{{ $s['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer Pagination --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing 1 to {{ count($seminars) }} of {{ count($seminars) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: SCHEDULE SEMINAR --}}
<x-pg.modal-form
    id="seminar-schedule-modal"
    title="Schedule Research Seminar"
    :action="route('pg-research.seminars.store')"
    submit-label="Schedule seminar"
    width="600px">

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Seminar type" name="seminar_type" required>
            <select name="seminar_type" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                <option value="PROPOSAL">Departmental proposal seminar</option>
                <option value="PROGRESS">Progress seminar</option>
                <option value="PRE_DEFENCE">Pre-defence seminar</option>
            </select>
        </x-pg.field>

        <x-pg.field label="Date &amp; time" name="scheduled_for" required>
            <input type="datetime-local" name="scheduled_for" required
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Venue" name="venue" required>
            <input type="text" name="venue" required maxlength="190" value="{{ old('venue') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Panel chair" name="panel_chair">
            <input type="text" name="panel_chair" maxlength="190" value="{{ old('panel_chair') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>
</x-pg.modal-form>

{{-- MODAL: RECORD SEMINAR OUTCOME --}}
<div class="modal" id="seminar-conclude-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(560px, 94vw);">
        <form method="POST" action="{{ route('pg-research.seminars.conclude', 0) }}" id="conclude-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Record Seminar Outcome</h2>
                    <small style="color:rgba(255,255,255,0.85);">Only a scheduled seminar can be concluded.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg font-bold text-slate-900 text-xs" id="conclude-candidate"></div>

                <x-pg.field label="Outcome" name="status" required>
                    <select name="status" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="PASSED">Passed</option>
                        <option value="HELD">Held — outcome pending</option>
                        <option value="FAILED">Failed</option>
                        <option value="DEFERRED">Deferred</option>
                        <option value="CANCELLED">Cancelled</option>
                    </select>
                </x-pg.field>

                <x-pg.field label="Panel feedback" name="outcome_notes">
                    <textarea name="outcome_notes" rows="4" class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Record outcome</button>
                </div>
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

    function openCertifySeminarModal(name, reg, type, feedback) {
        document.getElementById('modal-sm-name').textContent = name + ' (' + reg + ')';
        document.getElementById('modal-sm-type').textContent = type;
        document.getElementById('modal-sm-feedback').textContent = feedback;
        document.getElementById('sem-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const concludeBase = @js(route('pg-research.seminars.conclude', 0));
        document.querySelectorAll('.conclude-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('conclude-form').action = concludeBase.replace(/\/0\//, '/' + btn.dataset.seminar + '/');
                document.getElementById('conclude-candidate').textContent = btn.dataset.candidate;
            });
        });

        const searchInput = document.getElementById('seminar-search');
        const rows = document.querySelectorAll('.seminar-row');

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
