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

    {{-- Real-Time Alert Toast Container --}}
    <div id="resub-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
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
                                <button type="button" onclick="openCertifyModal('{{ addslashes($sub['student_name']) }}', '{{ $sub['reg_no'] }}', '{{ addslashes($sub['thesis_title']) }}', '{{ addslashes($sub['corrections_matrix']) }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Certify
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

{{-- MODAL: CERTIFY RESUBMISSION --}}
<div class="modal" id="certify-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Certificate of Thesis Corrections Verification</h2>
                <small style="color:rgba(255,255,255,0.85);">Authorize final hardbound thesis binding and senate graduation listing.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs space-y-3.5">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Scholar Name & Registration</div>
                <div class="font-bold text-slate-900 text-xs mt-0.5" id="modal-c-student"></div>
                <div class="text-slate-600 text-[11px] font-mono mt-0.5" id="modal-c-reg"></div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Thesis Title</div>
                <div class="font-medium text-slate-800 mt-0.5 leading-snug" id="modal-c-title"></div>
            </div>

            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                <div class="text-[11px] text-emerald-800 font-semibold">Corrections Audit Status</div>
                <div class="text-xs font-bold text-emerald-950 mt-0.5" id="modal-c-matrix"></div>
                <div class="text-emerald-700 text-[11px] mt-1">✓ Verified by Lead Supervisor & Internal Viva Panellist</div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded bg-red-600 text-white font-bold text-xs" onclick="document.getElementById('certify-modal').classList.remove('open'); triggerActionAlert('error', 'Corrections Rejected', 'Resubmitted draft returned to candidate for further editing.');">Request Revision</button>
                    <button type="button" class="px-3 py-1.5 rounded bg-emerald-600 text-white font-bold text-xs" onclick="document.getElementById('certify-modal').classList.remove('open'); triggerActionAlert('success', 'Approved for Binding', 'Certificate of Corrections issued. Scholar authorized to print gold-embossed hardbound copies.');">Authorize Hardbound</button>
                </div>
            </div>
        </div>
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
        const box = document.getElementById('resub-alert-box');
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
        document.getElementById('resub-alert-box').classList.add('hidden');
    }

    function openCertifyModal(student, reg, title, matrix) {
        document.getElementById('modal-c-student').textContent = student;
        document.getElementById('modal-c-reg').textContent = reg;
        document.getElementById('modal-c-title').textContent = title;
        document.getElementById('modal-c-matrix').textContent = matrix;
        document.getElementById('certify-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
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
