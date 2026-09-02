@extends('layouts.app')

@section('title', 'Research Eligibility & Coursework Gating')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Eligibility & Coursework Gating</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify postgraduate 100% coursework completion, fee balance status, and provisional waivers for pending mark releases (R19)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="waiver-request-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Request Provisional Waiver
            </button>
            <button type="button" data-modal-open="candidate-create-modal" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white font-bold text-xs hover:bg-[#0d5068] transition-colors shadow-2xs">
                Register Candidate
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Postgraduate Research Phase Gating Rules (Report Section 2 & R19)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">School of Science and Technology</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> 100% Coursework Pass
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Student must achieve a full pass across all taught coursework units before the research proposal phase unlocks.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i> R19 Delayed Marks Waiver
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Accounts for administrative timing delays in official exam mark release to prevent unfair candidate research stalling.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="credit-card" class="w-4 h-4 text-blue-600"></i> Zero Fee Arrears
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Financial verification is integrated directly with Student Accounts. Module locks automatically if fee compliance fails.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="lock" class="w-4 h-4 text-[#0A3E50]"></i> Automated System Lock
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Students gain self-service access to upload proposals and log progress only once verified eligible by HOD and Dean.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Postgraduates</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalPostgrads'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active PhD & Master scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">2026/2027 Cohort</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Fully Eligible</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['fullyEligible'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Coursework & fees cleared.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">69.5% Gating Cleared</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">R19 Provisional Waivers</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['provisionalWaivers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Exams sat / marks in pipeline.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Dean Authorized</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Coursework / Fee Blocked</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['courseworkPending'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">System lock active.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Action Required</span>
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
            <label for="eligibility-search">Search:</label>
            <input type="text" id="eligibility-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search candidate...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="eligibility-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Candidate & Level</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Coursework Audit</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Fee Ledger</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">R19 Provisional Policy</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Eligibility Status</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="eligibility-tbody">
                    @foreach($candidates as $c)
                        <tr class="hover:bg-slate-50/70 transition-colors elig-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $c['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $c['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $c['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 text-xs">
                                {{ $c['coursework_status'] }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <span class="font-semibold {{ str_contains($c['fee_status'], 'Cleared') ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $c['fee_status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-600">
                                {{ $c['waiver_applied'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($c['eligibility_verdict'] === 'Fully Eligible')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Fully Eligible</span>
                                @elseif($c['eligibility_verdict'] === 'Provisional Research Clearance')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Provisional (R19)</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">Blocked</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <x-pg.action
                                        :action="route('pg-research.candidates.recompute', $c['id'])"
                                        label="Re-evaluate"
                                        variant="primary"
                                        title="Recompute eligibility from coursework, fees and active waivers" />

                                    @if($c['pending_waiver_id'])
                                        <div class="flex gap-1">
                                            <x-pg.action
                                                :action="route('pg-research.waivers.decide', $c['pending_waiver_id'])"
                                                :fields="['decision' => 'approve']"
                                                label="Approve waiver"
                                                variant="approve"
                                                confirm="Approve the pending R19 waiver for this candidate?" />
                                            <x-pg.action
                                                :action="route('pg-research.waivers.decide', $c['pending_waiver_id'])"
                                                :fields="['decision' => 'reject']"
                                                label="Reject"
                                                variant="reject"
                                                confirm="Reject the pending waiver request?" />
                                        </div>
                                    @elseif($c['approved_waiver_id'])
                                        <form method="POST" action="{{ route('pg-research.waivers.revoke', $c['approved_waiver_id']) }}" class="flex items-center gap-1">
                                            @csrf
                                            <input type="text" name="reason" required minlength="5" placeholder="Revocation reason"
                                                   class="w-32 px-1.5 py-0.5 text-[10.5px] rounded border border-slate-300">
                                            <button type="submit" class="px-2 py-1 rounded border border-red-400 text-red-700 hover:bg-red-50 font-semibold text-[10.5px]">
                                                Revoke
                                            </button>
                                        </form>
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
                Showing 1 to {{ count($candidates) }} of {{ count($candidates) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: REQUEST PROVISIONAL WAIVER (R19) --}}
<x-pg.modal-form
    id="waiver-request-modal"
    title="Request R19 Provisional Research Waiver"
    subtitle="Lodges a waiver request against a live candidate record; a decision recalculates eligibility."
    :action="route('pg-research.waivers.request')"
    submit-label="Lodge waiver request">

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Waiver type" name="waiver_type">
        <select name="waiver_type" class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="R19_PROVISIONAL">R19 provisional research start</option>
            <option value="FEE_DEFERRAL">Fee deferral</option>
            <option value="COURSEWORK_CONCESSION">Coursework concession</option>
        </select>
    </x-pg.field>

    <x-pg.field label="Justification" name="reason" required hint="Recorded verbatim on the waiver and in the research audit trail.">
        <textarea name="reason" rows="4" required minlength="10"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"
                  placeholder="e.g. DSC 902 marks pending Senate release; Dean has authorised a provisional start.">{{ old('reason') }}</textarea>
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: REGISTER RESEARCH CANDIDATE --}}
<x-pg.modal-form
    id="candidate-create-modal"
    title="Register Postgraduate Research Candidate"
    subtitle="Creates the candidate record; eligibility is computed from the figures entered, not typed in."
    :action="route('pg-research.candidates.store')"
    submit-label="Register candidate"
    width="640px">

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Registration number" name="reg_no" required>
            <input type="text" name="reg_no" required maxlength="60" value="{{ old('reg_no') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs font-mono" placeholder="PHD-CS/2026/001">
        </x-pg.field>

        <x-pg.field label="Candidate name" name="candidate_name" required>
            <input type="text" name="candidate_name" required maxlength="190" value="{{ old('candidate_name') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Degree level" name="degree_level" required>
            <select name="degree_level" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                <option value="MASTERS">Masters</option>
                <option value="PHD">PhD</option>
            </select>
        </x-pg.field>

        <x-pg.field label="Programme" name="programme_title" required>
            <input type="text" name="programme_title" required maxlength="190" value="{{ old('programme_title') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Academic year" name="academic_year">
            <input type="text" name="academic_year" maxlength="20" value="{{ old('academic_year') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs" placeholder="2026/2027">
        </x-pg.field>

        <x-pg.field label="Registration status" name="registration_status">
            <input type="text" name="registration_status" maxlength="40" value="{{ old('registration_status', 'ACTIVE') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Coursework units required" name="coursework_units_total" required>
            <input type="number" name="coursework_units_total" min="0" max="60" required value="{{ old('coursework_units_total', 0) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Coursework units passed" name="coursework_units_passed" required>
            <input type="number" name="coursework_units_passed" min="0" max="60" required value="{{ old('coursework_units_passed', 0) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="GPA" name="gpa">
            <input type="number" step="0.01" min="0" max="5" name="gpa" value="{{ old('gpa') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Fee balance (KES)" name="fee_balance" hint="A non-zero balance blocks research registration unless waived.">
            <input type="number" step="0.01" min="0" name="fee_balance" value="{{ old('fee_balance', 0) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>
</x-pg.modal-form>

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

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('eligibility-search');
        const rows = document.querySelectorAll('.elig-row');

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
