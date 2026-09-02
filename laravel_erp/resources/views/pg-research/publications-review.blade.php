@extends('layouts.app')

@section('title', 'Research Publications Review')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Publications Review</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Audit mandatory Commission for University Education (CUE) peer-reviewed journal publishing requirements for postgraduate candidates</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" onclick="openAddPubModal()" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Log Article
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="pub-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
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
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">CUE Harmonized Postgraduate Publication Benchmark Standard</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">CUE Standard 4.10</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i> PhD: 2 Articles
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Doctoral candidates must publish at least two (2) papers in refereed, peer-reviewed, indexed academic journals before viva.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="book-open" class="w-4 h-4 text-blue-600"></i> Master's: 1 Article
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Master's candidates require at least one (1) published or formally accepted peer-reviewed paper extracted from their thesis.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600"></i> Approved Journal Indexes
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Journals must be listed on Scopus, Web of Science, PubMed, AJOL, or University Senate-accredited index platforms.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1">
                    <i data-lucide="link" class="w-4 h-4 text-[#0A3E50]"></i> Verified DOI & Co-Authorship
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Candidate must be lead/first author with their appointed supervisors listed as co-authors and institutional affiliation confirmed.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Articles Logged</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalArticlesLogged'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Doctoral & Master scholars.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Institutional Output</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Verified Peer-Reviewed</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['verifiedPeerReviewed'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">CUE compliant articles.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">75% High Quality</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Indexing Check</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingIndexingCheck'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Secretariat validating DOI.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Under Review</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Rejected Non-CUE</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['rejectedNonCUE'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Predatory journal listing.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Strict Quality Bar</span>
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
            <label for="pub-search">Search:</label>
            <input type="text" id="pub-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search article title...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="pub-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Author & Programme</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Article Title & Journal</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Journal Indexing & DOI</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">CUE Threshold</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="pub-tbody">
                    @foreach($publications as $pb)
                        <tr class="hover:bg-slate-50/70 transition-colors pub-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $pb['author_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $pb['reg_no'] }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">{{ $pb['programme'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-medium text-slate-900 text-xs leading-snug">{{ $pb['article_title'] }}</div>
                                <div class="text-[11px] text-blue-900 font-semibold mt-1">{{ $pb['journal_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                    {{ $pb['indexing'] }}
                                </span>
                                <div class="text-[11px] text-slate-500 font-mono mt-1">DOI: {{ $pb['doi_link'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 text-[11px]">
                                {{ $pb['cue_requirement'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($pb['status'] === 'Verified & Approved')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Verified & Approved</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Pending Indexing</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openPubModal('{{ addslashes($pb['author_name']) }}', '{{ addslashes($pb['article_title']) }}', '{{ addslashes($pb['journal_name']) }}', '{{ $pb['doi_link'] }}')" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Verify
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
                Showing 1 to {{ count($publications) }} of {{ count($publications) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: VERIFY PUBLICATION --}}
<div class="modal" id="pub-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(560px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Postgraduate Publication Audit & Verification</h2>
                <small style="color:rgba(255,255,255,0.85);">Verify journal indexation, DOI, and co-authorship credentials.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs space-y-3.5">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Scholar Author</div>
                <div class="font-bold text-slate-900 text-xs mt-0.5" id="modal-pb-author"></div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-500 font-semibold">Article Title & Publisher</div>
                <div class="font-medium text-slate-800 mt-0.5 leading-snug" id="modal-pb-title"></div>
                <div class="text-blue-900 font-semibold text-[11px] mt-1" id="modal-pb-journal"></div>
                <div class="text-slate-500 font-mono text-[11px] mt-0.5" id="modal-pb-doi"></div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded bg-red-600 text-white font-bold text-xs" onclick="document.getElementById('pub-modal').classList.remove('open'); triggerActionAlert('error', 'Article Flagged Non-CUE', 'Journal indexing rejected due to unaccredited publisher status.');">Flag Non-CUE</button>
                    <button type="button" class="px-3 py-1.5 rounded bg-emerald-600 text-white font-bold text-xs" onclick="document.getElementById('pub-modal').classList.remove('open'); triggerActionAlert('success', 'Article Verified', 'Publication officially credited toward candidate degree requirement.');">Verify & Credit</button>
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
        const box = document.getElementById('pub-alert-box');
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
        document.getElementById('pub-alert-box').classList.add('hidden');
    }

    function openPubModal(author, title, journal, doi) {
        document.getElementById('modal-pb-author').textContent = author;
        document.getElementById('modal-pb-title').textContent = title;
        document.getElementById('modal-pb-journal').textContent = journal;
        document.getElementById('modal-pb-doi').textContent = 'DOI Link: ' + doi;
        document.getElementById('pub-modal').classList.add('open');
    }

    function openAddPubModal() {
        triggerActionAlert('info', 'Article Intake Portal', 'Scholar can upload article PDF manuscript, DOI link, and supervisor endorsement letter.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('pub-search');
        const rows = document.querySelectorAll('.pub-row');

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
