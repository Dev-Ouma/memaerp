@extends('layouts.app')

@section('title', 'Final Thesis Resubmission Review')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Final Thesis Resubmission Review</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify post-viva corrections matrix, examiner sign-offs, hardbound library submission, and graduation clearance</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Post-Viva Corrections & Hardbound Archival Protocol</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Senate Postgraduate Committee</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> Minor Corrections (30 Days)
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Typographical, citation, and structural revisions audited directly by the Internal Examiner and Lead Supervisor.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600"></i> Major Corrections (90 Days)
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Substantive methodology, model, or lab revisions requiring re-verification by both Internal & External Examiners.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="book-check" class="w-4 h-4 text-blue-600"></i> Certificate of Corrections
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Official certificate signed by Dean, HOD, and Examiners confirming all oral defense recommendations were fully addressed.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="library" class="w-4 h-4 text-[#0A3E50]"></i> Library Hardbound & Repository
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Submission of 4 gold-embossed hardbound copies (Dean, Dept, Library, Candidate) plus digital Institutional Repository PDF.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Resubmissions</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalResubmissions'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Doctoral & Master candidates.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Current Academic Cohort</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Under Review</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['underReview'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Examiners verifying matrix.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Active Review</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Approved For Binding</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['approvedForBinding'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Cleared for graduation list.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Graduation Ready</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Revisions Pending</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['revisionsPending'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Corrections incomplete.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">7.8% Re-work</span>
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
            <label for="resub-search">Search:</label>
            <input type="text" id="resub-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search resubmission...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="resub-table">
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
                                <span class="text-white font-bold" style="color:#ffffff !important;">Thesis Title & Viva Verdict</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Examiner Auditor</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Corrections Status</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Status</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="resub-tbody">
                    @foreach($resubmissions as $sub)
                        <tr class="hover:bg-slate-50/70 transition-colors resub-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $sub['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $sub['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $sub['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-medium text-slate-900 text-xs leading-snug">{{ $sub['thesis_title'] }}</div>
                                <div class="text-[11px] text-emerald-800 font-semibold mt-1">{{ $sub['viva_verdict'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-semibold text-slate-800">{{ $sub['examiner_auditor'] }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">Resubmitted: {{ $sub['resubmitted_at'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] text-slate-700 font-medium">{{ $sub['corrections_matrix'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5"><strong class="text-slate-700">Hardbound:</strong> {{ $sub['hardbound_copies'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($sub['status'] === 'Approved for Hardbound Binding')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Approved for Binding</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Under Review</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($sub['is_awaiting'])
                                    <button type="button" data-modal-open="resub-file-modal"
                                            data-resub="{{ $sub['id'] }}"
                                            data-student="{{ $sub['student_name'] }}"
                                            data-title="{{ $sub['thesis_title'] }}"
                                            class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors resub-file-trigger">
                                        File corrections
                                    </button>
                                @elseif($sub['is_submitted'])
                                    <button type="button" data-modal-open="resub-verify-modal"
                                            data-resub="{{ $sub['id'] }}"
                                            data-student="{{ $sub['student_name'] }}"
                                            data-reg="{{ $sub['reg_no'] }}"
                                            data-title="{{ $sub['thesis_title'] }}"
                                            data-matrix="{{ $sub['corrections_matrix'] }}"
                                            class="px-3 py-1 rounded border border-emerald-400 text-emerald-700 hover:bg-emerald-50 font-semibold text-xs transition-colors resub-verify-trigger">
                                        Verify corrections
                                    </button>
                                @else
                                    <span class="text-[10.5px] text-slate-500 font-semibold">{{ $sub['status'] }}</span>
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
                Showing 1 to {{ count($resubmissions) }} of {{ count($resubmissions) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: FILE CORRECTIONS --}}
<div class="modal" id="resub-file-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(560px, 94vw);">
        <form method="POST" action="{{ route('pg-research.resubmissions.submit', 0) }}" id="resub-file-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">File Corrected Thesis</h2>
                    <small style="color:rgba(255,255,255,0.85);">Records the corrections matrix and stamps the submission date.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="font-bold text-slate-900 text-xs" id="file-student"></div>
                    <div class="text-slate-600 text-[11px] mt-0.5 leading-snug" id="file-title"></div>
                </div>
                <x-pg.field label="Corrections matrix" name="corrections_summary" required
                            hint="Summarise how each examiner comment was addressed.">
                    <textarea name="corrections_summary" rows="6" required minlength="10"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>
                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs">File corrections</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: VERIFY CORRECTIONS --}}
<div class="modal" id="resub-verify-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <form method="POST" action="{{ route('pg-research.resubmissions.verify', 0) }}" id="resub-verify-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Certificate of Thesis Corrections</h2>
                    <small style="color:rgba(255,255,255,0.85);">Accepting completes the candidate; rejecting reopens the cycle.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="text-[11px] text-slate-500 font-semibold">Scholar &amp; registration</div>
                    <div class="font-bold text-slate-900 text-xs mt-0.5" id="verify-student"></div>
                    <div class="text-slate-600 text-[11px] font-mono mt-0.5" id="verify-reg"></div>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="text-[11px] text-slate-500 font-semibold">Thesis title</div>
                    <div class="font-medium text-slate-800 mt-0.5 leading-snug" id="verify-title"></div>
                </div>
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div class="text-[11px] text-emerald-800 font-semibold">Corrections matrix as filed</div>
                    <div class="text-xs text-emerald-950 mt-0.5 leading-snug" id="verify-matrix"></div>
                </div>

                <x-pg.field label="Verification notes" name="notes">
                    <textarea name="notes" rows="3"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
                    <div class="flex gap-2">
                        <button type="submit" name="decision" value="reject"
                                class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white font-bold text-xs">Request revision</button>
                        <button type="submit" name="decision" value="accept"
                                class="px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Authorize hardbound</button>
                    </div>
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

    function openCertifyModal(student, reg, title, matrix) {
        document.getElementById('modal-c-student').textContent = student;
        document.getElementById('modal-c-reg').textContent = reg;
        document.getElementById('modal-c-title').textContent = title;
        document.getElementById('modal-c-matrix').textContent = matrix;
        document.getElementById('certify-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const bind = (selector, formId, base, fields) => {
            document.querySelectorAll(selector).forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById(formId).action = base.replace(/\/0\//, '/' + btn.dataset.resub + '/');
                    Object.entries(fields).forEach(([id, key]) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = btn.dataset[key] || '';
                    });
                });
            });
        };
        bind('.resub-file-trigger', 'resub-file-form', @js(route('pg-research.resubmissions.submit', 0)),
             {'file-student': 'student', 'file-title': 'title'});
        bind('.resub-verify-trigger', 'resub-verify-form', @js(route('pg-research.resubmissions.verify', 0)),
             {'verify-student': 'student', 'verify-reg': 'reg', 'verify-title': 'title', 'verify-matrix': 'matrix'});

        const searchInput = document.getElementById('resub-search');
        const rows = document.querySelectorAll('.resub-row');

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
