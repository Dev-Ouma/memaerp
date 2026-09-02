@extends('layouts.app')

@section('title', 'PG Appeal Category')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Title & Top Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">PG Appeal Category</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure postgraduate appeal classifications, examination dispute tiers, and SLA resolution windows</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
            <button type="button" data-modal-open="category-create-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                New Category
            </button>
            <button type="button" data-modal-open="appeal-lodge-modal" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs transition-colors shadow-2xs">
                Lodge Appeal
            </button>
        </div>
    </div>

    {{-- Governance & Lifecycle Guide --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Postgraduate Appeals Framework & Senate Escalation Rules</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Directorate of Postgraduate Studies</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-slate-800 mb-1">
                    <i data-lucide="file-warning" class="w-4 h-4 text-amber-600"></i> Viva & Defense Disputes
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Pertains to procedural irregularities or grading contests arising from doctoral or master's oral examinations.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i> Milestone Progression
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Appeals contesting termination of candidature due to failed annual progress reports or supervisor impasse.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Integrity & Similarity
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Appeals referred to the Research Integrity Committee contesting Turnitin similarity threshold sanctions.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-orange-700 mb-1">
                    <i data-lucide="scale" class="w-4 h-4 text-orange-600"></i> Senate Adjudication SLA
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Every category has a binding SLA (7 to 30 days) within which the hearing panel must table its official report to Senate.
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
            <label for="category-search">Search:</label>
            <input type="text" id="category-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search category...">
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="category-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Category Code</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Category Name & Scope</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Adjudication Tier</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Resolution SLA</span>
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
                <tbody class="divide-y divide-slate-100 bg-white" id="category-tbody">
                    @foreach($categories as $cat)
                        <tr class="hover:bg-slate-50/70 transition-colors category-row">
                            <td class="py-3.5 px-4 font-bold text-slate-900 font-mono">{{ $cat['code'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $cat['name'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $cat['description'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                    {{ $cat['tier'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                                {{ $cat['sla_days'] }} Business Days
                            </td>
                            <td class="py-3.5 px-4">
                                @if($cat['status'] === 'Active')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <button type="button" data-modal-open="category-edit-modal"
                                            data-category="{{ $cat['id'] }}"
                                            data-code="{{ $cat['code'] }}"
                                            data-name="{{ $cat['name'] }}"
                                            data-tier="{{ $cat['tier'] }}"
                                            data-sla="{{ $cat['sla_days'] }}"
                                            data-description="{{ $cat['description'] }}"
                                            class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors category-edit-trigger">
                                        Edit
                                    </button>
                                    <x-pg.action
                                        :action="route('pg-research.appeal-categories.toggle', $cat['id'])"
                                        :label="$cat['is_active'] ? 'Deactivate' : 'Activate'"
                                        :variant="$cat['is_active'] ? 'reject' : 'approve'"
                                        :confirm="$cat['is_active']
                                            ? 'Deactivate this category? It will no longer be selectable when lodging an appeal.'
                                            : 'Reactivate this category?'" />
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
                Showing 1 to {{ count($categories) }} of {{ count($categories) }} entries
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-400 cursor-not-allowed">Previous</span>
                <span class="px-2.5 py-0.5 rounded bg-orange-500 text-white font-bold">1</span>
                <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
        </div>
    </div>

    {{-- Lodged appeals — the live register this configuration governs --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs mt-6">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Lodged Appeals</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Every appeal below is a persisted record; the SLA due date derives from its category.
                </p>
            </div>
            <span class="text-[11px] font-semibold text-slate-600">{{ count($appeals) }} on file</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">Reference</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">Candidate</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">Category &amp; Grounds</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">Reviewer</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">SLA</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] border-r border-white/15" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold uppercase text-[11px] text-center w-32" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($appeals as $ap)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $ap['reference'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $ap['student_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $ap['reg_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-[11px]">{{ $ap['category'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ Str::limit($ap['grounds'], 90) }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">{{ $ap['assignee'] }}</td>
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] text-slate-600">Lodged {{ $ap['submitted_at'] }}</div>
                                <div class="text-[11px] font-semibold {{ $ap['is_overdue'] ? 'text-red-600' : 'text-slate-700' }}">
                                    Due {{ $ap['due_at'] }}{{ $ap['is_overdue'] ? ' — overdue' : '' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold {{ $ap['is_open'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $ap['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($ap['is_open'])
                                    <div class="flex flex-col items-center gap-1">
                                        <button type="button" data-modal-open="appeal-assign-modal"
                                                data-appeal="{{ $ap['id'] }}"
                                                data-reference="{{ $ap['reference'] }}"
                                                class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-[10.5px] transition-colors appeal-assign-trigger">
                                            Assign
                                        </button>
                                        <button type="button" data-modal-open="appeal-decide-modal"
                                                data-appeal="{{ $ap['id'] }}"
                                                data-reference="{{ $ap['reference'] }}"
                                                data-grounds="{{ $ap['grounds'] }}"
                                                class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-[10.5px] transition-colors appeal-decide-trigger">
                                            Determine
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10.5px] text-slate-500 font-semibold">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-500">
                                No appeals lodged yet. Use <span class="font-semibold">Lodge Appeal</span> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL: NEW APPEAL CATEGORY --}}
<x-pg.modal-form
    id="category-create-modal"
    title="Configure PG Appeal Category"
    subtitle="The SLA drives the due date on every appeal lodged under this category."
    :action="route('pg-research.appeal-categories.store')"
    submit-label="Create category"
    width="580px">

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Category code" name="code" required>
            <input type="text" name="code" required maxlength="40" value="{{ old('code') }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs uppercase">
        </x-pg.field>

        <x-pg.field label="Adjudication tier" name="applies_to" required>
            <select name="applies_to" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                    @foreach(['MARKS' => 'Marks &amp; grading', 'VIVA' => 'Viva outcome', 'SUPERVISION' => 'Supervision', 'PROGRESSION' => 'Progression', 'OTHER' => 'Other'] as $value => $text)
                        <option value="{{ $value }}">{!! $text !!}</option>
                    @endforeach
            </select>
        </x-pg.field>
    </div>

    <x-pg.field label="Category name" name="name" required>
        <input type="text" name="name" required maxlength="190" value="{{ old('name') }}"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>

    <x-pg.field label="Description" name="description">
        <textarea name="description" rows="3"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('description') }}</textarea>
    </x-pg.field>

    <div class="grid grid-cols-2 gap-3">
        <x-pg.field label="Resolution SLA (days)" name="sla_days" required>
            <input type="number" name="sla_days" required min="1" max="180" value="{{ old('sla_days', 21) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>

        <x-pg.field label="Appeal fee (KES)" name="fee_amount">
            <input type="number" name="fee_amount" min="0" step="0.01" value="{{ old('fee_amount', 0) }}"
                   class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
        </x-pg.field>
    </div>

    <label class="flex items-center gap-2 text-xs text-slate-700 font-medium">
        <input type="checkbox" name="requires_evidence" value="1" class="rounded border-slate-300">
        Supporting evidence is mandatory for this category
    </label>
</x-pg.modal-form>

{{-- MODAL: EDIT APPEAL CATEGORY --}}
<div class="modal" id="category-edit-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <form method="POST" action="{{ route('pg-research.appeal-categories.update', 0) }}" id="category-edit-form">
            @csrf
            @method('PUT')
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Edit Appeal Category</h2>
                    <small style="color:rgba(255,255,255,0.85);">The code is immutable once appeals reference it.</small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="text-[11px] text-slate-500 font-semibold">Category code</div>
                    <div class="font-mono font-bold text-slate-900 text-xs mt-0.5" id="edit-code"></div>
                </div>

                <x-pg.field label="Category name" name="name" required>
                    <input type="text" name="name" id="edit-name" required maxlength="190"
                           class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                </x-pg.field>

                <x-pg.field label="Adjudication tier" name="applies_to" required>
                    <select name="applies_to" id="edit-tier" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                    @foreach(['MARKS' => 'Marks &amp; grading', 'VIVA' => 'Viva outcome', 'SUPERVISION' => 'Supervision', 'PROGRESSION' => 'Progression', 'OTHER' => 'Other'] as $value => $text)
                        <option value="{{ $value }}">{!! $text !!}</option>
                    @endforeach
                    </select>
                </x-pg.field>

                <x-pg.field label="Description" name="description">
                    <textarea name="description" id="edit-description" rows="3"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="grid grid-cols-2 gap-3">
                    <x-pg.field label="Resolution SLA (days)" name="sla_days" required>
                        <input type="number" name="sla_days" id="edit-sla" required min="1" max="180"
                               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                    </x-pg.field>

                    <x-pg.field label="Appeal fee (KES)" name="fee_amount">
                        <input type="number" name="fee_amount" min="0" step="0.01" value="0"
                               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                    </x-pg.field>
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                    <input type="checkbox" name="requires_evidence" value="1" class="rounded border-slate-300">
                    Supporting evidence is mandatory for this category
                </label>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: LODGE APPEAL --}}
<x-pg.modal-form
    id="appeal-lodge-modal"
    title="Lodge a Postgraduate Appeal"
    subtitle="An appeal can only be lodged while a window for its category is open."
    :action="route('pg-research.appeals.store')"
    submit-label="Lodge appeal"
    width="620px"
    multipart>

    <x-pg.field label="Candidate" name="candidate_id" required>
        <select name="candidate_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select candidate…</option>
            @foreach($allCandidates as $option)
                <option value="{{ $option->id }}">{{ $option->candidate_name }} — {{ $option->reg_no }}</option>
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Category" name="category_id" required>
        <select name="category_id" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
            <option value="">Select category…</option>
            @foreach($categories as $cat)
                @if($cat['is_active'])
                    <option value="{{ $cat['id'] }}">{{ $cat['code'] }} — {{ $cat['name'] }}</option>
                @endif
            @endforeach
        </select>
    </x-pg.field>

    <x-pg.field label="Grounds of appeal" name="grounds" required hint="Minimum 20 characters.">
        <textarea name="grounds" rows="5" required minlength="20"
                  class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">{{ old('grounds') }}</textarea>
    </x-pg.field>

    <x-pg.field label="Supporting evidence" name="evidence" hint="PDF, DOC, DOCX, JPG or PNG up to 10 MB. Mandatory for categories that require it.">
        <input type="file" name="evidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
    </x-pg.field>
</x-pg.modal-form>

{{-- MODAL: ASSIGN APPEAL --}}
<div class="modal" id="appeal-assign-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(520px, 94vw);">
        <form method="POST" action="{{ route('pg-research.appeals.assign', 0) }}" id="appeal-assign-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Assign Appeal for Review</h2>
                    <small style="color:rgba(255,255,255,0.85);" id="assign-reference"></small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <x-pg.field label="Reviewer" name="assigned_to" required>
                    <select name="assigned_to" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="">Select reviewer…</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </x-pg.field>
                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-[#0A3E50] hover:bg-[#072c39] text-white font-bold text-xs">Assign</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: DETERMINE APPEAL --}}
<div class="modal" id="appeal-decide-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <form method="POST" action="{{ route('pg-research.appeals.decide', 0) }}" id="appeal-decide-form">
            @csrf
            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">Record Appeal Determination</h2>
                    <small style="color:rgba(255,255,255,0.85);" id="decide-reference"></small>
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
            </div>
            <div class="panel-body p-5 text-xs space-y-3.5">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="text-[11px] text-slate-500 font-semibold">Grounds as lodged</div>
                    <div class="text-slate-800 text-[11px] mt-0.5 leading-snug" id="decide-grounds"></div>
                </div>

                <x-pg.field label="Determination" name="decision" required>
                    <select name="decision" required class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs">
                        <option value="UPHELD">Upheld</option>
                        <option value="DISMISSED">Dismissed</option>
                        <option value="SENT_BACK">Send back for more information</option>
                        <option value="WITHDRAWN">Withdrawn by candidate</option>
                    </select>
                </x-pg.field>

                <x-pg.field label="Determination notes" name="notes" required hint="Minimum 10 characters; these become part of the appeal record.">
                    <textarea name="notes" rows="5" required minlength="10"
                              class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs"></textarea>
                </x-pg.field>

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">Record determination</button>
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

    function openAddCategoryModal() {
        document.getElementById('category-modal-title').textContent = 'Add PG Appeal Category';
        document.getElementById('modal-cat-code').value = '';
        document.getElementById('modal-cat-name').value = '';
        document.getElementById('modal-cat-sla').value = '14';
        document.getElementById('modal-cat-desc').value = '';
        document.getElementById('category-modal').classList.add('open');
    }

    function openEditCategoryModal(code, name, tier, sla, desc, status) {
        document.getElementById('category-modal-title').textContent = 'Edit Appeal Category (' + code + ')';
        document.getElementById('modal-cat-code').value = code;
        document.getElementById('modal-cat-name').value = name;
        document.getElementById('modal-cat-tier').value = tier;
        document.getElementById('modal-cat-sla').value = sla;
        document.getElementById('modal-cat-desc').value = desc;
        document.getElementById('modal-cat-status').value = status;
        document.getElementById('category-modal').classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const rewrite = (base, id) => base.replace(/\/0(\/|$)/, '/' + id + '$1');

        document.querySelectorAll('.category-edit-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('category-edit-form').action =
                    rewrite(@js(route('pg-research.appeal-categories.update', 0)), btn.dataset.category);
                document.getElementById('edit-code').textContent = btn.dataset.code;
                document.getElementById('edit-name').value = btn.dataset.name;
                document.getElementById('edit-sla').value = btn.dataset.sla;
                document.getElementById('edit-description').value = btn.dataset.description;
                const tier = document.getElementById('edit-tier');
                Array.from(tier.options).forEach(o => {
                    if (o.textContent.trim().toLowerCase().startsWith(btn.dataset.tier.trim().toLowerCase().slice(0, 5))) {
                        tier.value = o.value;
                    }
                });
            });
        });

        document.querySelectorAll('.appeal-assign-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('appeal-assign-form').action =
                    rewrite(@js(route('pg-research.appeals.assign', 0)), btn.dataset.appeal);
                document.getElementById('assign-reference').textContent = btn.dataset.reference;
            });
        });

        document.querySelectorAll('.appeal-decide-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('appeal-decide-form').action =
                    rewrite(@js(route('pg-research.appeals.decide', 0)), btn.dataset.appeal);
                document.getElementById('decide-reference').textContent = btn.dataset.reference;
                document.getElementById('decide-grounds').textContent = btn.dataset.grounds;
            });
        });

        const searchInput = document.getElementById('category-search');
        const rows = document.querySelectorAll('.category-row');

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
