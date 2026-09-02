@extends('layouts.app')

@section('title', 'PG Appeal Period Setup')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Title & Top Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">PG Appeal Period Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure postgraduate appeal submission windows, examination review cutoffs, and Senate hearing dates</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="period-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                New Appeal Window
            </button>
        </div>
    </div>

    {{-- Governance & Lifecycle Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Postgraduate Appeals Schedule Governance & Senate Hearing Timeline</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Senate Regulation 14.3</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i> Submission Window
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    The designated period during which registered PhD and Master's scholars can file formal appeal dossiers via the portal.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-red-700 mb-1">
                    <i data-lucide="calendar-x" class="w-4 h-4 text-red-600"></i> Hard Submission Cutoff
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    System blocks late entries automatically to permit Secretariat document audit before panel hearings.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="gavel" class="w-4 h-4 text-blue-600"></i> Hearing & Board Date
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Formal sitting of the Postgraduate Appeals Tribunal to review submissions, call examiners, and deliberate verdicts.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="history" class="w-4 h-4 text-orange-600"></i> Window Extensions
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Senate Executive Chair sign-off required to adjust window dates. Modification triggers student email notification.
                </p>
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
            <label for="period-search">Search:</label>
            <input type="text" id="period-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search appeal window...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="period-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Appeal Window & Cohort</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Academic Year</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Start Date</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Cutoff Date</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Hearing Sitting</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="period-tbody">
                    @foreach($periods as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors period-row">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $p['window_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $p['cohort'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">{{ $p['academic_year'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">{{ $p['start_date'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">{{ $p['end_date'] }}</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-900">{{ $p['hearing_date'] }}</td>
                            <td class="py-3.5 px-4">
                                @if($p['status'] === 'Open')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Open Window</span>
                                @elseif($p['status'] === 'Scheduled')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Scheduled</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-600">Closed</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($p['is_draft'])
                                        <x-pg.action
                                            :action="route('pg-research.appeal-periods.open', $p['id'])"
                                            label="Open window"
                                            variant="approve"
                                            confirm="Open this window? Candidates will be able to lodge appeals immediately." />
                                    @elseif($p['is_open'])
                                        <x-pg.action
                                            :action="route('pg-research.appeal-periods.close', $p['id'])"
                                            label="Close window"
                                            variant="reject"
                                            confirm="Close this window? No further appeals can be lodged against it." />
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
                Showing 1 to {{ count($periods) }} of {{ count($periods) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: NEW APPEAL WINDOW --}}
<x-pg.modal-form
    id="period-modal"
    title="Configure PG Appeal Window"
    subtitle="Windows are created as drafts; opening one is a separate, audited action."
    :action="route('pg-research.appeal-periods.store')"
    submit-label="Create draft window"
    width="600px">

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Academic year" name="academic_year" required>
            <input type="text" name="academic_year" required maxlength="20" value="{{ old('academic_year') }}"
                   placeholder="2025/2026"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Window label" name="term_label" required>
            <input type="text" name="term_label" required maxlength="40" value="{{ old('term_label') }}"
                   placeholder="Semester 1 Appeals"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>

    <x-pg.field label="Appeal category" name="category_id" hint="Leave blank to accept appeals of every category in this window.">
        <select name="category_id" class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->code }} — {{ $category->name }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Opens on" name="opens_on" required>
            <input type="date" name="opens_on" required value="{{ old('opens_on') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Closes on" name="closes_on" required>
            <input type="date" name="closes_on" required value="{{ old('closes_on') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>

    <x-pg.field label="Notes" name="notes">
        <textarea name="notes" rows="3"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('notes') }}</textarea>
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

    function openAddPeriodModal() {
        document.getElementById('period-modal-title').textContent = 'Add PG Appeal Window';
        document.getElementById('modal-p-name').value = '';
        document.getElementById('modal-p-cohort').value = 'All Postgraduate Scholars';
        document.getElementById('period-modal').classList.add('open');
    }

    function openEditPeriodModal(name, year, cohort, start, end, hearing, status) {
        document.getElementById('period-modal-title').textContent = 'Edit Appeal Window (' + year + ')';
        document.getElementById('modal-p-name').value = name;
        document.getElementById('modal-p-year').value = year;
        document.getElementById('modal-p-cohort').value = cohort;
        document.getElementById('modal-p-start').value = start;
        document.getElementById('modal-p-end').value = end;
        document.getElementById('modal-p-hearing').value = hearing;
        document.getElementById('modal-p-status').value = status;
        document.getElementById('period-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('period-search');
        const rows = document.querySelectorAll('.period-row');

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
