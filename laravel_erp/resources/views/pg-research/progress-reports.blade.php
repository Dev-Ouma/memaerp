@extends('layouts.app')

@section('title', 'Research Progress Reports (Forms A, B, C)')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Progress Reports (Forms A, B, C)</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Monitor periodic research milestone submissions calibrated by degree level (3 for Master's, 6 for PhD) with self-service recall (R5, R14)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="progress-submit-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Log Progress Form
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Progress Reporting Calibration (Report Section 4.1.5, R5 & R14)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Directorate of Postgraduate Studies</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i> Master's: 3 Reports
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Form A (Inception), Form B (Data Collection), and Form C (Draft Dissertation) calibrated for 6-month research timeline.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-purple-800 mb-1">
                    <i data-lucide="layers" class="w-4 h-4 text-purple-600"></i> PhD: 6 Reports
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Bi-annual submission tracking theoretical development, empirical simulations, and publication outputs over 36 months.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i> Supervisor Counter-Check
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Supervisor 1 reviews and digitally certifies research milestones before next progressive stage unlocks.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="rotate-ccw" class="w-4 h-4 text-orange-600"></i> Self-Service Recall (R14)
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Students can independently recall or replace un-reviewed forms without requiring prior supervisor formal rejection.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Reports Logged</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalReportsSubmitted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Forms A, B, and C submitted.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">All Postgraduate Cohorts</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Form A (Inception)</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['formACount'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Early-stage scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Milestone 1</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Form B (Data/Analysis)</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['formBCount'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Mid-stage scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Milestone 2</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Form C (Thesis Draft)</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['formCCount'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Final stage scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Pre-Defense Clearance</span>
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
            <label for="prog-search">Search:</label>
            <input type="text" id="prog-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search progress report...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="prog-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Scholar & Level Policy</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Report Stage & Date</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Milestone Summary</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Supervisor Endorsement</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Self-Service (R14)</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="prog-tbody">
                    @foreach($reports as $r)
                        <tr class="hover:bg-slate-50/70 transition-colors prog-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $r['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $r['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $r['degree_level'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-blue-900 text-xs">{{ $r['report_stage'] }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">Submitted: {{ $r['submission_date'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-700 text-[11px]">
                                {{ $r['milestone_summary'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-semibold {{ str_contains($r['supervisor_endorsement'], 'Approved') ? 'text-emerald-800' : 'text-amber-800' }}">
                                    {{ $r['supervisor_endorsement'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-[11px]">
                                @if(str_contains($r['self_service_action'], 'Recall'))
                                    <span class="inline-block px-2 py-0.5 rounded font-bold bg-amber-50 text-amber-800 border border-amber-200">Recall / Replace Available</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded font-medium bg-slate-100 text-slate-600">Locked (Approved)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($r['is_open'])
                                    <div class="flex flex-col items-center gap-1">
                                        <x-pg.action
                                            :action="route('pg-research.progress-reports.decide', $r['id'])"
                                            :fields="['decision' => 'APPROVED']"
                                            label="Endorse"
                                            variant="approve"
                                            confirm="Endorse and sign off this progress report?" />
                                        <button type="button" data-modal-open="progress-return-modal"
                                                data-report="{{ $r['id'] }}"
                                                class="px-3 py-1 rounded border border-amber-400 text-amber-700 hover:bg-amber-50 font-semibold text-[10.5px] transition-colors return-trigger">
                                            Request clarification
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10.5px] text-emerald-700 font-semibold">Approved</span>
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
                Showing 1 to {{ count($reports) }} of {{ count($reports) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: SUBMIT PROGRESS REPORT --}}
<x-pg.modal-form
    id="progress-submit-modal"
    title="Submit Progress Report"
    subtitle="One report per candidate per reporting period."
    :action="route('pg-research.progress-reports.store')"
    submit-label="Submit report"
    width="620px">

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Reporting period" name="period_label" required>
            <input type="text" name="period_label" required maxlength="60" value="{{ old('period_label') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs" placeholder="2026/2027 Semester 1">
        </x-pg.field>

        <x-pg.field label="Research stage" name="report_stage" required>
            <input type="text" name="report_stage" required maxlength="60" value="{{ old('report_stage') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs" placeholder="Data collection">
        </x-pg.field>
    </div>

    <x-pg.field label="Milestone summary" name="milestone_summary" required>
        <textarea name="milestone_summary" rows="5" required minlength="10"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('milestone_summary') }}</textarea>
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: RETURN FOR CLARIFICATION --}}
<div class="modal" id="progress-return-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <form method="POST" action="{{ route('pg-research.progress-reports.decide', 0) }}" id="return-form">
            @csrf
            <input type="hidden" name="decision" value="RETURNED">
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Return Report for Clarification</h2>
                    <small style="color:rgba(255,255,255,0.85);">The comment is saved on the report and shown to the candidate.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <x-pg.field label="What must be clarified?" name="comment" required>
                    <textarea name="comment" rows="4" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>
                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs">Return report</button>
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

    function openProgressModal(name, reg, stage, milestone) {
        document.getElementById('modal-pg-student').textContent = name + ' (' + reg + ')';
        document.getElementById('modal-pg-stage').textContent = stage;
        document.getElementById('modal-pg-milestone').textContent = milestone;
        document.getElementById('prog-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const returnBase = @js(route('pg-research.progress-reports.decide', 0));
        document.querySelectorAll('.return-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('return-form').action = returnBase.replace(/\/0\//, '/' + btn.dataset.report + '/');
            });
        });

        const searchInput = document.getElementById('prog-search');
        const rows = document.querySelectorAll('.prog-row');

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
