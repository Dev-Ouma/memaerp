@extends('layouts.app')

@section('title', 'Examiner Dashboard')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Examiner Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage thesis evaluation dispatches, blind manuscript assessments, evaluation rubrics, and honoraria claims</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="examiner-appoint-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Appoint Examiner
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">External & Internal Examiner Workflow and SLA Regulations</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Examiner Board Code of Conduct</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="lock" class="w-4 h-4 text-[#0A3E50]"></i> Blind Peer Examination
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Manuscripts are coded and blinded to protect candidate identity and uphold objective academic assessment.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i> 21-Day Evaluation SLA
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Examiners must submit their comprehensive evaluation report and scoring rubric within 21 calendar days of dispatch.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i> Standard Scoring Rubric
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Evaluation spans: Originality, Literature Depth, Research Design & Methodology, Data Rigour, and Contribution to Knowledge.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="credit-card" class="w-4 h-4 text-blue-600"></i> Automated Honorarium
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Report submission triggers automatic verification and disbursement of the examiner statutory honorarium.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Assigned Manuscripts</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['assignedManuscripts'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active evaluation dossiers.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">External & Internal</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Evaluations Completed</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['evaluationsCompleted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Reports & rubrics received.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">64.3% Turnaround</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Evaluations Pending</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['evaluationsPending'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Within active 21d SLA window.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">In Progress</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Turnaround</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['avgTurnaroundDays'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Institutional performance.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">SLA Compliant</span>
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
            <label for="exam-search">Search:</label>
            <input type="text" id="exam-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search examiner...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="exam-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Examiner & Affiliation</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Candidate Code & Manuscript</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Dispatch & Due Dates</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Evaluation Report Status</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Honorarium</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="exam-tbody">
                    @foreach($assignments as $as)
                        <tr class="hover:bg-slate-50/70 transition-colors exam-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $as['examiner_name'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $as['examiner_type'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $as['candidate_code'] }}</span>
                                <div class="font-medium text-slate-800 text-xs mt-1 leading-snug">{{ $as['thesis_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                <div><strong class="text-slate-500">Sent:</strong> {{ $as['dispatch_date'] }}</div>
                                <div class="text-amber-800 font-semibold mt-0.5"><strong class="text-slate-500">Due:</strong> {{ $as['due_date'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($as['report_status'], 'Submitted'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $as['report_status'] }}</span>
                                @elseif(str_contains($as['report_status'], 'Under Review'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">{{ $as['report_status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $as['report_status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] font-semibold text-slate-700">
                                {{ $as['honorarium_status'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($as['has_report'])
                                    <span class="text-[10.5px] text-emerald-700 font-semibold">Report filed</span>
                                @else
                                    <button type="button" data-modal-open="examiner-report-modal"
                                            data-examiner="{{ $as['id'] }}"
                                            data-name="{{ $as['examiner_name'] }}"
                                            class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors report-trigger">
                                        File report
                                    </button>
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
                Showing 1 to {{ count($assignments) }} of {{ count($assignments) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: APPOINT EXAMINER --}}
<x-pg.modal-form
    id="examiner-appoint-modal"
    title="Appoint Thesis Examiner"
    subtitle="Only candidates with approved defence clearance can be listed here."
    :action="route('pg-research.examiners.store')"
    submit-label="Appoint examiner"
    width="620px">

    <x-pg.field label="Candidate" name="candidate_id" required hint="Populated from candidates whose defence clearance is approved.">
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($defenceCleared as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Examiner name" name="examiner_name" required>
            <input type="text" name="examiner_name" required maxlength="190" value="{{ old('examiner_name') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Examiner type" name="examiner_type" required>
            <select name="examiner_type" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                <option value="EXTERNAL">External examiner</option>
                <option value="INTERNAL">Internal examiner</option>
                <option value="CHAIR">Board chair</option>
            </select>
        </x-pg.field>

        <x-pg.field label="Institution" name="institution">
            <input type="text" name="institution" maxlength="190" value="{{ old('institution') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Email" name="email">
            <input type="email" name="email" maxlength="190" value="{{ old('email') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>
</x-pg.modal-form>

{{-- MODAL: FILE EXAMINER REPORT --}}
<div class="modal" id="examiner-report-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(600px, 94vw);">
        <form method="POST" action="{{ route('pg-research.examiners.report', 0) }}" id="examiner-report-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">File Examiner Report</h2>
                    <small style="color:rgba(255,255,255,0.85);">The viva cannot be scheduled until every appointed examiner has filed.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg font-bold text-slate-900 text-xs" id="examiner-report-name"></div>

                <div class="grid grid-cols-2 gap-3">
                    <x-pg.field label="Recommendation" name="recommendation" required>
                        <select name="recommendation" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                            <option value="PASS">Pass as submitted</option>
                            <option value="MINOR">Pass with minor corrections</option>
                            <option value="MAJOR">Pass with major corrections</option>
                            <option value="REEXAMINE">Re-examine</option>
                            <option value="FAIL">Fail</option>
                        </select>
                    </x-pg.field>

                    <x-pg.field label="Score" name="score" hint="Panel scores are averaged into the composite mark.">
                        <input type="number" step="0.01" min="0" max="100" name="score"
                               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                    </x-pg.field>
                </div>

                <x-pg.field label="Remarks" name="remarks" required>
                    <textarea name="remarks" rows="5" required minlength="10"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">File report</button>
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

    function openExaminerModal(name, code, title, status) {
        document.getElementById('modal-e-name').textContent = name;
        document.getElementById('modal-e-code').textContent = 'Candidate: ' + code;
        document.getElementById('modal-e-title').textContent = title;
        document.getElementById('examiner-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const reportBase = @js(route('pg-research.examiners.report', 0));
        document.querySelectorAll('.report-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('examiner-report-form').action = reportBase.replace(/\/0\//, '/' + btn.dataset.examiner + '/');
                document.getElementById('examiner-report-name').textContent = btn.dataset.name;
            });
        });

        const searchInput = document.getElementById('exam-search');
        const rows = document.querySelectorAll('.exam-row');

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
