@extends('layouts.app')

@section('title', 'Plagiarism & AI Similarity Index Checker')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Plagiarism & AI Similarity Index Checker</h1>
                <span class="text-[11px] font-bold text-white bg-[#0A3E50] px-2 py-0.5 rounded shadow-2xs">Embedded Turnitin Engine</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Embedded originality audit enforcing institutional ceilings: <strong>Max 15% Similarity Index (Turnitin)</strong> and <strong>Max 20% Allowed AI Content</strong></p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="scan-record-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Scan New Manuscript
            </button>
        </div>
    </div>

    {{-- Workflow Guide & Policy Banners --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">University Research Integrity & AI Policy (Report Section 4.3.3 & R11)</h3>
            </div>
            <div class="flex gap-2">
                <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Originality Limit: &le; 15%</span>
                <span class="text-[11px] font-bold text-purple-800 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">AI Limit: &le; 20%</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Turnitin Originality &le; 15%
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Excludes bibliography, formal quotations, and small phrase matches (&lt; 5 words). Maximum cumulative similarity threshold is strictly 15%.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-purple-800 mb-1">
                    <i data-lucide="bot" class="w-4 h-4 text-purple-600"></i> Allowed AI Usage &le; 20%
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    AI generation (ChatGPT, Claude, Copilot) is permitted for language polish but capped at 20%. Manuscripts with &gt; 20% AI text are returned for re-writing.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="file-check" class="w-4 h-4 text-blue-600"></i> Gating Gated Stages
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Clearance certificates are mandatory to unlock: (1) Proposal Defence, (2) External Examiner Dispatch, and (3) Hardbound Senate Clearance.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-[#0A3E50]"></i> Direct In-ERP Scanning
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Eliminates the need for supervisors and candidates to log into external portals. All similarity reports and heatmaps render natively inside the ERP.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Scans Conducted</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalScansConducted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Proposals, Dissertations & Theses.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Turnitin Embedded API</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Fully Cleared Manuscripts</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['fullyClearedDocs'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">&le;15% Sim / &le;20% AI Certified.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">79.0% Clearance Rate</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Flagged Similarity (>15%)</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['flaggedSimilarity'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Exceeded Turnitin ceiling.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Requires Revision</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Flagged AI Content (>20%)</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['flaggedAiUsage'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Exceeded 20% allowed AI use.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Academic Review</span>
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
            <label for="plag-search">Search:</label>
            <input type="text" id="plag-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search manuscript scan...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="plag-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Candidate & Document</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Turnitin Similarity (Max 15%)</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">AI Writing Index (Max 20%)</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Matched Sources Breakdown</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Scan Status & Cert</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="plag-tbody">
                    @foreach($scans as $sc)
                        <tr class="hover:bg-slate-50/70 transition-colors scan-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $sc['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $sc['reg_no'] }}</div>
                                <div class="font-medium text-slate-700 text-xs mt-1">{{ $sc['document_title'] }}</div>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-semibold text-blue-900 bg-blue-50 border border-blue-200">{{ $sc['document_stage'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-extrabold {{ $sc['similarity_score'] <= 15 ? 'text-emerald-700' : 'text-red-700' }}">
                                        {{ $sc['similarity_score'] }}%
                                    </span>
                                    @if($sc['similarity_score'] <= 15)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Pass (&le;15%)</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Excessive (>15%)</span>
                                    @endif
                                </div>
                                <div class="w-28 bg-slate-200 h-1.5 rounded-full overflow-hidden mt-1.5">
                                    <div class="h-full {{ $sc['similarity_score'] <= 15 ? 'bg-emerald-600' : 'bg-red-600' }}" style="width: {{ min(100, $sc['similarity_score'] * 4) }}%"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-extrabold {{ $sc['ai_score'] <= 20 ? 'text-purple-900' : 'text-red-700' }}">
                                        {{ $sc['ai_score'] }}%
                                    </span>
                                    @if($sc['ai_score'] <= 20)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-900">Allowed (&le;20%)</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Excessive AI</span>
                                    @endif
                                </div>
                                <div class="w-28 bg-slate-200 h-1.5 rounded-full overflow-hidden mt-1.5">
                                    <div class="h-full {{ $sc['ai_score'] <= 20 ? 'bg-purple-600' : 'bg-red-600' }}" style="width: {{ min(100, $sc['ai_score'] * 3) }}%"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-[11px] text-slate-600">
                                <div>{{ $sc['matched_sources'] }}</div>
                                <div class="text-purple-900 font-medium mt-0.5">{{ $sc['ai_breakdown'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($sc['verdict'], 'Cleared'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $sc['verdict'] }}</span>
                                    <div class="font-mono text-[10px] text-slate-500 mt-1 font-semibold">{{ $sc['certificate_no'] }}</div>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $sc['verdict'] }}</span>
                                    <div class="text-[10px] text-amber-700 font-semibold mt-1">Re-Scan Required</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($sc['is_flagged'])
                                    <button type="button" data-modal-open="scan-override-modal"
                                            data-scan="{{ $sc['id'] }}"
                                            data-student="{{ $sc['student_name'] }}"
                                            class="px-3 py-1 rounded border border-red-400 text-red-700 hover:bg-red-50 font-semibold text-xs transition-colors override-trigger">
                                        Review flag
                                    </button>
                                @else
                                    <span class="text-[10.5px] text-emerald-700 font-semibold">{{ $sc['verdict'] }}</span>
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
                Showing 1 to {{ count($scans) }} of {{ count($scans) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: RECORD SIMILARITY SCAN --}}
<x-pg.modal-form
    id="scan-record-modal"
    title="Record Similarity Scan"
    subtitle="The pass/flag verdict is computed from the score against the threshold — it is not chosen here."
    :action="route('pg-research.scans.store')"
    submit-label="Record scan"
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
        <x-pg.field label="Document" name="document_type" required>
            <select name="document_type" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                <option value="THESIS">Thesis manuscript</option>
                <option value="PROPOSAL">Research proposal</option>
                <option value="ARTICLE">Journal article</option>
            </select>
        </x-pg.field>

        <x-pg.field label="Report reference" name="report_reference">
            <input type="text" name="report_reference" maxlength="190" value="{{ old('report_reference') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs font-mono">
        </x-pg.field>

        <x-pg.field label="Similarity index (%)" name="similarity_index" required>
            <input type="number" step="0.01" min="0" max="100" name="similarity_index" required value="{{ old('similarity_index') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Threshold (%)" name="threshold" hint="Scores above this are flagged and block defence clearance.">
            <input type="number" step="0.01" min="0" max="100" name="threshold" value="{{ old('threshold', 15) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>
</x-pg.modal-form>

{{-- MODAL: OVERRIDE FLAGGED SCAN --}}
<div class="modal" id="scan-override-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(560px, 94vw);">
        <form method="POST" action="{{ route('pg-research.scans.override', 0) }}" id="override-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Clear Flagged Similarity Report</h2>
                    <small style="color:rgba(255,255,255,0.85);">The justification is retained permanently in the research audit trail.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg font-bold text-slate-900 text-xs" id="override-student"></div>

                <x-pg.field label="Override justification" name="notes" required hint="Minimum 10 characters; recorded against the scan.">
                    <textarea name="notes" rows="4" required minlength="10"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"
                              placeholder="e.g. Matches are the candidate's own published work, verified against DOI 10.xxxx."></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Clear flag</button>
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

    function openEmbeddedViewer(name, reg, title, sim, ai, cert) {
        document.getElementById('modal-vm-student').textContent = name;
        document.getElementById('modal-vm-reg').textContent = reg + ' | ' + title;
        document.getElementById('modal-vm-sim').textContent = sim + '%';
        document.getElementById('modal-vm-ai').textContent = ai + '%';
        document.getElementById('modal-vm-cert').textContent = cert;
        document.getElementById('viewer-modal').classList.add('open');
        lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const overrideBase = @js(route('pg-research.scans.override', 0));
        document.querySelectorAll('.override-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('override-form').action = overrideBase.replace(/\/0\//, '/' + btn.dataset.scan + '/');
                document.getElementById('override-student').textContent = btn.dataset.student;
            });
        });

        const searchInput = document.getElementById('plag-search');
        const rows = document.querySelectorAll('.scan-row');

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
