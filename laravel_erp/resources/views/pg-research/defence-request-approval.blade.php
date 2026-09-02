@extends('layouts.app')

@section('title', 'Defence Request Approval')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Defence Request Approval</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify postgraduate dissertation defense clearance, Turnitin originality, supervisor endorsement, and fee audit</p>
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
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Doctoral & Master's Defense Eligibility Verification Protocol</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Directorate of Postgraduate Studies</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-emerald-600"></i> Turnitin Plagiarism Threshold
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Similarity index must not exceed <strong>15% total</strong> with no single source exceeding 3% to qualify for viva dispatch.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="file-check" class="w-4 h-4 text-blue-600"></i> Supervisor Sign-off
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Both Lead and Co-Supervisors must have digitally endorsed the Notice of Intent to Submit and draft manuscript.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="book-open-check" class="w-4 h-4 text-amber-600"></i> Mandatory Publications
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    PhD candidates require 2 peer-reviewed journal papers; Master's scholars require at least 1 verified published article.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-[#0A3E50]"></i> 100% Fee Clearance
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Student finance ledger must reflect zero outstanding balance before oral defense date can be published.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Defence Requests</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalRequests'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Doctoral & Master candidates.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Current Academic Term</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Clearance</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingApproval'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting Board verification.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Needs Action</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Cleared For Viva</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['clearedForViva'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Ready for panel allocation.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">60.9% Rate</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Average Turnitin Score</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['avgTurnitin'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Below 15% threshold.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">100% CUE Compliant</span>
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
            <label for="defence-search">Search:</label>
            <input type="text" id="defence-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search candidate or title...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="defence-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Candidate & Admission</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Thesis Title & Supervisor</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Turnitin</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Publications & Fees</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="defence-tbody">
                    @foreach($requests as $req)
                        <tr class="hover:bg-slate-50/70 transition-colors defence-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $req['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $req['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $req['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-medium text-slate-900 text-xs leading-snug">{{ $req['thesis_title'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-1"><strong class="text-slate-700">Supervisor:</strong> {{ $req['lead_supervisor'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold font-mono {{ floatval($req['turnitin_score']) <= 15 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $req['turnitin_score'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 space-y-1">
                                <div class="text-[11px] text-slate-700 font-semibold flex items-center gap-1">
                                    <i data-lucide="book-check" class="w-3.5 h-3.5 text-blue-600"></i> {{ $req['publications_count'] }}
                                </div>
                                <div class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $req['fee_clearance'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req['status'] === 'Cleared for Viva')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Cleared for Viva</span>
                                @elseif($req['status'] === 'Pending Approval')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending Board</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">Sent Back</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($req['is_pending'])
                                    <div class="flex flex-col items-center gap-1">
                                        <x-pg.action
                                            :action="route('pg-research.defence-requests.decide', $req['id'])"
                                            :fields="['decision' => 'APPROVED']"
                                            label="Approve"
                                            variant="approve"
                                            confirm="Grant defence clearance? Examiners become appointable." />
                                        <button type="button" data-modal-open="defence-return-modal"
                                                data-request="{{ $req['id'] }}"
                                                class="px-3 py-1 rounded border border-amber-400 text-amber-700 hover:bg-amber-50 font-semibold text-[10.5px] transition-colors defence-return-trigger">
                                            Send back
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10.5px] text-slate-500 font-semibold">{{ $req['status'] }}</span>
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
                Showing 1 to {{ count($requests) }} of {{ count($requests) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: RAISE DEFENCE REQUEST --}}
<x-pg.modal-form
    id="defence-request-modal"
    title="Raise Defence Clearance Request"
    subtitle="Requires a thesis similarity scan on file; a flagged, unresolved scan blocks the request."
    :action="route('pg-research.defence-requests.store')"
    submit-label="Raise request"
    width="600px">

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Thesis title" name="thesis_title" required>
        <input type="text" name="thesis_title" required maxlength="190" value="{{ old('thesis_title') }}"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: SEND DEFENCE REQUEST BACK --}}
<div class="modal" id="defence-return-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <form method="POST" action="{{ route('pg-research.defence-requests.decide', 0) }}" id="defence-return-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Return Defence Request</h2>
                    <small style="color:rgba(255,255,255,0.85);">Records the decision and the reason against the request.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <x-pg.field label="Decision" name="decision" required>
                    <select name="decision" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="RETURNED">Send back for correction</option>
                        <option value="REJECTED">Reject outright</option>
                    </select>
                </x-pg.field>

                <x-pg.field label="Reason" name="notes" required>
                    <textarea name="notes" rows="4" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs">Record decision</button>
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

    function openReviewModal(student, reg, title, turnitin, supervisor) {
        document.getElementById('modal-r-student').textContent = student;
        document.getElementById('modal-r-reg').textContent = reg;
        document.getElementById('modal-r-title').textContent = title;
        document.getElementById('modal-r-turnitin').textContent = turnitin;
        document.getElementById('modal-r-supervisor').innerHTML = '<strong>Lead Supervisor:</strong> ' + supervisor;
        document.getElementById('review-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const defenceBase = @js(route('pg-research.defence-requests.decide', 0));
        document.querySelectorAll('.defence-return-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('defence-return-form').action = defenceBase.replace(/\/0\//, '/' + btn.dataset.request + '/');
            });
        });

        const searchInput = document.getElementById('defence-search');
        const rows = document.querySelectorAll('.defence-row');

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
