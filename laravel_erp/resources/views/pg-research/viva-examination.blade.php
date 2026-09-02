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
            <button type="button" data-modal-open="viva-schedule-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Schedule Viva
            </button>
        </div>
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
                                @if($v['is_open'])
                                    <button type="button" data-modal-open="viva-verdict-modal"
                                            data-viva="{{ $v['id'] }}"
                                            data-candidate="{{ $v['candidate_name'] }}"
                                            data-reg="{{ $v['reg_no'] }}"
                                            class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors verdict-trigger">
                                        Record verdict
                                    </button>
                                @else
                                    <span class="text-[10.5px] text-slate-500 font-semibold">{{ $v['status'] }}</span>
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

{{-- MODAL: SCHEDULE VIVA --}}
<x-pg.modal-form
    id="viva-schedule-modal"
    title="Schedule Viva Voce Examination"
    subtitle="Only candidates whose full examiner panel has filed reports are listed."
    :action="route('pg-research.vivas.store')"
    submit-label="Schedule viva"
    width="620px">

    <x-pg.field label="Candidate" name="candidate_id" required hint="A candidate with an outstanding examiner report cannot be scheduled.">
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($readyCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
        @if($readyCandidates->isEmpty())
            <span class="block text-[10.5px] text-amber-700 font-semibold mt-1">
                No candidate currently has a complete examiner panel report set.
            </span>
        @endif
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Date &amp; time" name="scheduled_for" required>
            <input type="datetime-local" name="scheduled_for" required
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Venue" name="venue" required>
            <input type="text" name="venue" required maxlength="190" value="{{ old('venue') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>

    <x-pg.field label="Board chair" name="chair_name">
        <input type="text" name="chair_name" maxlength="190" value="{{ old('chair_name') }}"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: RECORD VIVA VERDICT --}}
<div class="modal" id="viva-verdict-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(600px, 94vw);">
        <form method="POST" action="{{ route('pg-research.vivas.verdict', 0) }}" id="verdict-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Record Examination Board Verdict</h2>
                    <small style="color:rgba(255,255,255,0.85);">A corrections verdict opens a resubmission cycle with a real due date.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="font-bold text-slate-900 text-xs" id="verdict-candidate"></div>
                    <div class="text-slate-600 text-[11px] font-mono mt-0.5" id="verdict-reg"></div>
                </div>

                <x-pg.field label="Verdict" name="verdict" required>
                    <select name="verdict" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="PASS">Pass as submitted — composite mark computed from panel scores</option>
                        <option value="PASS_MINOR">Pass with minor corrections — 90-day resubmission cycle</option>
                        <option value="PASS_MAJOR">Pass with major corrections — 180-day resubmission cycle</option>
                        <option value="REEXAMINE">Re-examine — candidate returns to writing</option>
                        <option value="FAIL">Fail</option>
                    </select>
                </x-pg.field>

                <x-pg.field label="Board notes" name="verdict_notes" required>
                    <textarea name="verdict_notes" rows="5" required minlength="10"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Record verdict</button>
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

    function openVerdictModal(student, reg, degree, date) {
        document.getElementById('modal-v-student').textContent = student + ' (' + degree + ')';
        document.getElementById('modal-v-reg').textContent = reg + ' • Defended on: ' + date;
        document.getElementById('verdict-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const verdictBase = @js(route('pg-research.vivas.verdict', 0));
        document.querySelectorAll('.verdict-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('verdict-form').action = verdictBase.replace(/\/0\//, '/' + btn.dataset.viva + '/');
                document.getElementById('verdict-candidate').textContent = btn.dataset.candidate;
                document.getElementById('verdict-reg').textContent = btn.dataset.reg;
            });
        });

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
