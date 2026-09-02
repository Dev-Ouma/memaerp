@extends('layouts.app')

@section('title', 'Legacy Projects & Interim Data Migration')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Legacy Projects & Interim Data Migration</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Consolidate DSC800 (Module 12) supervisory records and migrate ongoing candidate interim dossiers (R10, R18)</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="legacy-stage-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Stage Legacy Record
            </button>
            <button type="button" data-modal-open="legacy-batch-modal" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs transition-colors shadow-2xs">
                Import Batch
            </button>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Legacy System Consolidation & Data Migration Standard (R10 & R18)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">ICT Directorate & Graduate School</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="database" class="w-4 h-4 text-blue-600"></i> DSC800 (Module 12) Intake
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Unifies project supervision tools from DSC800 into the unified ERP research suite so staff work in one single system.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i> Zero Re-Submission Guarantee
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Ongoing scholars who uploaded forms under interim procedures will not be required to restart or re-upload from scratch.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600"></i> Checksum & Audit Integrity
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Every migrated PDF, proposal rubber-stamp, and supervisor log is verified against historical department records.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-[#0A3E50]"></i> Seamless Stage Mapping
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Automatically emplaces candidates at their current exact lifecycle phase (Proposal, Progress Form, or Final Viva).
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Legacy Dossiers</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalLegacyDossiers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Identified ongoing scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Historical Pipeline</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">DSC800 (Module 12) Migrated</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['migratedFromDSC800'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Project management data.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Unified Portal</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Interim Forms Imported</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['interimFormsMigrated'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Preserved student uploads.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Zero Re-Work</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Re-Validation</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingDataValidation'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting HOD sign-off.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Final Verification</span>
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
            <label for="mig-search">Search:</label>
            <input type="text" id="mig-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search migration record...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="mig-table">
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
                                <span class="text-white font-bold" style="color:#ffffff !important;">Legacy Source System</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Migrated Artifacts</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Target Lifecycle Stage</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Validation Status</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="mig-tbody">
                    @foreach($migrations as $m)
                        <tr class="hover:bg-slate-50/70 transition-colors mig-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $m['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $m['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $m['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-blue-900 bg-blue-50 border border-blue-200">
                                    {{ $m['source_module'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-[11px] text-slate-700">
                                {{ $m['migrated_artifacts'] }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-[#0A3E50] text-xs">
                                {{ $m['target_stage'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($m['validation_status'], '100%'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Verified (100%)</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending Re-Confirm</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($m['is_pending'])
                                        <x-pg.action
                                            :action="route('pg-research.legacy.import', $m['id'])"
                                            label="Import"
                                            variant="primary"
                                            confirm="Import this legacy record into the live research register?" />
                                    @elseif($m['is_imported'])
                                        <x-pg.action
                                            :action="route('pg-research.legacy.verify', $m['id'])"
                                            label="Confirm &amp; sync"
                                            variant="approve"
                                            confirm="Confirm the imported dossier reconciles with the source system?" />
                                    @else
                                        <span class="text-[10.5px] text-slate-500 font-semibold">{{ $m['validation_status'] }}</span>
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
                Showing 1 to {{ count($migrations) }} of {{ count($migrations) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: STAGE LEGACY RECORD --}}
<x-pg.modal-form
    id="legacy-stage-modal"
    title="Stage a Legacy Record for Import"
    subtitle="Staging is idempotent — re-staging the same batch and source reference updates the existing row."
    :action="route('pg-research.legacy.store')"
    submit-label="Stage record"
    width="620px">

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Batch reference" name="batch_reference" required hint="Records sharing a batch can be imported together.">
            <input type="text" name="batch_reference" required maxlength="60" value="{{ old('batch_reference') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Source module" name="source_module" required>
            <input type="text" name="source_module" required maxlength="60" value="{{ old('source_module') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>

    <x-pg.field label="Source reference" name="source_reference" required hint="Must match the candidate registration number for the import to bind.">
        <input type="text" name="source_reference" required maxlength="100" value="{{ old('source_reference') }}"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>

    <x-pg.field label="Target stage" name="target_stage" required>
        <select name="target_stage" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            @foreach(\App\Models\PgResearch\PgResearchCandidate::STAGES as $stage)
                <option value="{{ $stage }}">{{ ucfirst(strtolower($stage)) }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Artefacts carried over" name="artifacts">
        <textarea name="artifacts" rows="3"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('artifacts') }}</textarea>
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: IMPORT WHOLE BATCH --}}
<x-pg.modal-form
    id="legacy-batch-modal"
    title="Import an Entire Batch"
    subtitle="Every pending or previously failed record in the batch is attempted; failures keep their error message."
    :action="route('pg-research.legacy.batch')"
    submit-label="Run batch import"
    width="480px">

    <x-pg.field label="Batch reference" name="batch_reference" required>
        <input type="text" name="batch_reference" required maxlength="60"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>
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

    function openMigModal(name, reg, source, artifacts, stage) {
        document.getElementById('modal-mg-student').textContent = name + ' (' + reg + ')';
        document.getElementById('modal-mg-source').textContent = 'Source: ' + source;
        document.getElementById('modal-mg-artifacts').textContent = artifacts;
        document.getElementById('modal-mg-stage').textContent = stage;
        document.getElementById('mig-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('mig-search');
        const rows = document.querySelectorAll('.mig-row');

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
