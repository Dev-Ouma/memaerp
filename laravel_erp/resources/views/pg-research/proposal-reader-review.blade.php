@extends('layouts.app')

@section('title', 'Proposal Reader Review')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Proposal Reader Review Stage</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Independent proposal reader / internal examiner individual assessment step before oral defense panel (R6 Blocking Requirement)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="proposal-submit-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Appoint Reader
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Proposal Reader / Internal Examiner Review Governance (Report Section 4.2.1 & R6)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Mandatory Quality Gate</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="book-open" class="w-4 h-4 text-[#0A3E50]"></i> Individual Reader Audit
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    A designated academic conducts a thorough written critique of the research proposal prior to any panel sitting.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="user-check" class="w-4 h-4 text-blue-600"></i> Continuity to Viva
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    The appointed reader transitions automatically into the candidate's Internal Examiner for the thesis viva examination.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i> 14-Day Review SLA
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Reader has 14 days to provide structured feedback: (1) Approved to defend, (2) Minor revisions, or (3) Re-write.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Quality Assurance
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Prevents students from presenting weak proposals to defense panels, protecting candidate confidence and academic rigor.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Proposals Under Review</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['proposalsUnderReview'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active reader evaluations.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Quality Pipeline</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Reader Approved</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['readerApproved'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Ready for oral proposal panel.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Panel Cleared</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Revisions Requested</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['readerRevisions'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Candidate addressing comments.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Pre-Panel Polish</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Reader SLA</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['readerTurnaround'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Within 14d regulatory SLA.</p>
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
            <label for="reader-search">Search:</label>
            <input type="text" id="reader-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search proposal...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="reader-table">
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
                                <span class="text-white font-bold" style="color:#ffffff !important;">Proposal Title</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Appointed Reader / Examiner</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Reader Verdict</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="reader-tbody">
                    @foreach($proposals as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors reader-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $p['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $p['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $p['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-medium text-slate-800 text-xs leading-snug">{{ $p['proposal_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">{{ $p['appointed_reader'] }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">Assigned: {{ $p['assigned_date'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-semibold text-emerald-800">{{ $p['reader_verdict'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $p['comments_summary'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($p['status'] === 'Reader Cleared')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Reader Cleared</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Revisions</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($p['is_open'])
                                        <button type="button" data-modal-open="proposal-review-modal"
                                                data-proposal="{{ $p['id'] }}"
                                                data-student="{{ $p['student_name'] }}"
                                                data-title="{{ $p['proposal_title'] }}"
                                                class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors review-trigger">
                                            Record verdict
                                        </button>
                                        <button type="button" data-modal-open="proposal-reader-modal"
                                                data-proposal="{{ $p['id'] }}"
                                                class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-[10.5px] transition-colors reader-trigger">
                                            Appoint reader
                                        </button>
                                    @else
                                        <span class="text-[10.5px] text-slate-500 font-semibold">{{ $p['status'] }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer Pagination --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing 1 to {{ count($proposals) }} of {{ count($proposals) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: SUBMIT PROPOSAL --}}
<x-pg.modal-form
    id="proposal-submit-modal"
    title="Submit Research Proposal"
    subtitle="Versioning is automatic; a candidate blocked on eligibility is refused."
    :action="route('pg-research.proposals.store')"
    submit-label="Submit proposal"
    width="620px">

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Proposal title" name="title" required>
        <input type="text" name="title" required maxlength="190" value="{{ old('title') }}"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>

    <x-pg.field label="Abstract" name="abstract">
        <textarea name="abstract" rows="4" class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('abstract') }}</textarea>
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: APPOINT READER --}}
<div class="modal" id="proposal-reader-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(520px, 94vw);">
        <form method="POST" action="{{ route('pg-research.proposals.reader', 0) }}" id="reader-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Appoint Proposal Reader</h2>
                    <small style="color:rgba(255,255,255,0.85);">Moves the proposal into formal reader review.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <x-pg.field label="Reader" name="reader_id" required>
                    <select name="reader_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="">Select reader…</option>
                        @foreach($readers as $reader)
                            <option value="{{ $reader->id }}">{{ $reader->full_name }} ({{ $reader->academic_rank }})</option>
                        @endforeach
                    </select>
                </x-pg.field>
                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Appoint reader</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: RECORD READER VERDICT --}}
<div class="modal" id="proposal-review-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(600px, 94vw);">
        <form method="POST" action="{{ route('pg-research.proposals.review', 0) }}" id="review-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Record Reader Verdict</h2>
                    <small style="color:rgba(255,255,255,0.85);">An approval advances the candidate to fieldwork.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="font-bold text-slate-900 text-xs" id="review-student"></div>
                    <div class="text-slate-600 text-[11px] mt-0.5" id="review-title"></div>
                </div>

                <x-pg.field label="Verdict" name="verdict" required>
                    <select name="verdict" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="APPROVE">Approve — clear for panel defence</option>
                        <option value="MINOR_REVISION">Minor revision required</option>
                        <option value="MAJOR_REVISION">Major revision required</option>
                        <option value="REJECT">Reject</option>
                    </select>
                </x-pg.field>

                <x-pg.field label="Score (optional)" name="score">
                    <input type="number" step="0.01" min="0" max="100" name="score"
                           class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                </x-pg.field>

                <x-pg.field label="Reader comments" name="comments" required>
                    <textarea name="comments" rows="5" required minlength="10"
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

    function openReaderModal(student, reg, title, reader, critique) {
        document.getElementById('modal-pr-student').textContent = student + ' (' + reg + ')';
        document.getElementById('modal-pr-title').textContent = title;
        document.getElementById('modal-pr-critique').textContent = critique;
        document.getElementById('reader-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const readerBase = @js(route('pg-research.proposals.reader', 0));
        const reviewBase = @js(route('pg-research.proposals.review', 0));

        document.querySelectorAll('.reader-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('reader-form').action = readerBase.replace(/0$/, btn.dataset.proposal);
            });
        });

        document.querySelectorAll('.review-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('review-form').action = reviewBase.replace(/\/0\//, '/' + btn.dataset.proposal + '/');
                document.getElementById('review-student').textContent = btn.dataset.student;
                document.getElementById('review-title').textContent = btn.dataset.title;
            });
        });

        const searchInput = document.getElementById('reader-search');
        const rows = document.querySelectorAll('.reader-row');

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
